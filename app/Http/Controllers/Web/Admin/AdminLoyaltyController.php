<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReviewLoyaltyRedemptionRequest;
use App\Http\Requests\StoreLoyaltyRewardRequest;
use App\Models\LoyaltyPointTransaction;
use App\Models\LoyaltyRedemption;
use App\Models\LoyaltyReward;
use App\Services\LoyaltyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminLoyaltyController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'status' => ['nullable', Rule::in(['pending', 'approved', 'rejected'])],
            'search' => ['nullable', 'string', 'max:255'],
        ]);

        $redemptions = LoyaltyRedemption::query()
            ->with(['customer', 'reward', 'reviewedBy'])
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($redemptionQuery) use ($search) {
                    $redemptionQuery
                        ->where('redemption_code', 'like', "%{$search}%")
                        ->orWhereHas('customer', fn ($customerQuery) => $customerQuery
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%"))
                        ->orWhereHas('reward', fn ($rewardQuery) => $rewardQuery
                            ->where('name', 'like', "%{$search}%"));
                });
            })
            ->orderByRaw("CASE WHEN status = 'pending' THEN 0 ELSE 1 END")
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.loyalty.index', [
            'rewards' => LoyaltyReward::query()->latest()->get(),
            'redemptions' => $redemptions,
            'statuses' => ['pending', 'approved', 'rejected'],
            'metrics' => [
                'pending_redemptions' => LoyaltyRedemption::query()->where('status', 'pending')->count(),
                'points_issued' => LoyaltyPointTransaction::query()->where('points', '>', 0)->sum('points'),
                'points_redeemed' => abs(LoyaltyPointTransaction::query()->where('points', '<', 0)->sum('points')),
                'active_rewards' => LoyaltyReward::query()->where('is_active', true)->count(),
            ],
        ]);
    }

    public function storeReward(StoreLoyaltyRewardRequest $request): RedirectResponse
    {
        LoyaltyReward::create($request->validated());

        return redirect()->route('admin.loyalty.index')->with('success', 'Reward created successfully.');
    }

    public function toggleReward(LoyaltyReward $loyaltyReward): RedirectResponse
    {
        $loyaltyReward->update(['is_active' => ! $loyaltyReward->is_active]);

        return redirect()->route('admin.loyalty.index')->with('success', 'Reward availability updated.');
    }

    public function reviewRedemption(
        ReviewLoyaltyRedemptionRequest $request,
        LoyaltyRedemption $loyaltyRedemption,
        LoyaltyService $loyaltyService
    ): RedirectResponse {
        $loyaltyService->reviewRedemption(
            redemption: $loyaltyRedemption,
            admin: $request->user(),
            status: $request->validated('status'),
            note: $request->validated('review_note')
        );

        return redirect()->route('admin.loyalty.index')->with('success', 'Reward redemption reviewed.');
    }
}
