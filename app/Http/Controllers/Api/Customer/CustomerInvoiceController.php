<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Http\Resources\InvoiceResource;
use App\Models\Invoice;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerInvoiceController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $invoices = Invoice::query()
            ->where('customer_id', $request->user()->id)
            ->with([
                'booking',
                'payments.receivedBy',
            ])
            ->latest('issued_at')
            ->paginate(15);

        return $this->successResponse(
            'Invoices retrieved successfully.',
            InvoiceResource::collection($invoices)
                ->response()
                ->getData(true)
        );
    }

    public function show(
        Request $request,
        int $invoice
    ): JsonResponse {
        $invoice = Invoice::query()
            ->where('customer_id', $request->user()->id)
            ->with([
                'booking',
                'payments.receivedBy',
            ])
            ->findOrFail($invoice);

        return $this->successResponse(
            'Invoice retrieved successfully.',
            [
                'invoice' => new InvoiceResource($invoice),
            ]
        );
    }
}