<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LoyaltyRedemption extends Model
{
    protected $fillable = ['customer_id', 'loyalty_reward_id', 'points_cost', 'redemption_code', 'status', 'reviewed_by', 'reviewed_at', 'review_note'];

    protected function casts(): array
    {
        return ['points_cost' => 'integer', 'reviewed_at' => 'datetime'];
    }

    public function customer(): BelongsTo { return $this->belongsTo(User::class, 'customer_id'); }
    public function reward(): BelongsTo { return $this->belongsTo(LoyaltyReward::class, 'loyalty_reward_id'); }
    public function reviewedBy(): BelongsTo { return $this->belongsTo(User::class, 'reviewed_by'); }
    public function pointTransactions(): HasMany { return $this->hasMany(LoyaltyPointTransaction::class); }
}
