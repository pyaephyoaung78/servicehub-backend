@extends('admin.layouts.app')

@section('title', 'Payment #' . $payment->id)
@section('page_title', 'Payment Details')

@section('content')
    <div class="mb-8">
        <a href="{{ route('admin.payments.index') }}" class="text-sm font-medium text-blue-700 hover:text-blue-900">
            ← Back to payments
        </a>
        <h1 class="mt-2 text-2xl font-bold text-slate-900">Payment #{{ $payment->id }}</h1>
        <p class="mt-1 text-slate-500">Received {{ $payment->paid_at?->format('d M Y, h:i A') }}</p>
    </div>

    <div class="grid gap-6 xl:grid-cols-3">
        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm xl:col-span-2">
            <h2 class="text-lg font-semibold text-slate-900">Payment information</h2>
            <dl class="mt-5 space-y-4">
                <div class="flex justify-between gap-4 border-b border-slate-100 pb-3">
                    <dt class="text-slate-500">Amount</dt>
                    <dd class="text-xl font-bold text-emerald-700">{{ number_format((float) $payment->amount, 0) }} MMK</dd>
                </div>
                <div class="flex justify-between gap-4 border-b border-slate-100 pb-3">
                    <dt class="text-slate-500">Payment method</dt>
                    <dd class="font-medium text-slate-900">{{ $payment->payment_method ?? 'Not specified' }}</dd>
                </div>
                <div class="flex justify-between gap-4 border-b border-slate-100 pb-3">
                    <dt class="text-slate-500">Received by</dt>
                    <dd class="font-medium text-slate-900">{{ $payment->receivedBy?->name ?? 'System' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">Note</dt>
                    <dd class="mt-1 whitespace-pre-line text-slate-800">{{ $payment->note ?? 'No note' }}</dd>
                </div>
            </dl>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">Related invoice</h2>
            <dl class="mt-5 space-y-4 text-sm">
                <div>
                    <dt class="text-slate-500">Invoice</dt>
                    <dd class="mt-1 font-medium text-slate-900">{{ $payment->invoice?->invoice_no }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">Customer</dt>
                    <dd class="mt-1 font-medium text-slate-900">{{ $payment->invoice?->customer?->name }}</dd>
                    <dd class="text-slate-600">{{ $payment->invoice?->customer?->email }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">Invoice status</dt>
                    <dd class="mt-1 font-medium text-slate-900">{{ strtoupper($payment->invoice?->payment_status?->value ?? '') }}</dd>
                </div>
            </dl>

            @if ($payment->invoice)
                <a href="{{ route('admin.invoices.show', $payment->invoice) }}" class="mt-5 inline-block text-sm font-semibold text-blue-700 hover:text-blue-900">
                    View invoice →
                </a>
            @endif
        </section>
    </div>
@endsection
