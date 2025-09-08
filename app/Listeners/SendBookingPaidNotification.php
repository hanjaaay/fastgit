<?php

namespace App\Listeners;

use App\Events\BookingPaid;
use App\Mail\BookingPaidMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendBookingPaidNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(BookingPaid $event)
    {
        $booking = $event->booking;
        
        Mail::to($booking->user->email)->send(new BookingPaidMail($booking));
    }
} 