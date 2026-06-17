<?php

namespace App\Http\Controllers\Api\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateStaffAvailabilityRequest;
use App\Http\Resources\StaffProfileResource;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StaffProfileController extends Controller
{
    use ApiResponse;

    public function show(Request $request): JsonResponse
    {
        $profile = $request->user()
            ->staffProfile()
            ->with([
                'user',
                'services.category',
            ])
            ->firstOrFail();

        return $this->successResponse(
            'Staff profile retrieved successfully.',
            [
                'staff' => new StaffProfileResource($profile),
            ]
        );
    }

    public function updateAvailability(
        UpdateStaffAvailabilityRequest $request
    ): JsonResponse {
        $profile = $request->user()
            ->staffProfile()
            ->firstOrFail();

        if (! $profile->is_active) {
            return $this->errorResponse(
                'Inactive staff cannot change availability.',
                null,
                422
            );
        }

        $profile->update([
            'is_available' =>
                $request->validated('is_available'),
        ]);

        $profile->load([
            'user',
            'services.category',
        ]);

        return $this->successResponse(
            'Availability updated successfully.',
            [
                'staff' => new StaffProfileResource($profile),
            ]
        );
    }
}