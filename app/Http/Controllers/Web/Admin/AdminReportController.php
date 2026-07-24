<?php

namespace App\Http\Controllers\Web\Admin;

use App\Enums\BookingStatus;
use App\Enums\QuotationStatus;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\Quotation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminReportController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
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

        $dateFrom = Carbon::parse(
            $filters['date_from'] ?? now()->startOfMonth()->toDateString()
        )->startOfDay();
        $dateTo = Carbon::parse(
            $filters['date_to'] ?? now()->toDateString()
        )->endOfDay();

        $bookingQuery = Booking::query()->whereBetween(
            'created_at',
            [$dateFrom, $dateTo]
        );
        $quotationQuery = Quotation::query()->whereBetween(
            'created_at',
            [$dateFrom, $dateTo]
        );
        $invoiceQuery = Invoice::query()->whereBetween(
            'issued_at',
            [$dateFrom, $dateTo]
        );
        $paymentQuery = InvoicePayment::query()->whereBetween(
            'paid_at',
            [$dateFrom, $dateTo]
        );

        $bookingStatusCounts = (clone $bookingQuery)
            ->select('status')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $quotationStatusCounts = (clone $quotationQuery)
            ->select('status')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $invoiceSummary = (clone $invoiceQuery)
            ->selectRaw('COUNT(*) as invoice_count')
            ->selectRaw('COALESCE(SUM(total_amount), 0) as total_amount')
            ->selectRaw('COALESCE(SUM(paid_amount), 0) as paid_amount')
            ->selectRaw('COALESCE(SUM(remaining_amount), 0) as remaining_amount')
            ->first();

        $paymentSummary = (clone $paymentQuery)
            ->selectRaw('COUNT(*) as payment_count')
            ->selectRaw('COALESCE(SUM(amount), 0) as total_amount')
            ->first();

        $paymentMethods = (clone $paymentQuery)
            ->select('payment_method')
            ->selectRaw('COUNT(*) as payment_count')
            ->selectRaw('COALESCE(SUM(amount), 0) as total_amount')
            ->groupBy('payment_method')
            ->orderByDesc('total_amount')
            ->get();

        $topServices = (clone $bookingQuery)
            ->select('service_name')
            ->selectRaw('COUNT(*) as booking_count')
            ->selectRaw('COALESCE(SUM(service_price), 0) as service_revenue')
            ->groupBy('service_name')
            ->orderByDesc('booking_count')
            ->limit(8)
            ->get();

        $recentPayments = (clone $paymentQuery)
            ->with([
                'invoice.customer',
            ])
            ->latest('paid_at')
            ->limit(8)
            ->get();

        $bookingStatuses = collect(BookingStatus::cases())
            ->map(fn (BookingStatus $status) => [
                'label' => str($status->value)->replace('_', ' ')->title()->toString(),
                'value' => $bookingStatusCounts->get($status->value, 0),
            ]);

        $quotationStatuses = collect(QuotationStatus::cases())
            ->map(fn (QuotationStatus $status) => [
                'label' => str($status->value)->title()->toString(),
                'value' => $quotationStatusCounts->get($status->value, 0),
            ]);

        $quotationTotal = (int) $quotationStatusCounts->sum();
        $acceptedQuotations = (int) $quotationStatusCounts->get(
            QuotationStatus::Accepted->value,
            0
        );

        return view('admin.reports.index', [
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'bookingTotal' => (int) $bookingStatusCounts->sum(),
            'bookingStatuses' => $bookingStatuses,
            'quotationTotal' => $quotationTotal,
            'quotationStatuses' => $quotationStatuses,
            'quotationAcceptanceRate' => $quotationTotal > 0
                ? round(($acceptedQuotations / $quotationTotal) * 100)
                : 0,
            'invoiceSummary' => $invoiceSummary,
            'paymentSummary' => $paymentSummary,
            'paymentMethods' => $paymentMethods,
            'topServices' => $topServices,
            'recentPayments' => $recentPayments,
        ]);
    }
}
