<?php

namespace App\Models;

use App\Enums\BookingStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Booking extends Model
{
    protected $fillable = [
        'customer_id',
        'service_id',
        'service_name',
        'service_price',
        'scheduled_at',
        'phone',
        'address',
        'customer_note',
        'status',
        'on_the_way_at',
        'started_at',
        'completed_at',

        'cancellation_reason',
        'cancelled_by',
        'cancelled_at',

        'rejection_reason',
        'rejected_by',
        'rejected_at',
    ];

    protected function casts(): array
    {
        return [
            'service_price' => 'decimal:2',
            'scheduled_at' => 'datetime',
            'status' => BookingStatus::class,

            'on_the_way_at' => 'datetime',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',

            'cancelled_at' => 'datetime',
            'rejected_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(BookingAssignment::class);
    }

    public function latestAssignment(): HasOne
    {
        return $this->hasOne(BookingAssignment::class)
            ->latestOfMany();
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'cancelled_by'
        );
    }

    public function rejectedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'rejected_by'
        );
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }
}
