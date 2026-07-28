<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecurringServicePlan extends Model
{
    protected $fillable = ['customer_id', 'service_id', 'source_booking_id', 'service_name', 'interval_days', 'reminder_days_before', 'next_reminder_at', 'last_reminded_at', 'is_active'];

    protected function casts(): array
    {
        return ['interval_days' => 'integer', 'reminder_days_before' => 'integer', 'next_reminder_at' => 'datetime', 'last_reminded_at' => 'datetime', 'is_active' => 'boolean'];
    }

    public function customer(): BelongsTo { return $this->belongsTo(User::class, 'customer_id'); }
    public function service(): BelongsTo { return $this->belongsTo(Service::class); }
    public function sourceBooking(): BelongsTo { return $this->belongsTo(Booking::class, 'source_booking_id'); }
}
