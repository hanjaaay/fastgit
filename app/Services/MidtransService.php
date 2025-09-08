<?php

namespace App\Services;

use Midtrans\Config;
use Midtrans\Snap;
use App\Models\Payment;
use App\Models\Booking;
use Illuminate\Support\Facades\Log;

class MidtransService
{
    public function __construct()
    {
        // Set Midtrans configuration
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$isProduction = config('services.midtrans.is_production', false);
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    public function createTransaction(Payment $payment)
    {
        try {
            $booking = $payment->booking;
            $user = $booking->user;

            $params = [
                'transaction_details' => [
                    'order_id' => $payment->payment_code,
                    'gross_amount' => (int) $payment->amount,
                ],
                'customer_details' => [
                    'first_name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                ],
                'item_details' => [
                    [
                        'id' => $booking->id,
                        'price' => (int) $payment->amount,
                        'quantity' => 1,
                        'name' => 'Booking Ticket - ' . $booking->ticket->name,
                    ],
                ],
                'expiry' => [
                    'start_time' => now()->format('Y-m-d H:i:s O'),
                    'unit' => 'hour',
                    'duration' => 24,
                ],
            ];

            // Get Snap Token
            $snapToken = Snap::getSnapToken($params);

            // Update payment with snap token
            $payment->update([
                'payment_details' => array_merge($payment->payment_details ?? [], [
                    'snap_token' => $snapToken,
                    'midtrans_transaction_id' => $payment->payment_code,
                ])
            ]);

            return $snapToken;

        } catch (\Exception $e) {
            Log::error('Midtrans transaction creation failed: ' . $e->getMessage());
            throw $e;
        }
    }

    public function handleNotification($notification)
    {
        try {
            $notification = json_decode($notification, true);
            
            $payment = Payment::where('payment_code', $notification['order_id'])->firstOrFail();
            
            $transactionStatus = $notification['transaction_status'];
            $fraudStatus = $notification['fraud_status'];

            if ($transactionStatus == 'capture') {
                if ($fraudStatus == 'challenge') {
                    $payment->update(['payment_status' => 'challenge']);
                } else if ($fraudStatus == 'accept') {
                    $payment->markAsPaid();
                    $payment->booking->update([
                        'status' => 'confirmed',
                        'payment_status' => 'paid'
                    ]);
                }
            } else if ($transactionStatus == 'settlement') {
                $payment->markAsPaid();
                $payment->booking->update([
                    'status' => 'confirmed',
                    'payment_status' => 'paid'
                ]);
            } else if ($transactionStatus == 'cancel' || $transactionStatus == 'deny' || $transactionStatus == 'expire') {
                $payment->markAsFailed();
                $payment->booking->update([
                    'status' => 'cancelled',
                    'payment_status' => 'failed'
                ]);
            } else if ($transactionStatus == 'pending') {
                $payment->update(['payment_status' => 'pending']);
            }

            return true;

        } catch (\Exception $e) {
            Log::error('Midtrans notification handling failed: ' . $e->getMessage());
            throw $e;
        }
    }
} 