<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Http\Resources\LoyaltyPointTransactionResource;
use App\Http\Resources\LoyaltyRedemptionResource;
use App\Http\Resources\LoyaltyRewardResource;
use App\Models\LoyaltyReward;
use App\Services\LoyaltyService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerLoyaltyController extends Controller
{
    use ApiResponse;

    public function summary(Request $request, LoyaltyService $loyaltyService): JsonResponse
    {
        $customer = $request->user();

        return $this->successResponse('Loyalty summary retrieved successfully.', [
            'points_balance' => $loyaltyService->pointsFor($customer),
            'referral_code' => $loyaltyService->ensureReferralCode($customer),
            'completed_booking_points' => LoyaltyService::COMPLETED_BOOKING_POINTS,
            'referrer_points' => LoyaltyService::REFERRER_POINTS,
            'referred_customer_points' => LoyaltyService::REFERRED_CUSTOMER_POINTS,
        ]);
    }

    public function rewards(): JsonResponse
    {
        $rewards = LoyaltyReward::query()->where('is_active', true)->orderBy('points_cost')->get();

        return $this->successResponse('Loyalty rewards retrieved successfully.', [
            'rewards' => LoyaltyRewardResource::collection($rewards),
        ]);
    }

    public function transactions(Request $request): JsonResponse
    {
        $transactions = $request->user()->loyaltyPointTransactions()
            ->with('booking')
            ->latest()
            ->paginate(30);

        return $this->successResponse(
            'Loyalty activity retrieved successfully.',
            LoyaltyPointTransactionResource::collection($transactions)->response()->getData(true)
        );
    }

    public function redemptions(Request $request): JsonResponse
    {
        $redemptions = $request->user()->loyaltyRedemptions()
            ->with('reward')
            ->latest()
            ->paginate(20);

        return $this->successResponse(
            'Reward redemptions retrieved successfully.',
            LoyaltyRedemptionResource::collection($redemptions)->response()->getData(true)
        );
    }

    public function redeem(
        Request $request,
        LoyaltyReward $loyaltyReward,
        LoyaltyService $loyaltyService
    ): JsonResponse {
        $redemption = $loyaltyService->redeem($request->user(), $loyaltyReward);

        return $this->successResponse('Reward redemption submitted for review.', [
            'redemption' => new LoyaltyRedemptionResource($redemption),
        ], 201);
    }
}
