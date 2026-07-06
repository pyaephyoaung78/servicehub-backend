<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enums\QuotationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreQuotationRequest;
use App\Http\Resources\QuotationResource;
use App\Models\Booking;
use App\Models\Quotation;
use App\Services\QuotationService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AdminQuotationController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'status' => [
                'nullable',
                Rule::enum(QuotationStatus::class),
            ],

            'search' => [
                'nullable',
                'string',
                'max:255',
            ],

            'per_page' => [
                'nullable',
                'integer',
                'min:1',
                'max:100',
            ],
        ]);

        $quotations = Quotation::query()
            ->with([
                'customer',
                'createdBy',
                'booking.service.category',
            ])
            ->when(
                $validated['status'] ?? null,
                fn ($query, $status) =>
                    $query->where('status', $status)
            )
            ->when(
                $validated['search'] ?? null,
                function ($query, $search) {
                    $query->where(function ($subQuery) use ($search) {
                        $subQuery
                            ->where('quotation_no', 'like', "%{$search}%")
                            ->orWhere('service_name', 'like', "%{$search}%")
                            ->orWhereHas(
                                'customer',
                                fn ($customerQuery) =>
                                    $customerQuery
                                        ->where('name', 'like', "%{$search}%")
                                        ->orWhere('email', 'like', "%{$search}%")
                            );
                    });
                }
            )
            ->latest()
            ->paginate($validated['per_page'] ?? 15);

        return $this->successResponse(
            'Quotations retrieved successfully.',
            QuotationResource::collection($quotations)
                ->response()
                ->getData(true)
        );
    }

    public function storeForBooking(
        StoreQuotationRequest $request,
        Booking $booking,
        QuotationService $quotationService
    ): JsonResponse {
        $quotation = DB::transaction(function () use (
            $request,
            $booking,
            $quotationService
        ) {
            $lockedBooking = Booking::query()
                ->lockForUpdate()
                ->findOrFail($booking->id);

            return $quotationService->createForBooking(
                booking: $lockedBooking,
                admin: $request->user(),
                data: $request->validated()
            );
        });

        $quotation->load([
            'customer',
            'createdBy',
            'booking.service.category',
        ]);

        return $this->successResponse(
            'Quotation created successfully.',
            [
                'quotation' => new QuotationResource($quotation),
            ],
            201
        );
    }

    public function show(Quotation $quotation): JsonResponse
    {
        $quotation->load([
            'customer',
            'createdBy',
            'booking.service.category',
        ]);

        return $this->successResponse(
            'Quotation retrieved successfully.',
            [
                'quotation' => new QuotationResource($quotation),
            ]
        );
    }
}