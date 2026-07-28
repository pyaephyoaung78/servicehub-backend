<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class BookingWorkflowNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly int $bookingId,
        private readonly string $eventType,
        private readonly string $title,
        private readonly string $body
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'booking_id' => $this->bookingId,
            'event_type' => $this->eventType,
            'title' => $this->title,
            'body' => $this->body,
        ];
    }
}
