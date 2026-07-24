<?php

namespace App\Http\Controllers\Web\Admin;

use App\Enums\QuotationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\AdminStoreQuotationRequest;
use App\Models\Booking;
use App\Models\Quotation;
use App\Services\QuotationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminQuotationController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'status' => [
                'nullable',
                Rule::enum(QuotationStatus::class),
            ],
            'search' => [
                'nullable',
                'string',
                'max:255',
            ],
        ]);

        $quotations = Quotation::query()
            ->with([
                'customer',
                'createdBy',
                'booking',
            ])
            ->when(
                $filters['status'] ?? null,
                fn ($query, $status) => $query->where('status', $status)
            )
            ->when(
                $filters['search'] ?? null,
                function ($query, $search) {
                    $query->where(function ($quotationQuery) use ($search) {
                        $quotationQuery
                            ->where('quotation_no', 'like', "%{$search}%")
                            ->orWhere('service_name', 'like', "%{$search}%")
                            ->orWhereHas(
                                'customer',
                                fn ($customerQuery) => $customerQuery
                                    ->where('name', 'like', "%{$search}%")
                                    ->orWhere('email', 'like', "%{$search}%")
                            );
                    });
                }
            )
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.quotations.index', [
            'quotations' => $quotations,
            'statuses' => QuotationStatus::cases(),
        ]);
    }

    public function create(Request $request): View
    {
        $eligibleBookings = Booking::query()
            ->with('customer')
            ->where('status', 'pending')
            ->whereDoesntHave('quotation')
            ->orderBy('scheduled_at')
            ->get();

        $selectedBooking = $eligibleBookings->firstWhere(
            'id',
            $request->integer('booking')
        );

        return view('admin.quotations.create', [
            'eligibleBookings' => $eligibleBookings,
            'selectedBooking' => $selectedBooking,
        ]);
    }

    public function store(
        AdminStoreQuotationRequest $request,
        QuotationService $quotationService
    ): RedirectResponse {
        $data = $request->validated();

        $quotation = DB::transaction(function () use (
            $request,
            $data,
            $quotationService
        ) {
            $booking = Booking::query()
                ->lockForUpdate()
                ->findOrFail($data['booking_id']);

            return $quotationService->createForBooking(
                booking: $booking,
                admin: $request->user(),
                data: $data
            );
        });

        return redirect()
            ->route('admin.quotations.show', $quotation)
            ->with('success', 'Quotation created and sent successfully.');
    }

    public function show(Quotation $quotation): View
    {
        $quotation->load([
            'customer',
            'createdBy',
            'booking.service.category',
        ]);

        return view('admin.quotations.show', [
            'quotation' => $quotation,
        ]);
    }
}
