@extends('admin.layouts.app')

@section('title', 'Bookings')
@section('page_title', 'Bookings')

@section('content')
    <div class="mb-8 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">
                Booking Management
            </h1>
            <p class="mt-1 text-slate-500">
                Review customer requests and manage their valid workflow actions.
            </p>
        </div>

        <p class="text-sm text-slate-500">
            Customers create bookings from the mobile app.
        </p>
    </div>

    @if (session('success'))
        <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    <form method="GET" class="mb-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
            <label class="block xl:col-span-2">
                <span class="mb-1 block text-sm font-medium text-slate-700">Search</span>
                <input
                    type="search"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Customer, service, phone or address"
                    class="w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500"
                >
            </label>

            <label class="block">
                <span class="mb-1 block text-sm font-medium text-slate-700">Status</span>
                <select
                    name="status"
                    class="w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500"
                >
                    <option value="">All statuses</option>
                    @foreach ($statuses as $status)
                        <option
                            value="{{ $status->value }}"
                            @selected(request('status') === $status->value)
                        >
                            {{ strtoupper(str_replace('_', ' ', $status->value)) }}
                        </option>
                    @endforeach
                </select>
            </label>

            <label class="block">
                <span class="mb-1 block text-sm font-medium text-slate-700">Scheduled from</span>
                <input
                    type="date"
                    name="date_from"
                    value="{{ request('date_from') }}"
                    class="w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500"
                >
            </label>

            <label class="block">
                <span class="mb-1 block text-sm font-medium text-slate-700">Scheduled to</span>
                <input
                    type="date"
                    name="date_to"
                    value="{{ request('date_to') }}"
                    class="w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500"
                >
            </label>
        </div>

        <div class="mt-5 flex gap-3">
            <button
                type="submit"
                class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700"
            >
                Apply filters
            </button>

            <a
                href="{{ route('admin.bookings.index') }}"
                class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
            >
                Reset
            </a>
        </div>
    </form>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 px-6 py-4">
            <p class="text-sm text-slate-500">
                {{ $bookings->total() }} booking{{ $bookings->total() === 1 ? '' : 's' }} found
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-6 py-3">Customer</th>
                        <th class="px-6 py-3">Service</th>
                        <th class="px-6 py-3">Scheduled</th>
                        <th class="px-6 py-3">Booking status</th>
                        <th class="px-6 py-3">Quotation</th>
                        <th class="px-6 py-3">Staff</th>
                        <th class="px-6 py-3"></th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                    @forelse ($bookings as $booking)
                        <tr class="hover:bg-slate-50/80">
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

                            <td class="px-6 py-4 text-right">
                                <a
                                    href="{{ route('admin.bookings.show', $booking) }}"
                                    class="font-medium text-blue-700 hover:text-blue-900"
                                >
                                    View
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
