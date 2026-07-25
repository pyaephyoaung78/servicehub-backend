@extends('admin.layouts.app')

@section('title', 'Payment #' . $payment->id)
@section('page_title', 'Payment Details')

@section('content')
<section class="mb-8 overflow-hidden rounded-xl bg-teal-950 px-5 py-6 shadow-[0_14px_32px_rgba(15,60,58,0.14)] sm:px-7 sm:py-7">
    <a href="{{ route('admin.payments.index') }}" class="inline-flex items-center gap-1.5 rounded-lg border border-teal-700 bg-teal-900/70 px-2.5 py-1.5 text-xs font-semibold text-teal-50 transition hover:bg-teal-800 focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-teal-950 active:translate-y-px"><svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
        </svg>Back to payments</a>
    <p class="mt-5 text-sm font-medium text-teal-300">Payment record</p>
    <h1 class="mt-2 text-2xl font-semibold tracking-tight text-white sm:text-3xl">Payment #{{ $payment->id }}</h1>
    <p class="mt-2 text-sm text-teal-50/85">Received {{ $payment->paid_at?->format('d M Y, h:i A') }}</p>
</section>
<div class="grid gap-6 xl:grid-cols-3">
    <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-[0_1px_2px_rgba(15,60,58,0.05)] xl:col-span-2">
        <div class="border-b border-slate-200 px-5 py-5 sm:px-6">
            <h2 class="text-lg font-semibold tracking-tight text-slate-950">Payment information</h2>
            <p class="mt-1 text-sm text-slate-500">Received amount and payment record details.</p>
        </div>
        <dl class="divide-y divide-slate-100 px-5 sm:px-6">
            <div class="flex justify-between gap-6 bg-teal-50/60 py-4">
                <dt class="font-semibold text-teal-950">Amount</dt>
                <dd class="text-xl font-bold text-emerald-700">{{ number_format((float) $payment->amount, 0) }} MMK</dd>
            </div>
            <div class="flex justify-between gap-6 py-4">
                <dt class="text-sm text-slate-500">Payment method</dt>
                <dd class="text-right text-sm font-semibold text-slate-900">{{ $payment->payment_method ?? 'Not specified' }}</dd>
            </div>
            <div class="flex justify-between gap-6 py-4">
                <dt class="text-sm text-slate-500">Received by</dt>
                <dd class="text-right text-sm font-semibold text-slate-900">{{ $payment->receivedBy?->name ?? 'System' }}</dd>
            </div>
            <div class="py-4">
                <dt class="text-sm text-slate-500">Note</dt>
                <dd class="mt-2 whitespace-pre-line text-sm text-slate-800">{{ $payment->note ?? 'No note' }}</dd>
            </div>
        </dl>
    </section>
    <section class="rounded-xl border border-teal-100 bg-teal-50/40 p-5 shadow-[0_1px_2px_rgba(15,60,58,0.05)] sm:p-6">
        <h2 class="text-lg font-semibold tracking-tight text-teal-950">Related invoice</h2>
        <p class="mt-1 text-sm text-teal-900/70">The invoice this payment was recorded against.</p>
        <dl class="mt-5 divide-y divide-teal-100 text-sm">
            <div class="py-4 first:pt-0">
                <dt class="font-medium text-teal-900/70">Invoice</dt>
                <dd class="mt-2 font-semibold text-slate-900">{{ $payment->invoice?->invoice_no }}</dd>
            </div>
            <div class="py-4">
                <dt class="font-medium text-teal-900/70">Customer</dt>
                <dd class="mt-2 font-semibold text-slate-900">{{ $payment->invoice?->customer?->name }}</dd>
                <dd class="mt-1 text-slate-600">{{ $payment->invoice?->customer?->email }}</dd>
            </div>
            <div class="pb-0 pt-4">
                <dt class="font-medium text-teal-900/70">Invoice status</dt>
                <dd class="mt-2 font-semibold text-slate-900">{{ strtoupper($payment->invoice?->payment_status?->value ?? '') }}</dd>
            </div>
        </dl>@if ($payment->invoice)<a href="{{ route('admin.invoices.show', $payment->invoice) }}" class="mt-5 inline-flex rounded-lg border border-teal-200 bg-white px-3.5 py-2 text-sm font-semibold text-teal-800 transition hover:bg-teal-50 focus:outline-none focus:ring-2 focus:ring-teal-600 focus:ring-offset-2">View invoice</a>@endif
    </section>
</div>
@endsection