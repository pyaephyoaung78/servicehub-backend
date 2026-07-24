@extends('admin.layouts.app')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard Overview')

@section('content')
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-slate-900">
            Dashboard Overview
        </h1>
        <p class="mt-1 text-slate-500">
            Monitor bookings, quotations, invoices and staff activity.
        </p>
    </div>

    <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
        <x-admin.stat-card
            title="Pending Bookings"
            :value="$stats['pending_bookings']"
            description="Bookings waiting for review"
        />

        <x-admin.stat-card
            title="Sent Quotations"
            :value="$stats['sent_quotations']"
            description="Waiting for customer response"
        />

        <x-admin.stat-card
            title="Accepted Quotations"
            :value="$stats['accepted_quotations']"
            description="Ready for staff assignment"
        />

        <x-admin.stat-card
            title="Unpaid Invoices"
            :value="$stats['unpaid_invoices']"
            description="No payment received yet"
        />

        <x-admin.stat-card
            title="Partial Invoices"
            :value="$stats['partial_invoices']"
            description="Some payment received"
        />

        <x-admin.stat-card
            title="Active Staff"
            :value="$stats['active_staff']"
            description="Available workforce base"
        />
    </div>

    <div class="mt-8 rounded-2xl bg-white border border-slate-200 shadow-sm">
        <div class="border-b border-slate-200 px-6 py-4">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h2 class="font-semibold text-slate-900">
                        Recent Bookings
                    </h2>
                    <p class="text-sm text-slate-500">
                        Latest customer service requests
                    </p>
                </div>

                <a
                    href="{{ route('admin.bookings.index') }}"
                    class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700"
                >
                    Manage bookings
                </a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-6 py-3">Customer</th>
                        <th class="px-6 py-3">Service</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3">Quotation</th>
                        <th class="px-6 py-3">Scheduled</th>
                        <th class="px-6 py-3"></th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                    @forelse ($recentBookings as $booking)
                        <tr>
                            <td class="px-6 py-4">
                                <div class="font-medium text-slate-900">
                                    {{ $booking->customer?->name ?? 'Unknown' }}
                                </div>
                                <div class="text-xs text-slate-500">
                                    {{ $booking->customer?->email }}
                                </div>
                            </td>

                            <td class="px-6 py-4">
                                {{ $booking->service_name }}
                            </td>

                            <td class="px-6 py-4">
                                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-700">
                                    {{ strtoupper(str_replace('_', ' ', $booking->status->value)) }}
                                </span>
                            </td>

                            <td class="px-6 py-4">
                                @if ($booking->quotation)
                                    <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-700">
                                        {{ strtoupper($booking->quotation->status->value) }}
                                    </span>
                                @else
                                    <span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-medium text-amber-700">
                                        NOT SENT
                                    </span>
                                @endif
                            </td>

                            <td class="px-6 py-4 text-slate-500">
                                {{ $booking->scheduled_at?->format('d M Y, h:i A') }}
                            </td>

                            <td class="px-6 py-4 text-right">
                                <a
                                    href="{{ route('admin.bookings.show', $booking) }}"
                                    class="text-sm font-medium text-blue-700 hover:text-blue-900"
                                >
                                    View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-slate-500">
                                No bookings yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
