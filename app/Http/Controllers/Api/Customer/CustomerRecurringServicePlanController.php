<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRecurringServicePlanRequest;
use App\Http\Requests\UpdateRecurringServicePlanRequest;
use App\Http\Resources\RecurringServicePlanResource;
use App\Models\Booking;
use App\Models\RecurringServicePlan;
use App\Services\RecurringServicePlanService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerRecurringServicePlanController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $plans = $request->user()->recurringServicePlans()->latest()->get();
        return $this->successResponse('Service plans retrieved successfully.', ['plans' => RecurringServicePlanResource::collection($plans)]);
    }

    public function store(StoreRecurringServicePlanRequest $request, int $booking, RecurringServicePlanService $plans): JsonResponse
    {
        $source = Booking::query()->where('customer_id', $request->user()->id)->findOrFail($booking);
        $plan = $plans->createFromCompletedBooking($source, $request->user(), $request->validated());
        return $this->successResponse('Service plan saved successfully.', ['plan' => new RecurringServicePlanResource($plan)], 201);
    }

    public function update(UpdateRecurringServicePlanRequest $request, RecurringServicePlan $recurringServicePlan): JsonResponse
    {
        abort_unless($recurringServicePlan->customer_id === $request->user()->id, 404);
        $recurringServicePlan->update(['is_active' => $request->boolean('is_active')]);
        return $this->successResponse('Service plan updated successfully.', ['plan' => new RecurringServicePlanResource($recurringServicePlan->fresh())]);
    }
}
