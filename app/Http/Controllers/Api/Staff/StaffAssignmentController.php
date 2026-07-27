<?php

namespace App\Http\Controllers\Api\Staff;

use App\Enums\AssignmentStatus;
use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\RespondToAssignmentRequest;
use App\Http\Resources\BookingAssignmentResource;
use App\Models\Booking;
use App\Models\BookingAssignment;
use App\Services\BookingTimelineService;
use App\Services\BookingWorkflowNotifier;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StaffAssignmentController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $staffProfile = $request->user()->staffProfile;

        if (! $staffProfile) {
            return $this->errorResponse(
                'Staff profile not found.',
                null,
                404
            );
        }

        $query = BookingAssignment::query()
            ->where(
                'staff_profile_id',
                $staffProfile->id
            )
            ->with([
                'booking.customer',
                'booking.service.category',
                'booking.timelineEvents.actor',
                'staffProfile.user',
                'assignedBy',
            ]);

        if ($request->filled('status')) {
            $status = $request->string('status')->toString();

            $validStatuses = array_map(
                fn (AssignmentStatus $status) => $status->value,
                AssignmentStatus::cases()
            );

            if (! in_array($status, $validStatuses, true)) {
                return $this->errorResponse(
                    'Invalid assignment status.',
                    null,
                    422
                );
            }

            $query->where('status', $status);
        }

        $assignments = $query
            ->latest('assigned_at')
            ->paginate(20)
            ->withQueryString();

        return $this->successResponse(
            'Assignments retrieved successfully.',
            BookingAssignmentResource::collection($assignments)
                ->response()
                ->getData(true)
        );
    }

    public function show(
        Request $request,
        int $assignment
    ): JsonResponse {
        $staffProfile = $request->user()->staffProfile;

        if (! $staffProfile) {
            return $this->errorResponse(
                'Staff profile not found.',
                null,
                404
            );
        }

        $ownedAssignment = BookingAssignment::query()
            ->where(
                'staff_profile_id',
                $staffProfile->id
            )
            ->with([
                'booking.customer',
                'booking.service.category',
                'booking.timelineEvents.actor',
                'staffProfile.user',
                'assignedBy',
            ])
            ->findOrFail($assignment);

        return $this->successResponse(
            'Assignment retrieved successfully.',
            [
                'assignment' =>
                    new BookingAssignmentResource(
                        $ownedAssignment
                    ),
            ]
        );
    }

    public function respond(
        RespondToAssignmentRequest $request,
        int $assignment,
        BookingTimelineService $timelineService,
        BookingWorkflowNotifier $notifier
    ): JsonResponse {
        $data = $request->validated();

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
                'Inactive staff cannot respond to assignments.',
                null,
                422
            );
        }

        $updatedAssignment = DB::transaction(
            function () use (
                $assignment,
                $staffProfile,
                $data,
                $timelineService
            ) {
                $lockedAssignment = BookingAssignment::query()
                    ->where(
                        'staff_profile_id',
                        $staffProfile->id
                    )
                    ->lockForUpdate()
                    ->findOrFail($assignment);

                if (
                    $lockedAssignment->status
                    !== AssignmentStatus::Pending
                ) {
                    abort(
                        response()->json([
                            'success' => false,
                            'message' =>
                                'This assignment has already been responded to.',
                            'errors' => null,
                        ], 422)
                    );
                }

                $booking = Booking::query()
                    ->lockForUpdate()
                    ->findOrFail(
                        $lockedAssignment->booking_id
                    );

                if (
                    $booking->status
                    !== BookingStatus::Assigned
                ) {
                    abort(
                        response()->json([
                            'success' => false,
                            'message' =>
                                'This booking is no longer waiting for a staff response.',
                            'errors' => null,
                        ], 422)
                    );
                }

                if ($data['action'] === 'accept') {
                    $lockedAssignment->update([
                        'status' =>
                            AssignmentStatus::Accepted,
                        'staff_response_note' =>
                            $data['response_note'] ?? null,
                        'responded_at' => now(),
                    ]);

                    $booking->update([
                        'status' => BookingStatus::Accepted,
                    ]);

                    $timelineService->record(
                        $booking,
                        'staff_assignment_accepted',
                        'Staff confirmed the booking',
                        'Your service professional accepted this assignment.',
                        $request->user(),
                        ['staff_profile_id' => $staffProfile->id]
                    );
                }

                if ($data['action'] === 'reject') {
                    $lockedAssignment->update([
                        'status' =>
                            AssignmentStatus::Rejected,
                        'staff_response_note' =>
                            $data['response_note'] ?? null,
                        'responded_at' => now(),
                    ]);

                    $booking->update([
                        'status' => BookingStatus::Pending,
                    ]);

                    $timelineService->record(
                        $booking,
                        'staff_assignment_rejected',
                        'Staff assignment declined',
                        'The admin will assign another available professional.',
                        $request->user(),
                        ['staff_profile_id' => $staffProfile->id]
                    );
                }

                return $lockedAssignment;
            }
        );

        $updatedAssignment->load([
            'booking.customer',
            'booking.service.category',
            'booking.timelineEvents.actor',
            'staffProfile.user',
            'assignedBy',
        ]);

        if ($data['action'] === 'accept') {
            $notifier->notifyCustomer(
                $updatedAssignment->booking,
                'staff_assignment_accepted',
                'Your staff member is confirmed',
                'Your service professional accepted the booking. You will be notified when they are on the way.'
            );
        } else {
            $notifier->notifyUser(
                $updatedAssignment->assignedBy,
                $updatedAssignment->booking_id,
                'staff_assignment_rejected',
                'Staff assignment declined',
                'Please assign another available professional.'
            );
        }

        return $this->successResponse(
            $data['action'] === 'accept'
                ? 'Assignment accepted successfully.'
                : 'Assignment rejected successfully.',
            [
                'assignment' =>
                    new BookingAssignmentResource(
                        $updatedAssignment
                    ),
            ]
        );
    }
}
