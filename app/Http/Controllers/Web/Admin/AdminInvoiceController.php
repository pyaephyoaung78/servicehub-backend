<?php

namespace App\Http\Controllers\Web\Admin;

use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\AdminStoreInvoiceRequest;
use App\Http\Requests\StoreInvoicePaymentRequest;
use App\Models\Booking;
use App\Models\Invoice;
use App\Services\InvoiceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminInvoiceController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'payment_status' => [
                'nullable',
                Rule::enum(PaymentStatus::class),
            ],
            'search' => [
                'nullable',
                'string',
                'max:255',
            ],
        ]);

        $invoices = Invoice::query()
            ->with([
                'customer',
                'booking',
                'issuedBy',
            ])
            ->when(
                $filters['payment_status'] ?? null,
                fn ($query, $status) => $query->where('payment_status', $status)
            )
            ->when(
                $filters['search'] ?? null,
                function ($query, $search) {
                    $query->where(function ($invoiceQuery) use ($search) {
                        $invoiceQuery
                            ->where('invoice_no', 'like', "%{$search}%")
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
            ->latest('issued_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.invoices.index', [
            'invoices' => $invoices,
            'statuses' => PaymentStatus::cases(),
        ]);
    }

    public function create(Request $request): View
    {
        $eligibleBookings = Booking::query()
            ->with('customer')
            ->where('status', 'completed')
            ->whereDoesntHave('invoice')
            ->orderByDesc('completed_at')
            ->get();

        $selectedBooking = $eligibleBookings->firstWhere(
            'id',
            $request->integer('booking')
        );

        return view('admin.invoices.create', [
            'eligibleBookings' => $eligibleBookings,
            'selectedBooking' => $selectedBooking,
        ]);
    }

    public function store(
        AdminStoreInvoiceRequest $request,
        InvoiceService $invoiceService
    ): RedirectResponse {
        $data = $request->validated();

        $invoice = DB::transaction(function () use (
            $request,
            $data,
            $invoiceService
        ) {
            $booking = Booking::query()
                ->lockForUpdate()
                ->findOrFail($data['booking_id']);

            return $invoiceService->createFromBooking(
                booking: $booking,
                admin: $request->user(),
                data: $data
            );
        });

        return redirect()
            ->route('admin.invoices.show', $invoice)
            ->with('success', 'Invoice created successfully.');
    }

    public function show(Invoice $invoice): View
    {
        $invoice->load([
            'customer',
            'issuedBy',
            'booking',
            'payments.receivedBy',
        ]);

        return view('admin.invoices.show', [
            'invoice' => $invoice,
        ]);
    }

    public function recordPayment(
        StoreInvoicePaymentRequest $request,
        Invoice $invoice,
        InvoiceService $invoiceService
    ): RedirectResponse {
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

        return redirect()
            ->route('admin.invoices.show', $updatedInvoice)
            ->with('success', 'Payment recorded successfully.');
    }
}
