@extends('admin.layouts.app')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard Overview')

@section('content')
<section class="mb-8 flex flex-col gap-5 border-b border-slate-200 pb-8 xl:flex-row xl:items-end xl:justify-between">
    <div>
        <p class="text-sm font-medium text-teal-700">Operations overview</p>
        <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950 sm:text-4xl">
            Keep work moving.
        </h1>
        <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-600 sm:text-base">
            Review customer requests, progress accepted quotations, and keep payments up to date.
        </p>
    </div>

    <div class="flex flex-wrap gap-3">
        <a
            href="{{ route('admin.bookings.index') }}"
            class="rounded-lg bg-teal-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-teal-800 focus:outline-none focus:ring-2 focus:ring-teal-600 focus:ring-offset-2 active:translate-y-px">
            Review bookings
        </a>
        <a
            href="{{ route('admin.quotations.create') }}"
            class="rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-slate-400 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-teal-600 focus:ring-offset-2 active:translate-y-px">
            Create quotation
        </a>
    </div>
</section>

    <section>
    <div class="mb-4">
        <h2 class="text-lg font-semibold tracking-tight text-slate-950">Work requiring attention</h2>
        <p class="mt-1 text-sm text-slate-500">Counts update from the current ServiceHub workflow.</p>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        <x-admin.stat-card
            title="Pending bookings"
            :value="$stats['pending_bookings']"
            description="Customer requests waiting for review"
            :href="route('admin.bookings.index', ['status' => 'pending'])"
            action-label="Review" />

        <x-admin.stat-card
            title="Sent quotations"
            :value="$stats['sent_quotations']"
            description="Waiting for a customer response"
            :href="route('admin.quotations.index', ['status' => 'sent'])"
            action-label="View" />

        <x-admin.stat-card
            title="Accepted quotations"
            :value="$stats['accepted_quotations']"
            description="Ready for staff assignment"
            :href="route('admin.quotations.index', ['status' => 'accepted'])"
            action-label="Assign" />

        <x-admin.stat-card
            title="Unpaid invoices"
            :value="$stats['unpaid_invoices']"
            description="No payment has been recorded"
            :href="route('admin.invoices.index', ['payment_status' => 'unpaid'])"
            action-label="Review" />

        <x-admin.stat-card
            title="Partially paid"
            :value="$stats['partial_invoices']"
            description="Payment is still outstanding"
            :href="route('admin.invoices.index', ['payment_status' => 'partial'])"
            action-label="View" />

        <x-admin.stat-card
            title="Active staff"
            :value="$stats['active_staff']"
            description="Available workforce records"
            :href="route('admin.staff.index')"
            action-label="Manage" />
        </div>
    </section>

    <div class="mt-8 grid gap-6 xl:grid-cols-3">
        <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-[0_1px_2px_rgba(15,60,58,0.05)] xl:col-span-2">
            <div class="border-b border-slate-200 px-5 py-5 sm:px-6">
                <h2 class="text-lg font-semibold tracking-tight text-slate-950">Booking volume</h2>
                <p class="mt-1 text-sm text-slate-500">Bookings created over the last seven days.</p>
            </div>

            <div class="grid h-56 grid-cols-7 items-end gap-3 px-5 pb-5 pt-8 sm:h-64 sm:px-6 sm:pb-6">
                @foreach ($weeklyBookingActivity as $day)
                    <div class="flex h-full min-w-0 flex-col justify-end text-center">
                        <p class="mb-2 text-sm font-semibold text-slate-700">{{ $day['bookings'] }}</p>
                        <div class="flex flex-1 items-end rounded-t-lg bg-teal-50 px-1">
                            <div
                                class="w-full rounded-t-md bg-teal-700 transition"
                                style="height: {{ max(8, round(($day['bookings'] / $weeklyBookingMaximum) * 100)) }}%"
                                title="{{ $day['date'] }}: {{ $day['bookings'] }} bookings"
                            ></div>
                        </div>
                        <p class="mt-2 text-xs font-medium text-slate-500">{{ $day['label'] }}</p>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="rounded-xl border border-teal-100 bg-teal-50/40 p-5 shadow-[0_1px_2px_rgba(15,60,58,0.05)] sm:p-6">
            <h2 class="text-lg font-semibold tracking-tight text-teal-950">Financial position</h2>
            <p class="mt-1 text-sm text-teal-900/70">Live totals based on recorded invoices and payments.</p>

            <dl class="mt-5 divide-y divide-teal-100">
                <div class="py-4 first:pt-0">
                    <dt class="text-sm text-teal-900/70">Collected this month</dt>
                    <dd class="mt-1 text-xl font-semibold tracking-tight text-emerald-700">{{ number_format((float) $financialMetrics['collected_this_month'], 0) }} MMK</dd>
                </div>
                <div class="py-4">
                    <dt class="text-sm text-teal-900/70">Outstanding balance</dt>
                    <dd class="mt-1 text-xl font-semibold tracking-tight text-amber-700">{{ number_format((float) $financialMetrics['outstanding_balance'], 0) }} MMK</dd>
                </div>
                <div class="pb-0 pt-4">
                    <dt class="text-sm text-teal-900/70">Invoices this month</dt>
                    <dd class="mt-1 text-xl font-semibold tracking-tight text-teal-950">{{ $financialMetrics['invoices_this_month'] }}</dd>
                </div>
            </dl>

            <a href="{{ route('admin.reports.index') }}" class="mt-5 inline-flex rounded-lg border border-teal-200 bg-white px-3.5 py-2 text-sm font-semibold text-teal-800 transition hover:bg-teal-50 focus:outline-none focus:ring-2 focus:ring-teal-600 focus:ring-offset-2">Open reports</a>
        </section>
    </div>

    <section class="mt-6 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-[0_1px_2px_rgba(15,60,58,0.05)]">
        <div class="border-b border-slate-200 px-5 py-5 sm:px-6">
            <h2 class="text-lg font-semibold tracking-tight text-slate-950">Booking workflow</h2>
            <p class="mt-1 text-sm text-slate-500">Current booking volume by workflow status.</p>
        </div>
        <div class="grid gap-px bg-slate-200 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($bookingStatusSummary as $status)
                <div class="bg-white px-5 py-4 sm:px-6">
                    <p class="text-sm text-slate-500">{{ $status['label'] }}</p>
                    <p class="mt-2 text-2xl font-semibold tracking-tight text-slate-950">{{ $status['value'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    <section class="mt-8 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-[0_1px_2px_rgba(15,23,42,0.04)]">
    <div class="flex flex-col gap-4 border-b border-slate-200 px-5 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-6">
        <div>
            <h2 class="text-lg font-semibold tracking-tight text-slate-950">Recent bookings</h2>
            <p class="mt-1 text-sm text-slate-500">Latest customer service requests</p>
        </div>

        <a
            href="{{ route('admin.bookings.index') }}"
            class="w-fit rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-slate-400 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-teal-600 focus:ring-offset-2">
            All bookings
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full min-w-[760px] text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold text-slate-500">
                <tr>
                    <th class="px-5 py-3.5 sm:px-6">Customer</th>
                    <th class="px-5 py-3.5 sm:px-6">Service</th>
                    <th class="px-5 py-3.5 sm:px-6">Status</th>
                    <th class="px-5 py-3.5 sm:px-6">Quotation</th>
                    <th class="px-5 py-3.5 sm:px-6">Scheduled</th>
                    <th class="px-5 py-3.5 text-right sm:px-6"><span class="sr-only">Actions</span></th>
                </tr>
            </thead>

            <tbody class="divide-y divide-slate-100">
                @forelse ($recentBookings as $booking)
                <tr class="transition hover:bg-slate-50/80">
                    <td class="px-5 py-4 sm:px-6">
                        <div class="font-medium text-slate-900">{{ $booking->customer?->name ?? 'Unknown' }}</div>
                        <div class="text-xs text-slate-500">{{ $booking->customer?->email }}</div>
                    </td>

                    <td class="px-5 py-4 font-medium text-slate-700 sm:px-6">{{ $booking->service_name }}</td>

                    <td class="px-5 py-4 sm:px-6">
                        <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-700">
                            {{ strtoupper(str_replace('_', ' ', $booking->status->value)) }}
                        </span>
                    </td>

                    <td class="px-5 py-4 sm:px-6">
                        @if ($booking->quotation)
                        <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700">
                            {{ strtoupper($booking->quotation->status->value) }}
                        </span>
                        @else
                        <span class="rounded-full bg-amber-50 px-2.5 py-1 text-xs font-medium text-amber-700">NOT SENT</span>
                        @endif
                    </td>

                    <td class="whitespace-nowrap px-5 py-4 text-slate-500 sm:px-6">
                        {{ $booking->scheduled_at?->format('d M Y, h:i A') }}
                    </td>

                    <td class="px-5 py-4 text-right sm:px-6">
                        <a
                            href="{{ route('admin.bookings.show', $booking) }}"
                            class="text-sm font-semibold text-teal-700 transition hover:text-teal-900 focus:outline-none focus:ring-2 focus:ring-teal-600 focus:ring-offset-2">
                            Open
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                        No bookings have been created yet.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
