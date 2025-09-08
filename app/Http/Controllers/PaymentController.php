<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Midtrans\Config; // Tambahkan ini
use Midtrans\Snap; // Tambahkan ini

class PaymentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function showPaymentForm(Booking $booking)
    {
        // Validate booking ownership
        if ($booking->user_id !== auth()->id()) {
            return redirect()->route('bookings.index')->with('error', 'Unauthorized access');
        }

        // Check if booking is in correct status for payment
        if (!in_array($booking->status, ['pending', 'pending_payment'])) {
            return redirect()->route('bookings.show', $booking)->with('error', 'Booking is not ready for payment');
        }

        return view('bookings.payment', compact('booking'));
    }

    public function create(Request $request, Booking $booking)
    {
        // Validate booking ownership
        if ($booking->user_id !== auth()->id()) {
            return redirect()->route('bookings.index')->with('error', 'Unauthorized access');
        }

        // Perbarui validasi untuk Midtrans
        $validator = Validator::make($request->all(), [
            'payment_method' => 'required|in:midtrans', // Sesuaikan dengan opsi yang Anda inginkan
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            // Konfigurasi Midtrans
            Config::$serverKey = config('midtrans.server_key');
            Config::$isProduction = config('midtrans.is_production');
            Config::$isSanitized = true;
            Config::$is3ds = true;

            // Buat ID pesanan unik
            $orderId = $booking->id . '-' . uniqid();

            // Siapkan detail transaksi
            $transactionDetails = [
                'order_id' => $orderId,
                'gross_amount' => $booking->total_price,
            ];

            // Siapkan detail pelanggan
            $customerDetails = [
                'first_name' => $booking->user->name,
                'email' => $booking->user->email,
                'phone' => $booking->user->phone ?? '081234567890', // Ganti dengan nomor telepon nyata
            ];
            
            // Item details (opsional)
            $itemDetails = [
                [
                    'id' => $booking->attraction->id,
                    'price' => $booking->total_price,
                    'quantity' => $booking->number_of_tickets,
                    'name' => 'Tiket ' . $booking->attraction->name,
                ]
            ];

            // Buat payload untuk Snap
            $payload = [
                'transaction_details' => $transactionDetails,
                'customer_details' => $customerDetails,
                'item_details' => $itemDetails,
            ];
            
            // Dapatkan Snap Token dari Midtrans
            $snapToken = Snap::getSnapToken($payload);

            // Simpan data pembayaran ke database
            $payment = Payment::create([
                'booking_id' => $booking->id,
                'payment_code' => $orderId, // Gunakan orderId dari Midtrans
                'amount' => $booking->total_price,
                'currency' => 'IDR',
                'payment_method' => 'Midtrans Snap',
                'payment_channel' => 'snap',
                'payment_status' => 'pending',
                'midtrans_token' => $snapToken, // Simpan token
                'expired_at' => now()->addHours(24),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            // Update booking status
            $booking->update(['status' => 'pending_payment']);

            // Redirect pengguna ke halaman tampilan pembayaran dengan token Snap
            return view('payments.show', compact('snapToken', 'booking'));

        } catch (\Exception $e) {
            Log::error('Payment creation with Midtrans failed: ' . $e->getMessage());
            return back()->with('error', 'Payment creation failed. Please try again. ' . $e->getMessage());
        }
    }

    public function uploadProof(Request $request, Payment $payment)
    {
        // ... (Kode ini tidak perlu diubah) ...
        // Validate payment ownership
        if ($payment->booking->user_id !== auth()->id()) {
            return redirect()->route('bookings.index')->with('error', 'Unauthorized access');
        }

        // Validate payment status
        if (!$payment->isPending()) {
            return redirect()->route('bookings.show', $payment->booking)->with('error', 'Payment is not pending');
        }

        // Validate file
        $validator = Validator::make($request->all(), [
            'payment_proof' => 'required|image|max:2048' // max 2MB
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        try {
            // Store payment proof
            $path = $request->file('payment_proof')->store('payment-proofs', 'public');

            // Update payment record
            $payment->update([
                'payment_proof' => $path
            ]);

            return redirect()->route('bookings.show', $payment->booking)
                ->with('success', 'Payment proof uploaded successfully');

        } catch (\Exception $e) {
            Log::error('Payment proof upload failed: ' . $e->getMessage());
            return back()->with('error', 'Payment proof upload failed');
        }
    }

    public function verify(Request $request, Payment $payment)
    {
        // ... (Kode ini tidak perlu diubah) ...
        // Only admin can verify payments
        if (!auth()->user()->is_admin) {
            return redirect()->route('home')->with('error', 'Unauthorized access');
        }

        try {
            // Verify payment
            $payment->markAsPaid();
            
            // Update booking status
            $payment->booking->update([
                'status' => 'confirmed',
                'payment_status' => 'paid'
            ]);

            return redirect()->route('admin.bookings.index')
                ->with('success', 'Payment verified successfully');

        } catch (\Exception $e) {
            Log::error('Payment verification failed: ' . $e->getMessage());
            return back()->with('error', 'Payment verification failed');
        }
    }

    public function cancel(Request $request, Payment $payment)
    {
        // ... (Kode ini tidak perlu diubah) ...
        // Validate payment ownership
        if ($payment->booking->user_id !== auth()->id()) {
            return redirect()->route('bookings.index')->with('error', 'Unauthorized access');
        }

        // Validate payment status
        if (!$payment->isPending()) {
            return redirect()->route('bookings.show', $payment->booking)->with('error', 'Payment cannot be cancelled');
        }

        try {
            // Cancel payment
            $payment->markAsFailed();
            
            // Update booking status
            $payment->booking->update([
                'status' => 'cancelled',
                'payment_status' => 'failed'
            ]);

            return redirect()->route('bookings.index')
                ->with('success', 'Payment cancelled successfully');

        } catch (\Exception $e) {
            Log::error('Payment cancellation failed: ' . $e->getMessage());
            return back()->with('error', 'Payment cancellation failed');
        }
    }

    public function show(Payment $payment)
    {
        // Validate payment ownership
        if ($payment->booking->user_id !== auth()->id() && !auth()->user()->is_admin) {
            return redirect()->route('bookings.index')->with('error', 'Unauthorized access');
        }

        return view('payments.show', compact('payment'));
    }
}