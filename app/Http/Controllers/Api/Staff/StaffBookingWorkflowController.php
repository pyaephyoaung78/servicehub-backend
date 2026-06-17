<?php

namespace App\Http\Controllers\Api\Staff;

use App\Enums\AssignmentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateBookingWorkStatusRequest;
use App\Http\Resources\BookingResource;
use App\Models\Booking;
use App\Models\BookingAssignment;
use App\Services\BookingStatusTransitionService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class StaffBookingWorkflowController extends Controller
{
    use ApiResponse;

    public function updateStatus(
        UpdateBookingWorkStatusRequest $request,
        int $assignment,
        BookingStatusTransitionService $transitionService
    ): JsonResponse {
        $staffProfile = $request->user()->staffProfile;

        if (! $staffProfile) {
            return $this->errorResponse(
                'Staff profile not found.',
                null,
                404
            );
        }

        if (! $staffProfile->is_active) {
            return $this->errorResponse(
                'Inactive staff cannot update booking status.',
                null,
                422
            );
        }

        $data = $request->validated();

        $booking = DB::transaction(
            function () use (
                $assignment,
                $staffProfile,
                $data,
                $transitionService
            ) {
                $lockedAssignment =
                    BookingAssignment::query()
                        ->where(
                            'staff_profile_id',
                            $staffProfile->id
                        )
                        ->lockForUpdate()
                        ->findOrFail($assignment);

                if (
                    $lockedAssignment->status
                    !== AssignmentStatus::Accepted
                ) {
                    abort(
                        response()->json([
                            'success' => false,
                            'message' =>
                                'Only accepted assignments can update booking progress.',
                            'errors' => null,
                        ], 422)
                    );
                }

                $lockedBooking = Booking::query()
                    ->lockForUpdate()
                    ->findOrFail(
                        $lockedAssignment->booking_id
                    );

                return $transitionService->transition(
                    $lockedBooking,
                    $data['action']
                );
            }
        );

        $booking->load([
            'customer',
            'service.category',
            'latestAssignment.staffProfile.user',
            'latestAssignment.assignedBy',
        ]);

        return $this->successResponse(
            $this->successMessage($data['action']),
            [
                'booking' => new BookingResource($booking),
            ]
        );
    }

    private function successMessage(string $action): string
    {
        return match ($action) {
            'mark_on_the_way' =>
                'Booking marked as on the way.',

            'start' =>
                'Booking service started successfully.',

            'complete' =>
                'Booking completed successfully.',

            default =>
                'Booking status updated successfully.',
        };
    }
}