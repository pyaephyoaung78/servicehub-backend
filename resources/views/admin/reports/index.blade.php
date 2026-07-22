@extends('admin.layouts.app')

@section('title', 'Reports')
@section('page_title', 'Reports')

@section('content')
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-slate-900">Reports</h1>
        <p class="mt-1 text-slate-500">
            Business activity from {{ $dateFrom->format('d M Y') }} to {{ $dateTo->format('d M Y') }}.
        </p>
    </div>

    <form method="GET" class="mb-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="grid gap-4 md:grid-cols-3">
            <label class="block">
                <span class="mb-1 block text-sm font-medium text-slate-700">From</span>
                <input
                    type="date"
                    name="date_from"
                    value="{{ request('date_from', $dateFrom->toDateString()) }}"
                    class="w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500"
                >
            </label>
            <label class="block">
                <span class="mb-1 block text-sm font-medium text-slate-700">To</span>
                <input
                    type="date"
                    name="date_to"
                    value="{{ request('date_to', $dateTo->toDateString()) }}"
                    class="w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500"
                >
            </label>
            <div class="flex items-end gap-3">
                <button type="submit" class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">
                    Apply period
                </button>
                <a href="{{ route('admin.reports.index') }}" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                    This month
                </a>
            </div>
        </div>
        <p class="mt-3 text-xs text-slate-500">
            Bookings and quotations use their creation date. Invoices use issued date. Payments use received date.
        </p>
    </form>

    <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
        <x-admin.stat-card
            title="Bookings"
            :value="$bookingTotal"
            description="Created in this period"
        />
        <x-admin.stat-card
            title="Quotations"
            :value="$quotationTotal"
            description="Created in this period"
        />
        <x-admin.stat-card
            title="Invoiced"
            :value="number_format((float) $invoiceSummary->total_amount, 0) . ' MMK'"
            description="Total invoice value"
        />
        <x-admin.stat-card
            title="Collected"
            :value="number_format((float) $paymentSummary->total_amount, 0) . ' MMK'"
            description="Payments received"
        />
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-2">
        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="font-semibold text-slate-900">Booking status</h2>
                    <p class="mt-1 text-sm text-slate-500">Status of bookings created in this period.</p>
                </div>
                <a href="{{ route('admin.bookings.index') }}" class="text-sm font-medium text-blue-700 hover:text-blue-900">
                    View bookings →
                </a>
            </div>

            <div class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-4">
                @foreach ($bookingStatuses as $status)
                    <div class="rounded-xl bg-slate-50 p-4">
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-500">{{ $status['label'] }}</p>
                        <p class="mt-2 text-2xl font-bold text-slate-900">{{ $status['value'] }}</p>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="font-semibold text-slate-900">Quotation conversion</h2>
                    <p class="mt-1 text-sm text-slate-500">Customer responses to quotations created in this period.</p>
                </div>
                <a href="{{ route('admin.quotations.index') }}" class="text-sm font-medium text-blue-700 hover:text-blue-900">
                    View quotations →
                </a>
            </div>

            <div class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-4">
                @foreach ($quotationStatuses as $status)
                    <div class="rounded-xl bg-slate-50 p-4">
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-500">{{ $status['label'] }}</p>
                        <p class="mt-2 text-2xl font-bold text-slate-900">{{ $status['value'] }}</p>
                    </div>
                @endforeach
            </div>

            <div class="mt-5 rounded-xl bg-emerald-50 p-4">
                <p class="text-sm text-emerald-800">Acceptance rate</p>
                <p class="mt-1 text-2xl font-bold text-emerald-900">{{ $quotationAcceptanceRate }}%</p>
            </div>
        </section>
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-3">
        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm xl:col-span-2">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="font-semibold text-slate-900">Financial summary</h2>
                    <p class="mt-1 text-sm text-slate-500">Invoices issued during this period.</p>
                </div>
                <a href="{{ route('admin.invoices.index') }}" class="text-sm font-medium text-blue-700 hover:text-blue-900">
                    View invoices →
                </a>
            </div>

            <div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <p class="text-sm text-slate-500">Invoices</p>
                    <p class="mt-1 text-xl font-bold text-slate-900">{{ $invoiceSummary->invoice_count }}</p>
                </div>
                <div>
                    <p class="text-sm text-slate-500">Invoiced</p>
                    <p class="mt-1 text-xl font-bold text-slate-900">{{ number_format((float) $invoiceSummary->total_amount, 0) }} MMK</p>
                </div>
                <div>
                    <p class="text-sm text-slate-500">Paid on invoices</p>
                    <p class="mt-1 text-xl font-bold text-emerald-700">{{ number_format((float) $invoiceSummary->paid_amount, 0) }} MMK</p>
                </div>
                <div>
                    <p class="text-sm text-slate-500">Outstanding</p>
                    <p class="mt-1 text-xl font-bold text-amber-700">{{ number_format((float) $invoiceSummary->remaining_amount, 0) }} MMK</p>
                </div>
            </div>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="font-semibold text-slate-900">Payment methods</h2>
                    <p class="mt-1 text-sm text-slate-500">{{ $paymentSummary->payment_count }} payment{{ (int) $paymentSummary->payment_count === 1 ? '' : 's' }} received.</p>
                </div>
                <a href="{{ route('admin.payments.index') }}" class="text-sm font-medium text-blue-700 hover:text-blue-900">
                    View payments →
                </a>
            </div>

            <div class="mt-5 space-y-3">
                @forelse ($paymentMethods as $method)
                    <div class="flex items-center justify-between gap-4 rounded-xl bg-slate-50 px-4 py-3">
                        <div>
                            <p class="font-medium text-slate-900">{{ $method->payment_method ?? 'Not specified' }}</p>
                            <p class="text-xs text-slate-500">{{ $method->payment_count }} payment{{ (int) $method->payment_count === 1 ? '' : 's' }}</p>
                        </div>
                        <p class="font-semibold text-emerald-700">{{ number_format((float) $method->total_amount, 0) }} MMK</p>
                    </div>
                @empty
                    <p class="py-6 text-center text-sm text-slate-500">No payments in this period.</p>
                @endforelse
            </div>
        </section>
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-2">
        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="font-semibold text-slate-900">Top services</h2>
            <p class="mt-1 text-sm text-slate-500">Most requested services by booking count.</p>

            <div class="mt-5 overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="border-b border-slate-200 text-left text-xs uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="pb-3">Service</th>
                            <th class="pb-3 text-right">Bookings</th>
                            <th class="pb-3 text-right">Listed value</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($topServices as $service)
                            <tr>
                                <td class="py-3 font-medium text-slate-900">{{ $service->service_name }}</td>
                                <td class="py-3 text-right text-slate-600">{{ $service->booking_count }}</td>
                                <td class="py-3 text-right text-slate-600">{{ number_format((float) $service->service_revenue, 0) }} MMK</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="py-8 text-center text-slate-500">No bookings in this period.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="font-semibold text-slate-900">Recent collections</h2>
                    <p class="mt-1 text-sm text-slate-500">Latest payments received in this period.</p>
                </div>
                <a href="{{ route('admin.payments.index') }}" class="text-sm font-medium text-blue-700 hover:text-blue-900">
                    View all →
                </a>
            </div>

            <div class="mt-5 space-y-3">
                @forelse ($recentPayments as $payment)
                    <a href="{{ route('admin.payments.show', $payment) }}" class="flex items-center justify-between gap-4 rounded-xl bg-slate-50 px-4 py-3 hover:bg-slate-100">
                        <div class="min-w-0">
                            <p class="truncate font-medium text-slate-900">{{ $payment->invoice?->customer?->name ?? 'Unknown customer' }}</p>
                            <p class="text-xs text-slate-500">{{ $payment->invoice?->invoice_no }} · {{ $payment->paid_at?->format('d M Y, h:i A') }}</p>
                        </div>
                        <p class="whitespace-nowrap font-semibold text-emerald-700">{{ number_format((float) $payment->amount, 0) }} MMK</p>
                    </a>
                @empty
                    <p class="py-6 text-center text-sm text-slate-500">No payments in this period.</p>
                @endforelse
            </div>
        </section>
    </div>
@endsection
