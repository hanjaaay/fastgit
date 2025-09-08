<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ticket extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tourist_attraction_id',
        'name',
        'type', // regular, vip, package, children, etc.
        'price',
        'quota',
        'available_quantity', // <--- TAMBAHKAN BARIS INI
        'valid_from',
        'valid_until',
        'description',
        'is_active',
        'qr_code',
    ];

    protected $casts = [
        'valid_from' => 'datetime',
        'valid_until' => 'datetime',
        'is_active' => 'boolean',
        'price' => 'decimal:2',
    ];

    public function touristAttraction(): BelongsTo
    {
        return $this->belongsTo(TouristAttraction::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function getRemainingQuantity(): int
    {
        if ($this->available_quantity === 0) {
            return 0;
        }

        $soldTickets = $this->bookings()
            ->whereNotIn('status', ['cancelled'])
            ->sum('quantity');

        return max(0, $this->available_quantity - $soldTickets);
    }

    public function isAvailable()
    {
        return $this->is_active && 
               $this->quota > 0 && 
               now()->between($this->valid_from, $this->valid_until);
    }

    public function generateQrCode()
    {
        $this->qr_code = uniqid('TICKET-');
        $this->save();
        return $this->qr_code;
    }
}