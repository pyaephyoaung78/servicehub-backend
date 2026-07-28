<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\LoyaltyPointTransaction;
use App\Models\LoyaltyRedemption;
use App\Models\LoyaltyReward;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoyaltyService
{
    public const COMPLETED_BOOKING_POINTS = 100;
    public const REFERRER_POINTS = 250;
    public const REFERRED_CUSTOMER_POINTS = 150;

    public function ensureReferralCode(User $customer): string
    {
        if ($customer->referral_code) {
            return $customer->referral_code;
        }

        do {
            $code = 'SH'.Str::upper(Str::random(8));
        } while (User::query()->where('referral_code', $code)->exists());

        $customer->forceFill(['referral_code' => $code])->save();

        return $code;
    }

    public function pointsFor(User $customer): int
    {
        return (int) $customer->loyaltyPointTransactions()->sum('points');
    }

    public function awardForCompletedBooking(Booking $booking): void
    {
        $booking->loadMissing('customer');
        $customer = User::query()->lockForUpdate()->findOrFail($booking->customer_id);

        $transaction = LoyaltyPointTransaction::query()->firstOrCreate(
            [
                'customer_id' => $customer->id,
                'booking_id' => $booking->id,
                'type' => 'booking_completed',
            ],
            [
                'points' => self::COMPLETED_BOOKING_POINTS,
                'description' => 'Points earned for completing '.$booking->service_name.'.',
            ]
        );

        if (! $transaction->wasRecentlyCreated || ! $customer->referred_by) {
            return;
        }

        $isFirstCompletedBooking = Booking::query()
            ->where('customer_id', $customer->id)
            ->where('status', BookingStatus::Completed)
            ->count() === 1;

        if (! $isFirstCompletedBooking) {
            return;
        }

        LoyaltyPointTransaction::query()->firstOrCreate(
            [
                'customer_id' => $customer->id,
                'booking_id' => $booking->id,
                'type' => 'referral_first_completion',
            ],
            [
                'points' => self::REFERRED_CUSTOMER_POINTS,
                'referred_customer_id' => $customer->id,
                'description' => 'Referral welcome points after your first completed booking.',
            ]
        );

        $referrer = User::query()->lockForUpdate()->find($customer->referred_by);

        if ($referrer) {
            LoyaltyPointTransaction::query()->firstOrCreate(
                [
                    'customer_id' => $referrer->id,
                    'booking_id' => $booking->id,
                    'type' => 'referral_first_completion',
                ],
                [
                    'points' => self::REFERRER_POINTS,
                    'referred_customer_id' => $customer->id,
                    'description' => 'Referral points for '.$customer->name.' completing a first booking.',
                ]
            );
        }
    }

    public function redeem(User $customer, LoyaltyReward $reward): LoyaltyRedemption
    {
        return DB::transaction(function () use ($customer, $reward) {
            $lockedCustomer = User::query()->lockForUpdate()->findOrFail($customer->id);
            $lockedReward = LoyaltyReward::query()->lockForUpdate()->findOrFail($reward->id);

            if (! $lockedReward->is_active) {
                throw ValidationException::withMessages(['reward' => ['This reward is no longer available.']]);
            }

            if ($this->pointsFor($lockedCustomer) < $lockedReward->points_cost) {
                throw ValidationException::withMessages(['reward' => ['You do not have enough points for this reward.']]);
            }

            $redemption = LoyaltyRedemption::create([
                'customer_id' => $lockedCustomer->id,
                'loyalty_reward_id' => $lockedReward->id,
                'points_cost' => $lockedReward->points_cost,
                'redemption_code' => $this->newRedemptionCode(),
                'status' => 'pending',
            ]);

            LoyaltyPointTransaction::create([
                'customer_id' => $lockedCustomer->id,
                'points' => -$lockedReward->points_cost,
                'type' => 'reward_redemption',
                'loyalty_redemption_id' => $redemption->id,
                'description' => 'Points redeemed for '.$lockedReward->name.'.',
            ]);

            return $redemption->load('reward');
        });
    }

    public function reviewRedemption(
        LoyaltyRedemption $redemption,
        User $admin,
        string $status,
        ?string $note = null
    ): LoyaltyRedemption {
        if (! in_array($status, ['approved', 'rejected'], true)) {
            throw new \InvalidArgumentException('Invalid redemption decision.');
        }

        return DB::transaction(function () use ($redemption, $admin, $status, $note) {
            $lockedRedemption = LoyaltyRedemption::query()->lockForUpdate()->findOrFail($redemption->id);

            if ($lockedRedemption->status !== 'pending') {
                throw ValidationException::withMessages(['redemption' => ['Only pending redemptions can be reviewed.']]);
            }

            $lockedRedemption->update([
                'status' => $status,
                'reviewed_by' => $admin->id,
                'reviewed_at' => now(),
                'review_note' => $note,
            ]);

            if ($status === 'rejected') {
                LoyaltyPointTransaction::firstOrCreate(
                    [
                        'customer_id' => $lockedRedemption->customer_id,
                        'loyalty_redemption_id' => $lockedRedemption->id,
                        'type' => 'reward_refund',
                    ],
                    [
                        'points' => $lockedRedemption->points_cost,
                        'description' => 'Points refunded because reward redemption was declined.',
                    ]
                );
            }

            return $lockedRedemption->fresh(['reward', 'customer', 'reviewedBy']);
        });
    }

    private function newRedemptionCode(): string
    {
        do {
            $code = 'RWD-'.Str::upper(Str::random(10));
        } while (LoyaltyRedemption::query()->where('redemption_code', $code)->exists());

        return $code;
    }
}
