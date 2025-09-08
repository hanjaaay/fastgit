<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\TouristAttraction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $bookings = $request->user()
            ->bookings()
            ->with(['touristAttraction'])
            ->latest()
            ->paginate(10);

        return response()->json([
            'bookings' => $bookings
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'tourist_attraction_id' => 'required|exists:tourist_attractions,id',
            'visit_date' => 'required|date|after:today',
            'number_of_tickets' => 'required|integer|min:1'
        ]);

        $attraction = TouristAttraction::findOrFail($request->tourist_attraction_id);

        // Check if the attraction is available for the selected date
        $existingBookings = Booking::where('tourist_attraction_id', $attraction->id)
            ->where('visit_date', $request->visit_date)
            ->where('status', '!=', 'cancelled')
            ->sum('number_of_tickets');

        if ($existingBookings + $request->number_of_tickets > $attraction->capacity) {
            return response()->json([
                'message' => 'Sorry, the selected date is fully booked'
            ], 422);
        }

        try {
            DB::beginTransaction();

            $booking = $request->user()->bookings()->create([
                'tourist_attraction_id' => $attraction->id,
                'visit_date' => $request->visit_date,
                'number_of_tickets' => $request->number_of_tickets,
                'total_price' => $attraction->price * $request->number_of_tickets,
                'status' => 'pending'
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Booking created successfully',
                'booking' => $booking->load('touristAttraction')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to create booking'
            ], 500);
        }
    }

    public function show(Booking $booking)
    {
        // Check if the booking belongs to the authenticated user
        if ($booking->user_id !== auth()->id()) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }

        return response()->json([
            'booking' => $booking->load('touristAttraction')
        ]);
    }

    public function cancel(Booking $booking)
    {
        // Check if the booking belongs to the authenticated user
        if ($booking->user_id !== auth()->id()) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }

        // Check if the booking can be cancelled
        if ($booking->status !== 'pending') {
            return response()->json([
                'message' => 'This booking cannot be cancelled'
            ], 422);
        }

        $booking->update([
            'status' => 'cancelled'
        ]);

        return response()->json([
            'message' => 'Booking cancelled successfully',
            'booking' => $booking
        ]);
    }

    public function upcoming(Request $request)
    {
        $upcomingBookings = $request->user()
            ->bookings()
            ->with('touristAttraction')
            ->where('visit_date', '>=', now())
            ->where('status', '!=', 'cancelled')
            ->orderBy('visit_date')
            ->get();

        return response()->json([
            'bookings' => $upcomingBookings
        ]);
    }

    public function history(Request $request)
    {
        $historyBookings = $request->user()
            ->bookings()
            ->with('touristAttraction')
            ->where('visit_date', '<', now())
            ->orWhere('status', 'cancelled')
            ->orderBy('visit_date', 'desc')
            ->paginate(10);

        return response()->json([
            'bookings' => $historyBookings
        ]);
    }
} 