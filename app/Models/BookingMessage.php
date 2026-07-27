<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingMessage extends Model
{
    protected $fillable = ['booking_id', 'sender_id', 'body', 'attachment_path', 'attachment_original_name', 'attachment_mime_type', 'attachment_size'];

    public function booking(): BelongsTo { return $this->belongsTo(Booking::class); }
    public function sender(): BelongsTo { return $this->belongsTo(User::class, 'sender_id'); }
}
