<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\InvoicePayment;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminPaymentController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => [
                'nullable',
                'string',
                'max:255',
            ],
            'payment_method' => [
                'nullable',
                'string',
                'max:100',
            ],
            'date_from' => [
                'nullable',
                'date',
            ],
            'date_to' => [
                'nullable',
                'date',
                'after_or_equal:date_from',
            ],
        ]);

        $payments = InvoicePayment::query()
            ->with([
                'invoice.customer',
                'receivedBy',
            ])
            ->when(
                $filters['search'] ?? null,
                function ($query, $search) {
                    $query->where(function ($paymentQuery) use ($search) {
                        $paymentQuery
                            ->where('payment_method', 'like', "%{$search}%")
                            ->orWhere('note', 'like', "%{$search}%")
                            ->orWhereHas(
                                'invoice',
                                fn ($invoiceQuery) => $invoiceQuery
                                    ->where('invoice_no', 'like', "%{$search}%")
                                    ->orWhere('service_name', 'like', "%{$search}%")
                                    ->orWhereHas(
                                        'customer',
                                        fn ($customerQuery) => $customerQuery
                                            ->where('name', 'like', "%{$search}%")
                                            ->orWhere('email', 'like', "%{$search}%")
                                    )
                            );
                    });
                }
            )
            ->when(
                $filters['payment_method'] ?? null,
                fn ($query, $method) => $query->where(
                    'payment_method',
                    'like',
                    "%{$method}%"
                )
            )
            ->when(
                $filters['date_from'] ?? null,
                fn ($query, $dateFrom) => $query->whereDate(
                    'paid_at',
                    '>=',
                    $dateFrom
                )
            )
            ->when(
                $filters['date_to'] ?? null,
                fn ($query, $dateTo) => $query->whereDate(
                    'paid_at',
                    '<=',
                    $dateTo
                )
            )
            ->latest('paid_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.payments.index', [
            'payments' => $payments,
        ]);
    }

    public function show(InvoicePayment $payment): View
    {
        $payment->load([
            'invoice.customer',
            'invoice.issuedBy',
            'receivedBy',
        ]);

        return view('admin.payments.show', [
            'payment' => $payment,
        ]);
    }
}
