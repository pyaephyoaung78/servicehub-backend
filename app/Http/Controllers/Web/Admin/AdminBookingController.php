<?php

namespace App\Http\Controllers\Web\Admin;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\AdminBookingClosureRequest;
use App\Http\Requests\AdminBookingIndexRequest;
use App\Models\Booking;
use App\Services\BookingClosureService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminBookingController extends Controller
{
    public function index(AdminBookingIndexRequest $request): View
    {
        $data = $request->validated();

        $bookings = Booking::query()
            ->with([
                'customer',
                'service.category',
                'quotation',
                'invoice',
                'latestAssignment.staffProfile.user',
            ])
            ->when(
                $data['status'] ?? null,
                fn ($query, $status) => $query->where('status', $status)
            )
            ->when(
                $data['service_id'] ?? null,
                fn ($query, $serviceId) => $query->where('service_id', $serviceId)
            )
            ->when(
                $data['date_from'] ?? null,
                fn ($query, $dateFrom) => $query->whereDate('scheduled_at', '>=', $dateFrom)
            )
            ->when(
                $data['date_to'] ?? null,
                fn ($query, $dateTo) => $query->whereDate('scheduled_at', '<=', $dateTo)
            )
            ->when(
                $data['search'] ?? null,
                function ($query, $search) {
                    $query->where(function ($bookingQuery) use ($search) {
                        $bookingQuery
                            ->where('service_name', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%")
                            ->orWhere('address', 'like', "%{$search}%")
                            ->orWhereHas(
                                'customer',
                                fn ($customerQuery) => $customerQuery
                                    ->where('name', 'like', "%{$search}%")
                                    ->orWhere('email', 'like', "%{$search}%")
                            );
                    });
                }
            )
            ->orderBy('scheduled_at')
            ->paginate($data['per_page'] ?? 20)
            ->withQueryString();

        return view('admin.bookings.index', [
            'bookings' => $bookings,
            'statuses' => BookingStatus::cases(),
        ]);
    }

    public function show(Booking $booking): View
    {
        $booking->load([
            'customer',
            'service.category',
            'quotation.createdBy',
            'invoice.payments.receivedBy',
            'latestAssignment.staffProfile.user',
            'latestAssignment.assignedBy',
            'cancelledBy',
            'rejectedBy',
        ]);

        return view('admin.bookings.show', [
            'booking' => $booking,
        ]);
    }

    public function cancel(
        AdminBookingClosureRequest $request,
        Booking $booking,
        BookingClosureService $closureService
    ): RedirectResponse {
        $cancelledBooking = DB::transaction(function () use (
            $request,
            $booking,
            $closureService
        ) {
            $lockedBooking = Booking::query()
                ->lockForUpdate()
                ->findOrFail($booking->id);

            return $closureService->cancel(
                booking: $lockedBooking,
                actor: $request->user(),
                reason: $request->validated('reason'),
                isAdmin: true
            );
        });

        return redirect()
            ->route('admin.bookings.show', $cancelledBooking)
            ->with('success', 'Booking cancelled successfully.');
    }

    public function reject(
        AdminBookingClosureRequest $request,
        Booking $booking,
        BookingClosureService $closureService
    ): RedirectResponse {
        $rejectedBooking = DB::transaction(function () use (
            $request,
            $booking,
            $closureService
        ) {
            $lockedBooking = Booking::query()
                ->lockForUpdate()
                ->findOrFail($booking->id);

            return $closureService->reject(
                booking: $lockedBooking,
                actor: $request->user(),
                reason: $request->validated('reason')
            );
        });

        return redirect()
            ->route('admin.bookings.show', $rejectedBooking)
            ->with('success', 'Booking rejected successfully.');
    }
}
