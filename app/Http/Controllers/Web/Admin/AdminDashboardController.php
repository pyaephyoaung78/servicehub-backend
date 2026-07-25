<?php

namespace App\Http\Controllers\Web\Admin;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Enums\QuotationStatus;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\Quotation;
use App\Models\StaffProfile;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'pending_bookings' => Booking::query()
                ->where('status', BookingStatus::Pending)
                ->count(),

            'sent_quotations' => Quotation::query()
                ->where('status', QuotationStatus::Sent)
                ->count(),

            'accepted_quotations' => Quotation::query()
                ->where('status', QuotationStatus::Accepted)
                ->count(),

            'unpaid_invoices' => Invoice::query()
                ->where('payment_status', PaymentStatus::Unpaid)
                ->count(),

            'partial_invoices' => Invoice::query()
                ->where('payment_status', PaymentStatus::Partial)
                ->count(),

            'active_staff' => StaffProfile::query()
                ->where('is_active', true)
                ->count(),
        ];

        $recentBookings = Booking::query()
            ->with([
                'customer',
                'service.category',
                'quotation',
            ])
            ->latest()
            ->limit(8)
            ->get();

        $weeklyBookingActivity = collect(range(6, 0))
            ->map(function (int $daysAgo): array {
                $date = now()->subDays($daysAgo);

                return [
                    'label' => $date->format('D'),
                    'date' => $date->toDateString(),
                    'bookings' => Booking::query()
                        ->whereDate('created_at', $date)
                        ->count(),
                ];
            });

        $bookingStatusSummary = collect(BookingStatus::cases())
            ->map(fn (BookingStatus $status): array => [
                'label' => str($status->value)->replace('_', ' ')->title(),
                'value' => Booking::query()
                    ->where('status', $status)
                    ->count(),
            ]);

        $financialMetrics = [
            'collected_this_month' => InvoicePayment::query()
                ->whereBetween('paid_at', [now()->startOfMonth(), now()])
                ->sum('amount'),
            'outstanding_balance' => Invoice::query()
                ->whereIn('payment_status', [
                    PaymentStatus::Unpaid,
                    PaymentStatus::Partial,
                ])
                ->sum('remaining_amount'),
            'invoices_this_month' => Invoice::query()
                ->whereBetween('issued_at', [now()->startOfMonth(), now()])
                ->count(),
        ];

        return view('admin.dashboard.index', [
            'stats' => $stats,
            'recentBookings' => $recentBookings,
            'weeklyBookingActivity' => $weeklyBookingActivity,
            'weeklyBookingMaximum' => max(1, $weeklyBookingActivity->max('bookings')),
            'bookingStatusSummary' => $bookingStatusSummary,
            'financialMetrics' => $financialMetrics,
        ]);
    }
}
