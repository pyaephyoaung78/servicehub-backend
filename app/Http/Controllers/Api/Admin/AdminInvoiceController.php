<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInvoicePaymentRequest;
use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Resources\InvoiceResource;
use App\Models\Booking;
use App\Models\Invoice;
use App\Services\InvoiceService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AdminInvoiceController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'payment_status' => [
                'nullable',
                Rule::enum(PaymentStatus::class),
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

        $invoices = Invoice::query()
            ->with([
                'customer',
                'issuedBy',
                'booking',
                'payments.receivedBy',
            ])
            ->when(
                $validated['payment_status'] ?? null,
                fn ($query, $status) =>
                    $query->where('payment_status', $status)
            )
            ->when(
                $validated['search'] ?? null,
                function ($query, $search) {
                    $query->where(function ($subQuery) use ($search) {
                        $subQuery
                            ->where('invoice_no', 'like', "%{$search}%")
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
            ->latest('issued_at')
            ->paginate($validated['per_page'] ?? 15);

        return $this->successResponse(
            'Invoices retrieved successfully.',
            InvoiceResource::collection($invoices)
                ->response()
                ->getData(true)
        );
    }

    public function storeFromBooking(
        StoreInvoiceRequest $request,
        Booking $booking,
        InvoiceService $invoiceService
    ): JsonResponse {
        $invoice = DB::transaction(function () use (
            $request,
            $booking,
            $invoiceService
        ) {
            $lockedBooking = Booking::query()
                ->lockForUpdate()
                ->findOrFail($booking->id);

            return $invoiceService->createFromBooking(
                booking: $lockedBooking,
                admin: $request->user(),
                data: $request->validated()
            );
        });

        $invoice->load([
            'customer',
            'issuedBy',
            'booking',
            'payments.receivedBy',
        ]);

        return $this->successResponse(
            'Invoice created successfully.',
            [
                'invoice' => new InvoiceResource($invoice),
            ],
            201
        );
    }

    public function show(Invoice $invoice): JsonResponse
    {
        $invoice->load([
            'customer',
            'issuedBy',
            'booking',
            'payments.receivedBy',
        ]);

        return $this->successResponse(
            'Invoice retrieved successfully.',
            [
                'invoice' => new InvoiceResource($invoice),
            ]
        );
    }

    public function recordPayment(
        StoreInvoicePaymentRequest $request,
        Invoice $invoice,
        InvoiceService $invoiceService
    ): JsonResponse {
        $updatedInvoice = DB::transaction(function () use (
            $request,
            $invoice,
            $invoiceService
        ) {
            $lockedInvoice = Invoice::query()
                ->lockForUpdate()
                ->findOrFail($invoice->id);

            return $invoiceService->recordPayment(
                invoice: $lockedInvoice,
                admin: $request->user(),
                data: $request->validated()
            );
        });

        $updatedInvoice->load([
            'customer',
            'issuedBy',
            'booking',
            'payments.receivedBy',
        ]);

        return $this->successResponse(
            'Payment recorded successfully.',
            [
                'invoice' => new InvoiceResource($updatedInvoice),
            ]
        );
    }
}