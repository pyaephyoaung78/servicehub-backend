<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Models\Booking;
use Illuminate\Validation\ValidationException;

class BookingStatusTransitionService
{
    public function __construct(
        private readonly BookingTimelineService $timelineService,
        private readonly BookingWorkflowNotifier $notifier
    ) {
    }

    public function transition(
        Booking $booking,
        string $action,
        ?string $checkInCode = null
    ): Booking {
        if ($action === 'refresh_check_in_code') {
            return $this->refreshCheckInCode($booking);
        }

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
            $updates['check_in_code'] = $this->generateCheckInCode();
            $updates['check_in_code_expires_at'] = now()->addHours(4);
        }

        if ($nextStatus === BookingStatus::InProgress) {
            $this->ensureValidCheckInCode($booking, $checkInCode);
            $updates['started_at'] = now();
            $updates['checked_in_at'] = now();
            $updates['check_in_code'] = null;
        }

        if ($nextStatus === BookingStatus::Completed) {
            $hasBeforeProof = $booking->serviceProofs()->where('kind', 'before')->exists();
            $hasAfterProof = $booking->serviceProofs()->where('kind', 'after')->exists();

            if (! $hasBeforeProof || ! $hasAfterProof) {
                throw ValidationException::withMessages([
                    'service_proofs' => ['Upload at least one before and one after service photo before completing the booking.'],
                ]);
            }

            $updates['completed_at'] = now();
        }

        $booking->update($updates);

        $this->recordWorkflowUpdate($booking, $nextStatus);

        return $booking->fresh();
    }

    private function refreshCheckInCode(Booking $booking): Booking
    {
        if ($booking->status !== BookingStatus::OnTheWay) {
            throw ValidationException::withMessages([
                'action' => [
                    'A check-in code can only be refreshed while staff are on the way.',
                ],
            ]);
        }

        $booking->update([
            'check_in_code' => $this->generateCheckInCode(),
            'check_in_code_expires_at' => now()->addHours(4),
        ]);

        $this->timelineService->record(
            $booking,
            'check_in_code_refreshed',
            'Check-in code refreshed',
            'A new code is ready for customer verification.'
        );

        $this->notifier->notifyCustomer(
            $booking,
            'check_in_code_refreshed',
            'Your service check-in code was refreshed',
            'Open your booking details and share the new code only when the staff member arrives.'
        );

        return $booking->fresh();
    }

    private function ensureValidCheckInCode(
        Booking $booking,
        ?string $checkInCode
    ): void {
        if (
            ! $booking->check_in_code
            || ! $booking->check_in_code_expires_at?->isFuture()
            || ! hash_equals($booking->check_in_code, (string) $checkInCode)
        ) {
            throw ValidationException::withMessages([
                'check_in_code' => [
                    'The check-in code is invalid or has expired.',
                ],
            ]);
        }
    }

    private function recordWorkflowUpdate(
        Booking $booking,
        BookingStatus $nextStatus
    ): void {
        [$eventType, $title, $description] = match ($nextStatus) {
            BookingStatus::OnTheWay => [
                'staff_on_the_way',
                'Staff is on the way',
                'Your check-in code is ready in booking details.',
            ],
            BookingStatus::InProgress => [
                'service_started',
                'Service started',
                'Customer check-in was verified and work is now in progress.',
            ],
            BookingStatus::Completed => [
                'service_completed',
                'Service completed',
                'The staff member marked this service as completed.',
            ],
            default => throw new \LogicException('Unsupported booking status.'),
        };

        $this->timelineService->record(
            $booking,
            $eventType,
            $title,
            $description
        );

        $this->notifier->notifyCustomer(
            $booking,
            $eventType,
            $title,
            $nextStatus === BookingStatus::OnTheWay
                ? 'Open booking details to view the verification code for staff arrival.'
                : $description
        );
    }

    private function generateCheckInCode(): string
    {
        return (string) random_int(100000, 999999);
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
