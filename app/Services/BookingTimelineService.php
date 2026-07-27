<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingTimelineEvent;
use App\Models\User;

class BookingTimelineService
{
    public function record(
        Booking $booking,
        string $eventType,
        string $title,
        ?string $description = null,
        ?User $actor = null,
        array $meta = []
    ): BookingTimelineEvent {
        return BookingTimelineEvent::create([
            'booking_id' => $booking->id,
            'actor_type' => $actor ? $actor::class : null,
            'actor_id' => $actor?->id,
            'event_type' => $eventType,
            'title' => $title,
            'description' => $description,
            'meta' => $meta ?: null,
            'occurred_at' => now(),
        ]);
    }
}
