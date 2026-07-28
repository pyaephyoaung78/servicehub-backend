@extends('admin.layouts.app')

@section('title', 'Create Invoice')
@section('page_title', 'Create Invoice')

@section('content')
<section class="mb-8 overflow-hidden rounded-xl bg-teal-950 px-5 py-6 shadow-[0_14px_32px_rgba(15,60,58,0.14)] sm:px-7 sm:py-7">
    <a href="{{ route('admin.invoices.index') }}" class="inline-flex items-center gap-1.5 rounded-lg border border-teal-700 bg-teal-900/70 px-2.5 py-1.5 text-xs font-semibold text-teal-50 transition hover:bg-teal-800 focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-teal-950 active:translate-y-px">
        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
        </svg>
        Back to invoices
    </a>
    <p class="mt-5 text-sm font-medium text-teal-300">Finance operations</p>
    <h1 class="mt-2 text-2xl font-semibold tracking-tight text-white sm:text-3xl">Create invoice</h1>
    <p class="mt-2 max-w-2xl text-sm leading-6 text-teal-50/85">Choose completed work, confirm the pricing source, and optionally record an initial payment.</p>
</section>

@if ($errors->any())
<div class="mb-6 max-w-4xl rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
    <p class="font-semibold">Please correct the following invoice details.</p>
    <ul class="mt-1 list-inside list-disc">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
</div>
@endif

@if ($eligibleBookings->isEmpty())
<section class="max-w-4xl rounded-xl border border-amber-200 bg-amber-50 p-5 sm:p-6">
    <h2 class="text-lg font-semibold text-amber-950">No eligible bookings</h2>
    <p class="mt-2 text-sm leading-6 text-amber-900">There are no completed bookings waiting for an invoice.</p>
    <a href="{{ route('admin.bookings.index') }}" class="mt-4 inline-flex rounded-lg border border-amber-300 bg-white px-3.5 py-2 text-sm font-semibold text-amber-900 transition hover:bg-amber-100 focus:outline-none focus:ring-2 focus:ring-amber-600 focus:ring-offset-2">View bookings</a>
</section>
@else
<form method="POST" action="{{ route('admin.invoices.store') }}" class="max-w-4xl overflow-hidden rounded-xl border border-slate-200 bg-white shadow-[0_1px_2px_rgba(15,60,58,0.05)]">
    @csrf
    <div class="border-b border-slate-200 px-5 py-5 sm:px-6">
        <h2 class="text-lg font-semibold tracking-tight text-slate-950">Invoice details</h2>
        <p class="mt-1 text-sm text-slate-500">Only completed bookings without an invoice are available for billing.</p>
    </div>
    <div class="space-y-6 p-5 sm:p-6">
        <label class="block">
            <span class="mb-2 block text-sm font-medium text-slate-800">Completed booking</span>
            <select name="booking_id" required class="h-11 w-full rounded-lg border-teal-200 bg-white px-4 text-sm focus:border-teal-600 focus:ring-teal-600">
                <option value="">Select a booking</option>
                @foreach ($eligibleBookings as $booking)
                <option value="{{ $booking->id }}" @selected(old('booking_id', $selectedBooking?->id) == $booking->id)>
                    #{{ $booking->id }} - {{ $booking->customer?->name }} - {{ $booking->service_name }} - @if ($booking->quotation?->status === \App\Enums\QuotationStatus::Accepted)accepted quote {{ number_format((float) $booking->quotation->total_amount, 0) }} MMK - @endif completed {{ $booking->completed_at?->format('d M Y, h:i A') }}
                </option>
                @endforeach
            </select>
        </label>

        @if ($selectedBooking?->quotation?->status === \App\Enums\QuotationStatus::Accepted)
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-900">
            <p class="font-semibold">Accepted quotation pricing will be used</p>
            <p class="mt-1">{{ $selectedBooking->quotation->quotation_no }}: {{ number_format((float) $selectedBooking->quotation->total_amount, 0) }} MMK (service price, extra fee, and discount).</p>
        </div>
        @else
        <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">No accepted quotation is attached to this booking. The invoice will use the booking price and the adjustments below.</div>
        @endif

        <section class="rounded-xl border border-teal-100 bg-teal-50/40 p-5">
            <h3 class="font-semibold text-teal-950">Payment and pricing</h3>
            <p class="mt-1 text-sm text-teal-900/70">Adjustments apply only when no accepted quotation is attached to the booking.</p>
            <div class="mt-5 grid gap-5 sm:grid-cols-2">
                <label class="block"><span class="mb-2 block text-sm font-medium text-teal-950">Extra fee</span><input type="number" name="extra_fee" value="{{ old('extra_fee', '0') }}" min="0" step="0.01" class="h-11 w-full rounded-lg border-teal-200 bg-white px-4 text-sm focus:border-teal-600 focus:ring-teal-600"></label>
                <label class="block"><span class="mb-2 block text-sm font-medium text-teal-950">Discount</span><input type="number" name="discount_amount" value="{{ old('discount_amount', '0') }}" min="0" step="0.01" class="h-11 w-full rounded-lg border-teal-200 bg-white px-4 text-sm focus:border-teal-600 focus:ring-teal-600"></label>
                <label class="block"><span class="mb-2 block text-sm font-medium text-teal-950">Initial paid amount</span><input type="number" name="paid_amount" value="{{ old('paid_amount', '0') }}" min="0" step="0.01" class="h-11 w-full rounded-lg border-teal-200 bg-white px-4 text-sm focus:border-teal-600 focus:ring-teal-600"></label>
                <label class="block"><span class="mb-2 block text-sm font-medium text-teal-950">Payment method</span><input type="text" name="payment_method" value="{{ old('payment_method') }}" placeholder="Cash, KPay, WavePay" class="h-11 w-full rounded-lg border-teal-200 bg-white px-4 text-sm placeholder:text-teal-900/40 focus:border-teal-600 focus:ring-teal-600"></label>
            </div>
        </section>

        <label class="block">
            <span class="mb-2 block text-sm font-medium text-slate-800">Invoice note</span>
            <textarea name="note" rows="4" class="w-full rounded-lg border-teal-200 bg-white px-4 py-3 text-sm focus:border-teal-600 focus:ring-teal-600">{{ old('note') }}</textarea>
        </label>
    </div>
    <div class="flex flex-col gap-3 border-t border-slate-200 bg-slate-50 px-5 py-5 sm:flex-row sm:items-center sm:px-6">
        <button type="submit" class="w-full rounded-lg bg-teal-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-teal-800 focus:outline-none focus:ring-2 focus:ring-teal-600 focus:ring-offset-2 active:translate-y-px sm:w-auto">Create invoice</button>
        <a href="{{ route('admin.invoices.index') }}" class="w-full rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-center text-sm font-semibold text-slate-700 shadow-sm transition hover:border-slate-400 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-teal-600 focus:ring-offset-2 sm:w-auto">Cancel</a>
    </div>
</form>
@endif
@endsection