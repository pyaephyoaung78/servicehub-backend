<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoyaltyPointTransaction extends Model
{
    protected $fillable = ['customer_id', 'points', 'type', 'booking_id', 'loyalty_redemption_id', 'referred_customer_id', 'description'];

    protected function casts(): array
    {
        return ['points' => 'integer'];
    }

    public function customer(): BelongsTo { return $this->belongsTo(User::class, 'customer_id'); }
    public function booking(): BelongsTo { return $this->belongsTo(Booking::class); }
    public function redemption(): BelongsTo { return $this->belongsTo(LoyaltyRedemption::class, 'loyalty_redemption_id'); }
    public function referredCustomer(): BelongsTo { return $this->belongsTo(User::class, 'referred_customer_id'); }
}
