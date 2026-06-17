<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StaffProfile extends Model
{
    protected $fillable = [
        'user_id',
        'phone',
        'bio',
        'is_active',
        'is_available',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_available' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(
            Service::class,
            'service_staff'
        )->withTimestamps();
    }

    public function bookingAssignments(): HasMany
    {
        return $this->hasMany(BookingAssignment::class);
    }
}
