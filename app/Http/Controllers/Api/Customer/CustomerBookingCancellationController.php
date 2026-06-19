<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\CustomerCancelBookingRequest;
use App\Http\Resources\BookingResource;
use App\Models\Booking;
use App\Services\BookingClosureService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class CustomerBookingCancellationController extends Controller
{
    use ApiResponse;

    public function cancel(
        CustomerCancelBookingRequest $request,
        int $booking,
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
                $ownedBooking = Booking::query()
                    ->where(
                        'customer_id',
                        $request->user()->id
                    )
                    ->lockForUpdate()
                    ->findOrFail($booking);

                return $closureService->cancel(
                    booking: $ownedBooking,
                    actor: $request->user(),
                    reason: $data['reason'] ?? null,
                    isAdmin: false
                );
            }
        );

        $cancelledBooking->load([
            'customer',
            'service.category',
            'latestAssignment.staffProfile.user',
            'latestAssignment.assignedBy',
            'cancelledBy',
        ]);

        return $this->successResponse(
            'Booking cancelled successfully.',
            [
                'booking' =>
                    new BookingResource($cancelledBooking),
            ]
        );
    }
}