<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\User;
use App\Notifications\BookingWorkflowNotification;

class BookingWorkflowNotifier
{
    public function notifyCustomer(
        Booking $booking,
        string $eventType,
        string $title,
        string $body
    ): void {
        $booking->loadMissing('customer');

        $booking->customer?->notify(
            new BookingWorkflowNotification(
                $booking->id,
                $eventType,
                $title,
                $body
            )
        );
    }

    public function notifyUser(
        User $user,
        int $bookingId,
        string $eventType,
        string $title,
        string $body
    ): void {
        $user->notify(
            new BookingWorkflowNotification(
                $bookingId,
                $eventType,
                $title,
                $body
            )
        );
    }
}
