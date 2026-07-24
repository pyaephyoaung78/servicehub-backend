@extends('admin.layouts.app')

@section('title', 'Create Quotation')
@section('page_title', 'Create Quotation')

@section('content')
    <div class="mb-8">
        <a
            href="{{ route('admin.quotations.index') }}"
            class="text-sm font-medium text-blue-700 hover:text-blue-900"
        >
            ← Back to quotations
        </a>
        <h1 class="mt-2 text-2xl font-bold text-slate-900">Create Quotation</h1>
        <p class="mt-1 text-slate-500">
            Only pending bookings without an existing quotation can receive a quotation.
        </p>
    </div>

    @if ($errors->any())
        <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            <ul class="list-inside list-disc">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if ($eligibleBookings->isEmpty())
        <div class="rounded-2xl border border-amber-200 bg-amber-50 p-6 text-amber-900">
            There are no pending bookings waiting for a quotation.
        </div>
    @else
        <form method="POST" action="{{ route('admin.quotations.store') }}" class="max-w-3xl rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            @csrf

            <label class="block">
                <span class="mb-1 block text-sm font-medium text-slate-700">Booking</span>
                <select
                    name="booking_id"
                    required
                    class="w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500"
                >
                    <option value="">Select a booking</option>
                    @foreach ($eligibleBookings as $booking)
                        <option
                            value="{{ $booking->id }}"
                            @selected(old('booking_id', $selectedBooking?->id) == $booking->id)
                        >
                            #{{ $booking->id }} — {{ $booking->customer?->name }} —
                            {{ $booking->service_name }} —
                            {{ $booking->scheduled_at?->format('d M Y, h:i A') }}
                        </option>
                    @endforeach
                </select>
            </label>

            <div class="mt-5 grid gap-5 sm:grid-cols-2">
                <label class="block">
                    <span class="mb-1 block text-sm font-medium text-slate-700">Extra fee</span>
                    <input
                        type="number"
                        name="extra_fee"
                        value="{{ old('extra_fee', '0') }}"
                        min="0"
                        step="0.01"
                        class="w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500"
                    >
                </label>

                <label class="block">
                    <span class="mb-1 block text-sm font-medium text-slate-700">Discount</span>
                    <input
                        type="number"
                        name="discount_amount"
                        value="{{ old('discount_amount', '0') }}"
                        min="0"
                        step="0.01"
                        class="w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500"
                    >
                </label>
            </div>

            <label class="mt-5 block">
                <span class="mb-1 block text-sm font-medium text-slate-700">Valid until</span>
                <input
                    type="datetime-local"
                    name="valid_until"
                    value="{{ old('valid_until') }}"
                    class="w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500"
                >
                <span class="mt-1 block text-xs text-slate-500">
                    Leave empty if the quotation should not expire.
                </span>
            </label>

            <label class="mt-5 block">
                <span class="mb-1 block text-sm font-medium text-slate-700">Note for customer</span>
                <textarea
                    name="admin_note"
                    rows="4"
                    class="w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500"
                    placeholder="Explain additional work or pricing details"
                >{{ old('admin_note') }}</textarea>
            </label>

            <div class="mt-6 flex gap-3">
                <button
                    type="submit"
                    class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700"
                >
                    Create and send quotation
                </button>
                <a
                    href="{{ route('admin.quotations.index') }}"
                    class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
                >
                    Cancel
                </a>
            </div>
        </form>
    @endif
@endsection
