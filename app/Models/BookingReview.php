<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingReview extends Model
{
    protected $fillable = ['booking_id', 'customer_id', 'service_id', 'staff_profile_id', 'rating', 'comment', 'status', 'reviewed_by', 'reviewed_at'];
    protected function casts(): array { return ['rating' => 'integer', 'reviewed_at' => 'datetime']; }
    public function booking(): BelongsTo { return $this->belongsTo(Booking::class); }
    public function customer(): BelongsTo { return $this->belongsTo(User::class, 'customer_id'); }
    public function service(): BelongsTo { return $this->belongsTo(Service::class); }
    public function staffProfile(): BelongsTo { return $this->belongsTo(StaffProfile::class); }
    public function reviewedBy(): BelongsTo { return $this->belongsTo(User::class, 'reviewed_by'); }
}
