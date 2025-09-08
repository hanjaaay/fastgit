<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\TouristAttraction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Midtrans\Snap;
use Midtrans\Config;
use Midtrans\Notification;
use Exception;
use PDF;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BookingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->except(['midtransCallback']);
        
        Config::$serverKey = config('midtrans.server_key');
        Config::$clientKey = config('midtrans.client_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    public function index()
    {
        $bookings = Auth::user()->bookings()->with('touristAttraction')->latest()->paginate(10);
        return view('bookings.index', compact('bookings'));
    }

    public function create(TouristAttraction $attraction)
    {
        if ($attraction->tickets->isEmpty()) {
            return back()->with('error', 'No tickets available for this attraction.');
        }

        return view('bookings.create', compact('attraction'));
    }

    public function store(Request $request, TouristAttraction $attraction)
    {
        try {
            DB::beginTransaction();

            // Validasi input
            $validated = $request->validate([
                'visit_date' => 'required|date|after_or_equal:today',
                'quantity' => 'required|integer|min:1',
                'ticket_id' => 'required|exists:tickets,id',
                'notes' => 'nullable|string|max:500',
            ]);

            // Temukan tiket berdasarkan ID
            $ticket = $attraction->tickets()->find($validated['ticket_id']);
            
            // Cek ketersediaan tiket dan stok
            if (!$ticket) {
                DB::rollBack();
                return back()->with('error', 'Tiket yang dipilih tidak tersedia untuk atraksi ini.');
            }

            // PENTING: Periksa stok di dalam transaksi
            if ($ticket->stock < $validated['quantity']) {
                DB::rollBack();
                return back()->with('error', 'Maaf, stok tiket tidak mencukupi.');
            }

            // Hitung total harga
            $totalPrice = $ticket->price * $validated['quantity'];
            
            // Buat objek booking baru
            $booking = new Booking();
            $booking->user_id = Auth::id();
            $booking->tourist_attraction_id = $attraction->id;
            $booking->ticket_id = $ticket->id;
            $booking->visit_date = $validated['visit_date'];
            $booking->quantity = $validated['quantity'];
            $booking->total_price = number_format($totalPrice, 2, '.', '');
            $booking->notes = $validated['notes'];
            $booking->status = 'pending';
            $booking->save();
            
            // Generate dan update order_id setelah booking berhasil disimpan
            $order_id = 'BK-' . $booking->id;
            $booking->order_id = $order_id;
            $booking->save(); // Simpan lagi untuk menambahkan order_id
            
            // Kurangi stok tiket di dalam transaksi
            $ticket->decrement('stock', $booking->quantity);

            DB::commit();

            // Redirect ke halaman detail booking
            return redirect()->route('bookings.show', $booking)->with('success', 'Booking berhasil dibuat. Silakan selesaikan pembayaran.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return back()->withErrors($e->errors())->withInput();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Booking store error: ' . $e->getMessage() . ' at line ' . $e->getLine() . ' in ' . $e->getFile());
            return back()->with('error', 'Terjadi kesalahan saat memproses pesanan. Silakan coba lagi.');
        }
    }

    public function show(Booking $booking)
    {
        if ($booking->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }
        
        $snapToken = null;

        if ($booking->status === 'pending') {
            $params = [
                'transaction_details' => [
                    'order_id' => $booking->order_id,
                    'gross_amount' => $booking->total_price,
                ],
                'customer_details' => [
                    'first_name' => Auth::user()->name,
                    'email' => Auth::user()->email,
                ],
                'callbacks' => [
                    'notification' => config('app.url') . '/midtrans-callback',
                    'finish' => route('bookings.show', $booking)
                ],
            ];

            try {
                $snapToken = Snap::getSnapToken($params);
            } catch (Exception $e) {
                Log::error('Midtrans Snap Token Error: ' . $e->getMessage());
                return back()->with('error', 'Gagal membuat token pembayaran. Error: ' . $e->getMessage());
            }
        }
        
        return view('bookings.show', compact('booking', 'snapToken'));
    }

    public function edit(Booking $booking)
    {
        if ($booking->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }
        
        return view('bookings.edit', compact('booking'));
    }

    public function update(Request $request, Booking $booking)
    {
        if ($booking->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }
        
        $request->validate([
            'visit_date' => 'required|date|after_or_equal:today',
            'quantity' => 'required|integer|min:1',
        ]);

        if ($booking->status !== 'pending') {
            return back()->with('error', 'This booking cannot be updated anymore.');
        }

        $ticket = $booking->ticket;
        $booking->update([
            'quantity' => $request->quantity,
            'total_price' => $ticket->price * $request->quantity,
            'visit_date' => $request->visit_date,
        ]);

        return redirect()->route('bookings.show', $booking)
            ->with('success', 'Booking updated successfully.');
    }

    public function destroy(Booking $booking)
    {
        if ($booking->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }
        
        if ($booking->status !== 'pending') {
            return back()->with('error', 'This booking cannot be cancelled anymore.');
        }

        $booking->update(['status' => 'cancelled']);

        return redirect()->route('bookings.index')
            ->with('success', 'Booking cancelled successfully.');
    }

    public function downloadTicket(Booking $booking)
    {
        if ($booking->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        if ($booking->status !== 'paid') {
            return back()->with('error', 'Ticket can only be downloaded for paid bookings.');
        }
        
        $data = ['booking' => $booking];
        $pdf = PDF::loadView('tickets.show', $data);
        return $pdf->download('ticket-' . $booking->order_id . '.pdf');
    }
    
    public function midtransCallback(Request $request)
    {
        Log::info('Callback received:', ['request' => $request->all()]);
        
        try {
            $notif = new Notification();
        } catch (Exception $e) {
            Log::error('Error creating Midtrans Notification object: ' . $e->getMessage());
            return response()->json(['status' => 'error'], 500);
        }
        
        $transaction = $notif->transaction_status;
        $order_id = $notif->order_id;
        $fraud = $notif->fraud_status;

        Log::info('Processing callback for Order ID: ' . $order_id . ' with status: ' . $transaction);
        
        $booking = Booking::where('order_id', $order_id)->first();
        
        if (!$booking) {
            Log::error('Error: Booking with Order ID ' . $order_id . ' not found.');
            return response()->json(['status' => 'error', 'message' => 'Booking not found'], 404);
        }

        if ($transaction == 'capture' || $transaction == 'settlement') {
            if ($fraud == 'challenge') {
                $booking->update(['status' => 'challenge']);
            } else {
                if ($booking->status !== 'paid') {
                    try {
                        DB::transaction(function () use ($booking) {
                            $booking->update(['status' => 'paid']);
                        });
                        Log::info('Booking status updated to paid for Order ID: ' . $order_id);
                    } catch (Exception $e) {
                        Log::error('Database transaction failed for Order ID ' . $order_id . ': ' . $e->getMessage());
                        return response()->json(['status' => 'error'], 500);
                    }
                }
            }
        } else if ($transaction == 'pending') {
            // No need to update status as it's already pending
        } else if ($transaction == 'deny' || $transaction == 'expire' || $transaction == 'cancel') {
            if ($booking->status !== 'cancelled' && $booking->status !== 'paid') {
                 try {
                     DB::transaction(function () use ($booking) {
                          // Return stock
                          $ticket = $booking->ticket;
                          if ($ticket) {
                               $ticket->increment('stock', $booking->quantity);
                          }
                          $booking->update(['status' => 'cancelled']);
                     });
                     Log::info('Booking status updated to cancelled and stock returned for Order ID: ' . $order_id);
                 } catch (Exception $e) {
                      Log::error('Database transaction failed on cancellation for Order ID ' . $order_id . ': ' . $e->getMessage());
                      return response()->json(['status' => 'error'], 500);
                 }
            }
        }
        
        return response()->json(['status' => 'ok']);
    }
}