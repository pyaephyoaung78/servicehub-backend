<?php

namespace App\Models;

use App\Enums\QuotationStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Quotation extends Model
{
    protected $fillable = [
        'booking_id',
        'customer_id',
        'created_by',
        'quotation_no',
        'service_name',
        'service_price',
        'extra_fee',
        'discount_amount',
        'total_amount',
        'status',
        'admin_note',
        'customer_response_note',
        'valid_until',
        'sent_at',
        'accepted_at',
        'rejected_at',
        'expired_at',
    ];

    protected function casts(): array
    {
        return [
            'service_price' => 'decimal:2',
            'extra_fee' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'status' => QuotationStatus::class,
            'valid_until' => 'datetime',
            'sent_at' => 'datetime',
            'accepted_at' => 'datetime',
            'rejected_at' => 'datetime',
            'expired_at' => 'datetime',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}