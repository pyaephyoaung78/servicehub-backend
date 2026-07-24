<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePaymentProofRequest;
use App\Http\Resources\PaymentProofResource;
use App\Models\Invoice;
use App\Models\PaymentProof;
use App\Services\PaymentProofService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerPaymentProofController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $paymentProofs = PaymentProof::query()
            ->where('customer_id', $request->user()->id)
            ->with('invoice')
            ->latest()
            ->paginate(15);

        return $this->successResponse(
            'Payment proofs retrieved successfully.',
            PaymentProofResource::collection($paymentProofs)
                ->response()
                ->getData(true)
        );
    }

    public function store(
        StorePaymentProofRequest $request,
        Invoice $invoice,
        PaymentProofService $paymentProofService
    ): JsonResponse {
        $paymentProof = $paymentProofService->submit(
            invoice: $invoice,
            customer: $request->user(),
            proof: $request->file('proof'),
            data: $request->validated()
        );

        $paymentProof->load('invoice');

        return $this->successResponse(
            'Payment proof submitted successfully.',
            [
                'payment_proof' => new PaymentProofResource($paymentProof),
            ],
            201
        );
    }

    public function show(
        Request $request,
        int $paymentProof
    ): JsonResponse {
        $paymentProof = PaymentProof::query()
            ->where('customer_id', $request->user()->id)
            ->with([
                'invoice',
                'invoicePayment',
                'reviewedBy',
            ])
            ->findOrFail($paymentProof);

        return $this->successResponse(
            'Payment proof retrieved successfully.',
            [
                'payment_proof' => new PaymentProofResource($paymentProof),
            ]
        );
    }
}
