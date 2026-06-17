<?php

namespace App\Models;

use App\Enums\AssignmentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingAssignment extends Model
{
    protected $fillable = [
        'booking_id',
        'staff_profile_id',
        'assigned_by',
        'status',
        'admin_note',
        'staff_response_note',
        'assigned_at',
        'responded_at',
        'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => AssignmentStatus::class,
            'assigned_at' => 'datetime',
            'responded_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function staffProfile(): BelongsTo
    {
        return $this->belongsTo(StaffProfile::class);
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}