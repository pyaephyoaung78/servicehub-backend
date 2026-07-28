<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceProof extends Model
{
    protected $fillable = ['booking_id', 'staff_profile_id', 'kind', 'image_path', 'image_original_name', 'image_mime_type', 'image_size', 'note', 'captured_at'];

    protected function casts(): array { return ['captured_at' => 'datetime']; }

    public function booking(): BelongsTo { return $this->belongsTo(Booking::class); }
    public function staffProfile(): BelongsTo { return $this->belongsTo(StaffProfile::class); }
}
