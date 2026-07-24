@extends('admin.layouts.app')

@section('title', 'Create Invoice')
@section('page_title', 'Create Invoice')

@section('content')
    <div class="mb-8">
        <a href="{{ route('admin.invoices.index') }}" class="text-sm font-medium text-blue-700 hover:text-blue-900">
            ← Back to invoices
        </a>
        <h1 class="mt-2 text-2xl font-bold text-slate-900">Create Invoice</h1>
        <p class="mt-1 text-slate-500">
            Only completed bookings without an invoice can be invoiced.
        </p>
    </div>

    @if ($errors->any())
        <div class="mb-6 max-w-3xl rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            <ul class="list-inside list-disc">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if ($eligibleBookings->isEmpty())
        <div class="max-w-3xl rounded-2xl border border-amber-200 bg-amber-50 p-6 text-amber-900">
            There are no completed bookings waiting for an invoice.
        </div>
    @else
        <form method="POST" action="{{ route('admin.invoices.store') }}" class="max-w-3xl rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            @csrf

            <label class="block">
                <span class="mb-1 block text-sm font-medium text-slate-700">Completed booking</span>
                <select name="booking_id" required class="w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Select a booking</option>
                    @foreach ($eligibleBookings as $booking)
                        <option value="{{ $booking->id }}" @selected(old('booking_id', $selectedBooking?->id) == $booking->id)>
                            #{{ $booking->id }} — {{ $booking->customer?->name }} —
                            {{ $booking->service_name }} —
                            @if ($booking->quotation?->status === \App\Enums\QuotationStatus::Accepted)
                                accepted quote {{ number_format((float) $booking->quotation->total_amount, 0) }} MMK —
                            @endif
                            completed {{ $booking->completed_at?->format('d M Y, h:i A') }}
                        </option>
                    @endforeach
                </select>
            </label>

            @if ($selectedBooking?->quotation?->status === \App\Enums\QuotationStatus::Accepted)
                <div class="mt-5 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-900">
                    <p class="font-semibold">Accepted quotation pricing will be used</p>
                    <p class="mt-1">
                        {{ $selectedBooking->quotation->quotation_no }}:
                        {{ number_format((float) $selectedBooking->quotation->total_amount, 0) }} MMK
                        (service price, extra fee, and discount).
                    </p>
                </div>
            @else
                <div class="mt-5 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
                    No accepted quotation is attached to this booking. The invoice will use the booking price and the adjustments below.
                </div>
            @endif

            <div class="mt-5 grid gap-5 sm:grid-cols-2">
                <label class="block">
                    <span class="mb-1 block text-sm font-medium text-slate-700">Extra fee</span>
                    <input type="number" name="extra_fee" value="{{ old('extra_fee', '0') }}" min="0" step="0.01" class="w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                </label>
                <label class="block">
                    <span class="mb-1 block text-sm font-medium text-slate-700">Discount</span>
                    <input type="number" name="discount_amount" value="{{ old('discount_amount', '0') }}" min="0" step="0.01" class="w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                </label>
                <label class="block">
                    <span class="mb-1 block text-sm font-medium text-slate-700">Initial paid amount</span>
                    <input type="number" name="paid_amount" value="{{ old('paid_amount', '0') }}" min="0" step="0.01" class="w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                </label>
                <label class="block">
                    <span class="mb-1 block text-sm font-medium text-slate-700">Payment method</span>
                    <input type="text" name="payment_method" value="{{ old('payment_method') }}" placeholder="Cash, KPay, WavePay" class="w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                </label>
            </div>

            <p class="mt-3 text-xs text-slate-500">
                When an accepted quotation exists, the submitted extra fee and discount values are ignored and the accepted quotation remains the source of truth.
            </p>

            <label class="mt-5 block">
                <span class="mb-1 block text-sm font-medium text-slate-700">Invoice note</span>
                <textarea name="note" rows="4" class="w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">{{ old('note') }}</textarea>
            </label>

            <div class="mt-6 flex gap-3">
                <button type="submit" class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">
                    Create invoice
                </button>
                <a href="{{ route('admin.invoices.index') }}" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                    Cancel
                </a>
            </div>
        </form>
    @endif
@endsection
