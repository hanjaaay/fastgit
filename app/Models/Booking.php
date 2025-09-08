<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Booking extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'user_id',
        'tourist_attraction_id',
        'ticket_id',
        'visit_date',
        'quantity',
        'total_price',
        'status',
        'payment_status',
        'payment_method',
        'payment_proof',
        'notes',
        'order_id'
    ];

    protected $casts = [
        'visit_date' => 'date',
        'total_price' => 'decimal:2'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function touristAttraction(): BelongsTo
    {
        return $this->belongsTo(TouristAttraction::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function review(): HasOne
    {
        return $this->hasOne(Review::class);
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending' => 'warning',
            'paid' => 'success',
            'cancelled' => 'danger',
            default => 'secondary',
        };
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($booking) {
            if (empty($booking->booking_code)) {
                $booking->booking_code = 'BK' . date('Ymd') . strtoupper(uniqid());
            }
        });
    }

    protected function getActivityDescription($type): string
    {
        if ($type === 'created') {
            return "Created new booking for {$this->touristAttraction->name}";
        }
        
        if ($type === 'updated') {
            return "Updated booking status to {$this->status} for {$this->touristAttraction->name}";
        }
        
        return "Booking activity: {$type}";
    }
}