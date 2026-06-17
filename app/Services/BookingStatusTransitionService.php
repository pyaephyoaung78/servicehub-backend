<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Models\Booking;
use Illuminate\Validation\ValidationException;

class BookingStatusTransitionService
{
    public function transition(
        Booking $booking,
        string $action
    ): Booking {
        $nextStatus = $this->resolveNextStatus($action);

        $this->ensureTransitionIsAllowed(
            $booking->status,
            $nextStatus
        );

        $updates = [
            'status' => $nextStatus,
        ];

        if ($nextStatus === BookingStatus::OnTheWay) {
            $updates['on_the_way_at'] = now();
        }

        if ($nextStatus === BookingStatus::InProgress) {
            $updates['started_at'] = now();
        }

        if ($nextStatus === BookingStatus::Completed) {
            $updates['completed_at'] = now();
        }

        $booking->update($updates);

        return $booking->fresh();
    }

    private function resolveNextStatus(
        string $action
    ): BookingStatus {
        return match ($action) {
            'mark_on_the_way' => BookingStatus::OnTheWay,
            'start' => BookingStatus::InProgress,
            'complete' => BookingStatus::Completed,

            default => throw ValidationException::withMessages([
                'action' => [
                    'The selected work action is invalid.',
                ],
            ]),
        };
    }

    private function ensureTransitionIsAllowed(
        BookingStatus $currentStatus,
        BookingStatus $nextStatus
    ): void {
        $allowedTransitions = [
            BookingStatus::Accepted->value => [
                BookingStatus::OnTheWay,
            ],

            BookingStatus::OnTheWay->value => [
                BookingStatus::InProgress,
            ],

            BookingStatus::InProgress->value => [
                BookingStatus::Completed,
            ],
        ];

        $allowedNextStatuses =
            $allowedTransitions[$currentStatus->value] ?? [];

        if (! in_array(
            $nextStatus,
            $allowedNextStatuses,
            true
        )) {
            throw ValidationException::withMessages([
                'action' => [
                    "Booking cannot move from "
                    . "{$currentStatus->value} to "
                    . "{$nextStatus->value}.",
                ],
            ]);
        }
    }
}