<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminBookingIndexRequest;
use App\Http\Resources\BookingResource;
use App\Models\Booking;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class AdminBookingController extends Controller
{
    use ApiResponse;

    public function index(
        AdminBookingIndexRequest $request
    ): JsonResponse {
        $data = $request->validated();

        $query = Booking::query()
            ->with([
                'customer',
                'service.category',
                'latestAssignment.staffProfile.user',
                'latestAssignment.assignedBy',
                'cancelledBy',
                'rejectedBy',
                'invoice',
            ]);

        if (! empty($data['status'])) {
            $query->where('status', $data['status']);
        }

        if (! empty($data['service_id'])) {
            $query->where(
                'service_id',
                $data['service_id']
            );
        }

        if (! empty($data['date_from'])) {
            $query->whereDate(
                'scheduled_at',
                '>=',
                $data['date_from']
            );
        }

        if (! empty($data['date_to'])) {
            $query->whereDate(
                'scheduled_at',
                '<=',
                $data['date_to']
            );
        }

        if (! empty($data['search'])) {
            $search = $data['search'];

            $query->where(function ($bookingQuery) use ($search) {
                $bookingQuery
                    ->where('service_name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%")
                    ->orWhereHas(
                        'customer',
                        function ($customerQuery) use ($search) {
                            $customerQuery
                                ->where(
                                    'name',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'email',
                                    'like',
                                    "%{$search}%"
                                );
                        }
                    );
            });
        }

        $bookings = $query
            ->orderBy('scheduled_at')
            ->paginate($data['per_page'] ?? 20)
            ->withQueryString();

        return $this->successResponse(
            'Admin bookings retrieved successfully.',
            BookingResource::collection($bookings)
                ->response()
                ->getData(true)
        );
    }

    public function show(Booking $booking): JsonResponse
    {
        $booking->load([
            'customer',
            'service.category',
            'latestAssignment.staffProfile.user',
            'latestAssignment.assignedBy',
            'cancelledBy',
            'rejectedBy',
            'invoice',
        ]);

        return $this->successResponse(
            'Booking retrieved successfully.',
            [
                'booking' => new BookingResource($booking),
            ]
        );
    }
}
