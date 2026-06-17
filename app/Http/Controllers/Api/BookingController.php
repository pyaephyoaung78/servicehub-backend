<?php

namespace App\Http\Controllers\Api;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookingRequest;
use App\Http\Resources\BookingResource;
use App\Models\Booking;
use App\Models\Service;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $query = Booking::query()
            ->where('customer_id', $request->user()->id)
            ->with('service.category');

        if ($request->filled('status')) {
            $validStatuses = array_column(
                BookingStatus::cases(),
                'value'
            );

            if (! in_array($request->string('status')->toString(), $validStatuses, true)) {
                return $this->errorResponse(
                    'Invalid booking status.',
                    null,
                    422
                );
            }

            $query->where('status', $request->string('status')->toString());
        }

        $bookings = $query
            ->latest()
            ->paginate(20);

        return $this->successResponse(
            'Bookings retrieved successfully.',
            BookingResource::collection($bookings)
                ->response()
                ->getData(true)
        );
    }

    public function store(StoreBookingRequest $request): JsonResponse
    {
        $data = $request->validated();

        /*
         * Never trust service name or price from Flutter.
         * Read the current trusted service record from the database.
         */
        $service = Service::query()
            ->whereKey($data['service_id'])
            ->where('is_active', true)
            ->firstOrFail();

        $booking = Booking::create([
            'customer_id' => $request->user()->id,
            'service_id' => $service->id,

            // Historical snapshots
            'service_name' => $service->name,
            'service_price' => $service->base_price,

            'scheduled_at' => $data['scheduled_at'],
            'phone' => $data['phone'],
            'address' => $data['address'],
            'customer_note' => $data['customer_note'] ?? null,

            // Customers cannot control this value.
            'status' => BookingStatus::Pending,
        ]);

        $booking->load('service.category');

        return $this->successResponse(
            'Booking created successfully.',
            [
                'booking' => new BookingResource($booking),
            ],
            201
        );
    }

    public function show(Request $request, int $booking): JsonResponse
    {
        /*
         * Scope the query to the authenticated customer.
         * A customer cannot retrieve another customer's booking.
         */
        $ownedBooking = Booking::query()
            ->where('customer_id', $request->user()->id)
            ->with('service.category')
            ->findOrFail($booking);

        return $this->successResponse(
            'Booking retrieved successfully.',
            [
                'booking' => new BookingResource($ownedBooking),
            ]
        );
    }
}
