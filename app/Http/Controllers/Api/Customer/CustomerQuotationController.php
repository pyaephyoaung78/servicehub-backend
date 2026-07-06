<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\CustomerRespondQuotationRequest;
use App\Http\Resources\QuotationResource;
use App\Models\Quotation;
use App\Services\QuotationService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerQuotationController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $quotations = Quotation::query()
            ->where('customer_id', $request->user()->id)
            ->with([
                'createdBy',
                'booking.service.category',
            ])
            ->latest()
            ->paginate(15);

        return $this->successResponse(
            'Quotations retrieved successfully.',
            QuotationResource::collection($quotations)
                ->response()
                ->getData(true)
        );
    }

    public function show(
        Request $request,
        int $quotation
    ): JsonResponse {
        $quotation = Quotation::query()
            ->where('customer_id', $request->user()->id)
            ->with([
                'createdBy',
                'booking.service.category',
            ])
            ->findOrFail($quotation);

        return $this->successResponse(
            'Quotation retrieved successfully.',
            [
                'quotation' => new QuotationResource($quotation),
            ]
        );
    }

    public function respond(
        CustomerRespondQuotationRequest $request,
        int $quotation,
        QuotationService $quotationService
    ): JsonResponse {
        $data = $request->validated();

        $quotation = DB::transaction(function () use (
            $request,
            $quotation,
            $quotationService,
            $data
        ) {
            $ownedQuotation = Quotation::query()
                ->where('customer_id', $request->user()->id)
                ->lockForUpdate()
                ->findOrFail($quotation);

            if ($data['action'] === 'accept') {
                return $quotationService->accept(
                    quotation: $ownedQuotation,
                    customer: $request->user(),
                    note: $data['note'] ?? null
                );
            }

            return $quotationService->reject(
                quotation: $ownedQuotation,
                customer: $request->user(),
                note: $data['note'] ?? null
            );
        });

        $quotation->load([
            'createdBy',
            'booking.service.category',
        ]);

        return $this->successResponse(
            'Quotation response saved successfully.',
            [
                'quotation' => new QuotationResource($quotation),
            ]
        );
    }
}