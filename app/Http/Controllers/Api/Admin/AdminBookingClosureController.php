<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminBookingClosureRequest;
use App\Http\Resources\BookingResource;
use App\Models\Booking;
use App\Services\BookingClosureService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class AdminBookingClosureController extends Controller
{
    use ApiResponse;

    public function cancel(
        AdminBookingClosureRequest $request,
        Booking $booking,
        BookingClosureService $closureService
    ): JsonResponse {
        $data = $request->validated();

        $cancelledBooking = DB::transaction(
            function () use (
                $request,
                $booking,
                $data,
                $closureService
            ) {
                $lockedBooking = Booking::query()
                    ->lockForUpdate()
                    ->findOrFail($booking->id);

                return $closureService->cancel(
                    booking: $lockedBooking,
                    actor: $request->user(),
                    reason: $data['reason'],
                    isAdmin: true
                );
            }
        );

        $this->loadBookingRelationships($cancelledBooking);

        return $this->successResponse(
            'Booking cancelled successfully.',
            [
                'booking' =>
                    new BookingResource($cancelledBooking),
            ]
        );
    }

    public function reject(
        AdminBookingClosureRequest $request,
        Booking $booking,
        BookingClosureService $closureService
    ): JsonResponse {
        $data = $request->validated();

        $rejectedBooking = DB::transaction(
            function () use (
                $request,
                $booking,
                $data,
                $closureService
            ) {
                $lockedBooking = Booking::query()
                    ->lockForUpdate()
                    ->findOrFail($booking->id);

                return $closureService->reject(
                    booking: $lockedBooking,
                    actor: $request->user(),
                    reason: $data['reason']
                );
            }
        );

        $this->loadBookingRelationships($rejectedBooking);

        return $this->successResponse(
            'Booking rejected successfully.',
            [
                'booking' =>
                    new BookingResource($rejectedBooking),
            ]
        );
    }

    private function loadBookingRelationships(
        Booking $booking
    ): void {
        $booking->load([
            'customer',
            'service.category',
            'latestAssignment.staffProfile.user',
            'latestAssignment.assignedBy',
            'cancelledBy',
            'rejectedBy',
        ]);
    }
}