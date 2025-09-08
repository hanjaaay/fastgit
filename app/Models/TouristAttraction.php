<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class TouristAttraction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'type',
        'category_id',
        'description',
        'location',
        'price',
        'start_date',
        'end_date',
        'is_active',
        'image',
        'featured_image',
        'gallery',
        'facilities',
        'opening_hours',
        'closing_hours',
        'latitude',
        'longitude',
        'contact',
        'city',
        'province'
    ];

    protected $casts = [
        'facilities' => 'array',
        'gallery' => 'array',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'is_active' => 'boolean',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'price' => 'decimal:2',
        'opening_hours' => 'datetime:H:i',
        'closing_hours' => 'datetime:H:i',
    ];

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function bookings(): HasManyThrough
    {
        return $this->hasManyThrough(Booking::class, Ticket::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function isActive(): bool
    {
        return $this->is_active &&
               (!$this->end_date || now()->lte($this->end_date));
    }

    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'attraction' => 'Tourist Attraction',
            'concert' => 'Concert',
            'event' => 'Event',
            default => ucfirst($this->type)
        };
    }

    /**
     * Get the category name from the related Category model.
     * This mutator assumes you have a 'category' relationship.
     */
    public function getCategoryLabelAttribute(): ?string
    {
        return optional($this->category)->name;
    }

    public function getStatusColorAttribute(): string
    {
        return $this->isActive() ? 'success' : 'danger';
    }
}