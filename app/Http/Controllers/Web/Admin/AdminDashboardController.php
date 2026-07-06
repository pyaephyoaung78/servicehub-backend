<?php

namespace App\Http\Controllers\Web\Admin;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Enums\QuotationStatus;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Invoice;
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

        return view('admin.dashboard.index', [
            'stats' => $stats,
            'recentBookings' => $recentBookings,
        ]);
    }
}