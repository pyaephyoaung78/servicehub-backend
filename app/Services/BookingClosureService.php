<?php

namespace App\Services;

use App\Enums\AssignmentStatus;
use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\BookingAssignment;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class BookingClosureService
{
    public function cancel(
        Booking $booking,
        User $actor,
        ?string $reason,
        bool $isAdmin
    ): Booking {
        $allowedStatuses = $isAdmin
            ? [
                BookingStatus::Pending,
                BookingStatus::Assigned,
                BookingStatus::Accepted,
            ]
            : [
                BookingStatus::Pending,
                BookingStatus::Assigned,
            ];

        if (! in_array(
            $booking->status,
            $allowedStatuses,
            true
        )) {
            throw ValidationException::withMessages([
                'booking' => [
                    "Booking cannot be cancelled while its status is "
                    . "{$booking->status->value}.",
                ],
            ]);
        }

        /*
         * Cancel any assignment that is still active.
         */
        BookingAssignment::query()
            ->where('booking_id', $booking->id)
            ->whereIn('status', [
                AssignmentStatus::Pending->value,
                AssignmentStatus::Accepted->value,
            ])
            ->lockForUpdate()
            ->get()
            ->each(function (BookingAssignment $assignment) {
                $assignment->update([
                    'status' => AssignmentStatus::Cancelled,
                    'cancelled_at' => now(),
                ]);
            });

        $booking->update([
            'status' => BookingStatus::Cancelled,
            'cancellation_reason' => $reason,
            'cancelled_by' => $actor->id,
            'cancelled_at' => now(),
        ]);

        return $booking->fresh();
    }

    public function reject(
        Booking $booking,
        User $actor,
        string $reason
    ): Booking {
        if ($booking->status !== BookingStatus::Pending) {
            throw ValidationException::withMessages([
                'booking' => [
                    'Only pending bookings can be rejected.',
                ],
            ]);
        }

        $hasActiveAssignment = BookingAssignment::query()
            ->where('booking_id', $booking->id)
            ->whereIn('status', [
                AssignmentStatus::Pending->value,
                AssignmentStatus::Accepted->value,
            ])
            ->exists();

        if ($hasActiveAssignment) {
            throw ValidationException::withMessages([
                'booking' => [
                    'A booking with an active assignment cannot be rejected.',
                ],
            ]);
        }

        $booking->update([
            'status' => BookingStatus::Rejected,
            'rejection_reason' => $reason,
            'rejected_by' => $actor->id,
            'rejected_at' => now(),
        ]);

        return $booking->fresh();
    }
}