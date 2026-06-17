<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    protected $fillable = [
        'service_category_id',
        'name',
        'slug',
        'description',
        'base_price',
        'estimated_duration_minutes',
        'is_active',
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'estimated_duration_minutes' => 'integer',
        'is_active' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function qualifiedStaff(): BelongsToMany
    {
        return $this->belongsToMany(
            StaffProfile::class,
            'service_staff'
        )->withTimestamps();
    }
}
