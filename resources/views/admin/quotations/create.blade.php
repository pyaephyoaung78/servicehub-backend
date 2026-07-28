@extends('admin.layouts.app')

@section('title', 'Create Quotation')
@section('page_title', 'Create Quotation')

@section('content')
<section class="mb-8 overflow-hidden rounded-xl bg-teal-950 px-5 py-6 shadow-[0_14px_32px_rgba(15,60,58,0.14)] sm:px-7 sm:py-7">
    <a
        href="{{ route('admin.quotations.index') }}"
        class="inline-flex items-center gap-1.5 rounded-lg border border-teal-700 bg-teal-900/70 px-2.5 py-1.5 text-xs font-semibold text-teal-50 transition hover:bg-teal-800 focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-teal-950 active:translate-y-px">
        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
        </svg>
        Back to quotations
    </a>
    <p class="mt-5 text-sm font-medium text-teal-300">Service pricing</p>
    <h1 class="mt-2 text-2xl font-semibold tracking-tight text-white sm:text-3xl">Create quotation</h1>
    <p class="mt-2 max-w-2xl text-sm leading-6 text-teal-50/85">
        Set the service price adjustments and send a quotation to a customer with an eligible booking.
    </p>
</section>

@if ($errors->any())
<div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
    <p class="font-semibold">Please correct the following before sending the quotation.</p>
    <ul class="mt-1 list-inside list-disc">
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

@if ($eligibleBookings->isEmpty())
<section class="max-w-3xl rounded-xl border border-amber-200 bg-amber-50 p-5 sm:p-6">
    <h2 class="text-lg font-semibold text-amber-950">No eligible bookings</h2>
    <p class="mt-2 text-sm leading-6 text-amber-900">There are no pending bookings waiting for a quotation.</p>
    <a
        href="{{ route('admin.bookings.index') }}"
        class="mt-4 inline-flex rounded-lg border border-amber-300 bg-white px-3.5 py-2 text-sm font-semibold text-amber-900 transition hover:bg-amber-100 focus:outline-none focus:ring-2 focus:ring-amber-600 focus:ring-offset-2">
        View bookings
    </a>
</section>
@else
<form method="POST" action="{{ route('admin.quotations.store') }}" class="max-w-4xl overflow-hidden rounded-xl border border-slate-200 bg-white shadow-[0_1px_2px_rgba(15,60,58,0.05)]">
    @csrf

    <div class="border-b border-slate-200 px-5 py-5 sm:px-6">
        <h2 class="text-lg font-semibold tracking-tight text-slate-950">Quotation details</h2>
        <p class="mt-1 text-sm text-slate-500">Choose a pending booking and define any pricing changes before sending.</p>
    </div>

    <div class="space-y-6 p-5 sm:p-6">
        <label class="block">
            <span class="mb-2 block text-sm font-medium text-slate-800">Booking</span>
            <select
                name="booking_id"
                required
                class="h-11 w-full rounded-lg border-teal-200 bg-white px-4 text-sm focus:border-teal-600 focus:ring-teal-600">
                <option value="">Select a booking</option>
                @foreach ($eligibleBookings as $booking)
                <option
                    value="{{ $booking->id }}"
                    @selected(old('booking_id', $selectedBooking?->id) == $booking->id)
                    >
                    #{{ $booking->id }} - {{ $booking->customer?->name }} - {{ $booking->service_name }} - {{ $booking->scheduled_at?->format('d M Y, h:i A') }}
                </option>
                @endforeach
            </select>
            <span class="mt-2 block text-xs text-slate-500">Only pending bookings without an existing quotation are shown.</span>
        </label>

        <section class="rounded-xl border border-teal-100 bg-teal-50/40 p-5">
            <h3 class="font-semibold text-teal-950">Pricing adjustments</h3>
            <p class="mt-1 text-sm text-teal-900/70">Use these only when the booking service price needs an addition or reduction.</p>

            <div class="mt-5 grid gap-5 sm:grid-cols-2">
                <label class="block">
                    <span class="mb-2 block text-sm font-medium text-teal-950">Extra fee</span>
                    <input
                        type="number"
                        name="extra_fee"
                        value="{{ old('extra_fee', '0') }}"
                        min="0"
                        step="0.01"
                        class="h-11 w-full rounded-lg border-teal-200 bg-white px-4 text-sm focus:border-teal-600 focus:ring-teal-600">
                </label>

                <label class="block">
                    <span class="mb-2 block text-sm font-medium text-teal-950">Discount</span>
                    <input
                        type="number"
                        name="discount_amount"
                        value="{{ old('discount_amount', '0') }}"
                        min="0"
                        step="0.01"
                        class="h-11 w-full rounded-lg border-teal-200 bg-white px-4 text-sm focus:border-teal-600 focus:ring-teal-600">
                </label>
            </div>
        </section>

        <label class="block">
            <span class="mb-2 block text-sm font-medium text-slate-800">Valid until</span>
            <input
                type="datetime-local"
                name="valid_until"
                value="{{ old('valid_until') }}"
                class="h-11 w-full rounded-lg border-teal-200 bg-white px-4 text-sm focus:border-teal-600 focus:ring-teal-600">
            <span class="mt-2 block text-xs text-slate-500">Leave empty if the quotation should not expire.</span>
        </label>

        <label class="block">
            <span class="mb-2 block text-sm font-medium text-slate-800">Note for customer</span>
            <textarea
                name="admin_note"
                rows="4"
                class="w-full rounded-lg border-teal-200 bg-white px-4 py-3 text-sm placeholder:text-teal-900/40 focus:border-teal-600 focus:ring-teal-600"
                placeholder="Explain additional work or pricing details">{{ old('admin_note') }}</textarea>
        </label>
    </div>

    <div class="flex flex-col gap-3 border-t border-slate-200 bg-slate-50 px-5 py-5 sm:flex-row sm:items-center sm:px-6">
        <button
            type="submit"
            class="w-full rounded-lg bg-teal-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-teal-800 focus:outline-none focus:ring-2 focus:ring-teal-600 focus:ring-offset-2 active:translate-y-px sm:w-auto">
            Create and send quotation
        </button>
        <a
            href="{{ route('admin.quotations.index') }}"
            class="w-full rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-center text-sm font-semibold text-slate-700 shadow-sm transition hover:border-slate-400 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-teal-600 focus:ring-offset-2 sm:w-auto">
            Cancel
        </a>
    </div>
</form>
@endif
@endsection