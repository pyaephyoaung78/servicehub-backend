<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enums\AssignmentStatus;
use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\AssignStaffRequest;
use App\Http\Resources\BookingAssignmentResource;
use App\Http\Resources\StaffProfileResource;
use App\Models\Booking;
use App\Models\BookingAssignment;
use App\Models\StaffProfile;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class AdminBookingAssignmentController extends Controller
{
    use ApiResponse;

    public function eligibleStaff(Booking $booking): JsonResponse
    {
        if ($booking->status !== BookingStatus::Pending) {
            return $this->errorResponse(
                'Only pending bookings can receive a new assignment.',
                null,
                422
            );
        }

        $staff = StaffProfile::query()
            ->with([
                'user',
                'services.category',
            ])
            ->where('is_active', true)
            ->where('is_available', true)
            ->whereHas(
                'services',
                fn($query) =>
                $query->whereKey($booking->service_id)
            )
            ->orderBy(
                User::select('name')
                    ->whereColumn(
                        'users.id',
                        'staff_profiles.user_id'
                    )
            )
            ->get();

        return $this->successResponse(
            'Eligible staff retrieved successfully.',
            [
                'staff' => StaffProfileResource::collection($staff),
            ]
        );
    }

    public function assign(
        AssignStaffRequest $request,
        Booking $booking
    ): JsonResponse {
        $data = $request->validated();

        $assignment = DB::transaction(
            function () use ($request, $booking, $data) {
                /*
                 * Lock the booking row so two admin requests
                 * cannot assign different staff simultaneously.
                 */
                $lockedBooking = Booking::query()
                    ->lockForUpdate()
                    ->findOrFail($booking->id);

                if ($lockedBooking->status !== BookingStatus::Pending) {
                    abort(
                        response()->json([
                            'success' => false,
                            'message' =>
                            'Only pending bookings can be assigned.',
                            'errors' => null,
                        ], 422)
                    );
                }

                $hasActiveAssignment = BookingAssignment::query()
                    ->where('booking_id', $lockedBooking->id)
                    ->whereIn('status', [
                        AssignmentStatus::Pending->value,
                        AssignmentStatus::Accepted->value,
                    ])
                    ->exists();

                if ($hasActiveAssignment) {
                    abort(
                        response()->json([
                            'success' => false,
                            'message' =>
                            'This booking already has an active assignment.',
                            'errors' => null,
                        ], 422)
                    );
                }

                $staffProfile = StaffProfile::query()
                    ->with('services')
                    ->lockForUpdate()
                    ->findOrFail($data['staff_profile_id']);

                if (! $staffProfile->is_active) {
                    abort(
                        response()->json([
                            'success' => false,
                            'message' =>
                            'The selected staff account is inactive.',
                            'errors' => null,
                        ], 422)
                    );
                }

                if (! $staffProfile->is_available) {
                    abort(
                        response()->json([
                            'success' => false,
                            'message' =>
                            'The selected staff member is unavailable.',
                            'errors' => null,
                        ], 422)
                    );
                }

                $isQualified = $staffProfile
                    ->services()
                    ->whereKey($lockedBooking->service_id)
                    ->exists();

                if (! $isQualified) {
                    abort(
                        response()->json([
                            'success' => false,
                            'message' =>
                            'The selected staff member is not qualified for this service.',
                            'errors' => null,
                        ], 422)
                    );
                }

                $lockedBooking->load('service');

                $this->ensureNoScheduleConflict(
                    $staffProfile,
                    $lockedBooking
                );

                $newAssignment = BookingAssignment::create([
                    'booking_id' => $lockedBooking->id,
                    'staff_profile_id' => $staffProfile->id,
                    'assigned_by' => $request->user()->id,
                    'status' => AssignmentStatus::Pending,
                    'admin_note' => $data['admin_note'] ?? null,
                    'assigned_at' => now(),
                ]);

                $lockedBooking->update([
                    'status' => BookingStatus::Assigned,
                ]);

                return $newAssignment;
            }
        );

        $assignment->load([
            'staffProfile.user',
            'assignedBy',
        ]);

        return $this->successResponse(
            'Staff assigned successfully.',
            [
                'assignment' =>
                new BookingAssignmentResource($assignment),
            ],
            201
        );
    }

    private function ensureNoScheduleConflict(
        StaffProfile $staffProfile,
        Booking $booking
    ): void {
        $newDuration =
            $booking->service?->estimated_duration_minutes ?? 60;

        $newStart = $booking->scheduled_at;
        $newEnd = $newStart
            ->copy()
            ->addMinutes($newDuration);

        $existingAssignments = BookingAssignment::query()
            ->where('staff_profile_id', $staffProfile->id)
            ->whereIn('status', [
                AssignmentStatus::Pending->value,
                AssignmentStatus::Accepted->value,
            ])
            ->with('booking.service')
            ->get();

        foreach ($existingAssignments as $assignment) {
            $existingBooking = $assignment->booking;

            if (! in_array($existingBooking->status, [
                BookingStatus::Assigned,
                BookingStatus::Accepted,
                BookingStatus::OnTheWay,
                BookingStatus::InProgress,
            ], true)) {
                continue;
            }

            $existingDuration =
                $existingBooking->service
                ?->estimated_duration_minutes ?? 60;

            $existingStart = $existingBooking->scheduled_at;
            $existingEnd = $existingStart
                ->copy()
                ->addMinutes($existingDuration);

            $overlaps =
                $newStart->lt($existingEnd) &&
                $newEnd->gt($existingStart);

            if ($overlaps) {
                abort(
                    response()->json([
                        'success' => false,
                        'message' =>
                        'The selected staff member has another booking during this schedule.',
                        'errors' => null,
                    ], 422)
                );
            }
        }
    }
}
