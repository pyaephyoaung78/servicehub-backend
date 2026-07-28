@extends('admin.layouts.app')

@section('title', 'Bookings')
@section('page_title', 'Bookings')

@section('content')
<section class="mb-8 overflow-hidden rounded-xl bg-teal-950 px-5 py-6 shadow-[0_14px_32px_rgba(15,60,58,0.14)] sm:px-7 sm:py-7">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm font-medium text-teal-300">Service operations</p>
            <h1 class="mt-2 text-2xl font-semibold tracking-tight text-white sm:text-3xl">
                Booking Management
            </h1>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-teal-50/85">
                Review customer requests and manage their valid workflow actions.
            </p>
        </div>

        <p class="text-sm text-teal-100/80">
            Requests are created from the customer mobile app.
        </p>
    </div>
</section>

@if (session('success'))
<div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
    {{ session('success') }}
</div>
@endif

<form method="GET" class="mb-6 rounded-xl border border-teal-100 bg-teal-50/40 p-5 shadow-[0_1px_2px_rgba(15,60,58,0.06)] sm:p-6">
    <div class="mb-6">
        <h2 class="text-base font-semibold text-teal-950">Find bookings</h2>
        <p class="mt-1 text-sm text-teal-900/70">Search requests or narrow the list by workflow status and scheduled date.</p>
    </div>

    <div class="grid gap-5 md:grid-cols-2">
        <label class="block">
            <span class="mb-2 block text-sm font-medium text-teal-950">Search</span>
            <input
                type="search"
                name="search"
                value="{{ request('search') }}"
                placeholder="Customer, service, phone or address"
                class="h-11 w-full rounded-lg border-teal-200 bg-white px-4 text-sm placeholder:text-teal-900/40 focus:border-teal-600 focus:ring-teal-600">
        </label>

        <label class="block">
            <span class="mb-2 block text-sm font-medium text-teal-950">Status</span>
            <select
                name="status"
                class="h-11 w-full rounded-lg border-teal-200 bg-white px-4 text-sm focus:border-teal-600 focus:ring-teal-600">
                <option value="">All statuses</option>
                @foreach ($statuses as $status)
                <option
                    value="{{ $status->value }}"
                    @selected(request('status')===$status->value)
                    >
                    {{ strtoupper(str_replace('_', ' ', $status->value)) }}
                </option>
                @endforeach
            </select>
        </label>

        <label class="block">
            <span class="mb-2 block text-sm font-medium text-teal-950">Scheduled from</span>
            <input
                type="date"
                name="date_from"
                value="{{ request('date_from') }}"
                class="h-11 w-full rounded-lg border-teal-200 bg-white px-4 text-sm focus:border-teal-600 focus:ring-teal-600">
        </label>

        <label class="block">
            <span class="mb-2 block text-sm font-medium text-teal-950">Scheduled to</span>
            <input
                type="date"
                name="date_to"
                value="{{ request('date_to') }}"
                class="h-11 w-full rounded-lg border-teal-200 bg-white px-4 text-sm focus:border-teal-600 focus:ring-teal-600">
        </label>
    </div>

    <div class="mt-6 flex flex-col gap-3 border-t border-teal-100 pt-5 sm:flex-row sm:items-center">
        <button
            type="submit"
            class="w-full rounded-lg bg-teal-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-teal-800 focus:outline-none focus:ring-2 focus:ring-teal-600 focus:ring-offset-2 active:translate-y-px sm:w-auto">
            Apply filters
        </button>

        <a
            href="{{ route('admin.bookings.index') }}"
            class="w-full rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-center text-sm font-semibold text-slate-700 shadow-sm transition hover:border-slate-400 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-teal-600 focus:ring-offset-2 sm:w-auto">
            Reset
        </a>
    </div>
</form>

<div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-[0_1px_2px_rgba(15,60,58,0.05)]">
    <div class="border-b border-slate-200 bg-white px-5 py-4 sm:px-6">
        <p class="text-sm font-medium text-slate-600">
            {{ $bookings->total() }} booking{{ $bookings->total() === 1 ? '' : 's' }} found
        </p>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-teal-950 text-left text-xs font-semibold tracking-wide text-teal-50">
                <tr>
                    <th class="px-6 py-3">Customer</th>
                    <th class="px-6 py-3">Service</th>
                    <th class="px-6 py-3">Scheduled</th>
                    <th class="px-6 py-3">Booking status</th>
                    <th class="px-6 py-3">Quotation</th>
                    <th class="px-6 py-3">Staff</th>
                    <th class="px-6 py-3">Action</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-slate-100">
                @forelse ($bookings as $booking)
                <tr class="transition hover:bg-teal-50/60">
                    <td class="px-6 py-4">
                        <p class="font-medium text-slate-900">
                            {{ $booking->customer?->name ?? 'Unknown customer' }}
                        </p>
                        <p class="text-xs text-slate-500">
                            {{ $booking->customer?->email }}
                        </p>
                    </td>

                    <td class="px-6 py-4">
                        <p class="font-medium text-slate-800">{{ $booking->service_name }}</p>
                        <p class="text-xs text-slate-500">
                            {{ number_format((float) $booking->service_price, 0) }} MMK
                        </p>
                    </td>

                    <td class="whitespace-nowrap px-6 py-4 text-slate-600">
                        {{ $booking->scheduled_at?->format('d M Y, h:i A') }}
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

                    <td class="px-6 py-4 text-slate-600">
                        {{ $booking->latestAssignment?->staffProfile?->user?->name ?? 'Unassigned' }}
                    </td>

                    <td class="px-6 py-4 text-center">
                        <a
                            href="{{ route('admin.bookings.show', $booking) }}"
                            class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-teal-700 transition hover:bg-teal-50 hover:text-teal-900 focus:outline-none focus:ring-2 focus:ring-teal-600 focus:ring-offset-2"
                            aria-label="Open booking #{{ $booking->id }}">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12S5.75 5.25 12 5.25 21.75 12 21.75 12 18.25 18.75 12 18.75 2.25 12 2.25 12Z" />
                                <circle cx="12" cy="12" r="2.75" />
                            </svg>
                            <span class="sr-only">Open booking #{{ $booking->id }}</span>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center text-slate-500">
                        No bookings match the current filters.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($bookings->hasPages())
    <div class="border-t border-slate-200 px-6 py-4">
        {{ $bookings->links() }}
    </div>
    @endif
</div>
@endsection