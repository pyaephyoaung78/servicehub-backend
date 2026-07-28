<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $notifications = $request->user()
            ->notifications()
            ->latest()
            ->paginate(30);

        return $this->successResponse(
            'Notifications retrieved successfully.',
            $notifications->through(fn ($notification) => [
                'id' => $notification->id,
                'type' => $notification->data['event_type'] ?? 'booking_update',
                'title' => $notification->data['title'] ?? 'Booking update',
                'body' => $notification->data['body'] ?? '',
                'booking_id' => $notification->data['booking_id'] ?? null,
                'read_at' => $notification->read_at?->toISOString(),
                'created_at' => $notification->created_at?->toISOString(),
            ])->toArray()
        );
    }

    public function markAsRead(Request $request, string $notification): JsonResponse
    {
        $ownedNotification = $request->user()
            ->notifications()
            ->findOrFail($notification);

        $ownedNotification->markAsRead();

        return $this->successResponse(
            'Notification marked as read.',
            null
        );
    }
}
