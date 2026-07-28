@extends('admin.layouts.app')

@section('title', $invoice->invoice_no)
@section('page_title', 'Invoice Details')

@section('content')
<section class="mb-8 overflow-hidden rounded-xl bg-teal-950 px-5 py-6 shadow-[0_14px_32px_rgba(15,60,58,0.14)] sm:px-7 sm:py-7">
    <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <a href="{{ route('admin.invoices.index') }}" class="inline-flex items-center gap-1.5 rounded-lg border border-teal-700 bg-teal-900/70 px-2.5 py-1.5 text-xs font-semibold text-teal-50 transition hover:bg-teal-800 focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-teal-950 active:translate-y-px"><svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                </svg>Back to invoices</a>
            <p class="mt-5 text-sm font-medium text-teal-300">Billing record</p>
            <h1 class="mt-2 text-2xl font-semibold tracking-tight text-white sm:text-3xl">{{ $invoice->invoice_no }}</h1>
            <p class="mt-2 text-sm text-teal-50/85">Booking #{{ $invoice->booking_id }}. Issued {{ $invoice->issued_at?->format('d M Y, h:i A') }}</p>
        </div>
        <span class="w-fit rounded-full border border-teal-700 bg-teal-900 px-3 py-1.5 text-xs font-semibold text-teal-50">{{ strtoupper($invoice->payment_status->value) }}</span>
    </div>
</section>

@if (session('success'))<div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>@endif
@if ($errors->any())
<div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
    <p class="font-semibold">The invoice action could not be completed.</p>
    <ul class="mt-1 list-inside list-disc">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
</div>
@endif

<div class="grid gap-6 xl:grid-cols-3">
    <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-[0_1px_2px_rgba(15,60,58,0.05)] xl:col-span-2">
        <div class="border-b border-slate-200 px-5 py-5 sm:px-6">
            <h2 class="text-lg font-semibold tracking-tight text-slate-950">Invoice summary</h2>
            <p class="mt-1 text-sm text-slate-500">Pricing, customer, and current balance.</p>
        </div>
        <dl class="divide-y divide-slate-100 px-5 sm:px-6">
            <div class="flex flex-col gap-2 py-4 sm:flex-row sm:items-start sm:justify-between sm:gap-6">
                <dt class="text-sm text-slate-500">Customer</dt>
                <dd class="text-sm font-semibold text-slate-900 sm:text-right">{{ $invoice->customer?->name }}<span class="mt-1 block font-normal text-slate-500">{{ $invoice->customer?->email }}</span></dd>
            </div>
            <div class="flex justify-between gap-6 py-4">
                <dt class="text-sm text-slate-500">Service</dt>
                <dd class="text-right text-sm font-semibold text-slate-900">{{ $invoice->service_name }}</dd>
            </div>
            <div class="flex justify-between gap-6 py-4">
                <dt class="text-sm text-slate-500">Service price</dt>
                <dd class="text-sm font-semibold text-slate-900">{{ number_format((float) $invoice->service_price, 0) }} MMK</dd>
            </div>
            <div class="flex justify-between gap-6 py-4">
                <dt class="text-sm text-slate-500">Extra fee</dt>
                <dd class="text-sm font-semibold text-slate-900">{{ number_format((float) $invoice->extra_fee, 0) }} MMK</dd>
            </div>
            <div class="flex justify-between gap-6 py-4">
                <dt class="text-sm text-slate-500">Discount</dt>
                <dd class="text-sm font-semibold text-slate-900">{{ number_format((float) $invoice->discount_amount, 0) }} MMK</dd>
            </div>
            <div class="flex justify-between gap-6 bg-teal-50/60 py-4">
                <dt class="font-semibold text-teal-950">Total</dt>
                <dd class="font-bold text-teal-950">{{ number_format((float) $invoice->total_amount, 0) }} MMK</dd>
            </div>
            <div class="flex justify-between gap-6 py-4">
                <dt class="text-sm text-slate-500">Paid / remaining</dt>
                <dd class="text-right text-sm font-semibold text-slate-900">{{ number_format((float) $invoice->paid_amount, 0) }} / {{ number_format((float) $invoice->remaining_amount, 0) }} MMK</dd>
            </div>
        </dl>
        @if ($invoice->booking?->quotation?->status === \App\Enums\QuotationStatus::Accepted)
        <div class="mx-5 mb-5 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-900 sm:mx-6 sm:mb-6">
            <p class="font-semibold">Pricing source: accepted quotation</p>
            <p class="mt-1">This invoice uses <a href="{{ route('admin.quotations.show', $invoice->booking->quotation) }}" class="font-semibold underline hover:text-emerald-950">{{ $invoice->booking->quotation->quotation_no }}</a> pricing.</p>
        </div>
        @endif
        @if ($invoice->note)<div class="mx-5 mb-5 rounded-xl bg-slate-50 p-4 text-sm text-slate-700 sm:mx-6 sm:mb-6">
            <p class="font-semibold text-slate-900">Note</p>
            <p class="mt-1 whitespace-pre-line">{{ $invoice->note }}</p>
        </div>@endif
    </section>

    <section class="rounded-xl border border-teal-100 bg-teal-50/40 p-5 shadow-[0_1px_2px_rgba(15,60,58,0.05)] sm:p-6">
        <h2 class="text-lg font-semibold tracking-tight text-teal-950">Payment history</h2>
        <p class="mt-1 text-sm text-teal-900/70">Recorded payments for this invoice.</p>
        <div class="mt-5 space-y-3">
            @forelse ($invoice->payments->sortByDesc('paid_at') as $payment)
            <a href="{{ route('admin.payments.show', $payment) }}" class="block rounded-lg border border-teal-100 bg-white p-4 transition hover:border-teal-200 hover:bg-teal-50">
                <div class="flex justify-between gap-3"><span class="font-semibold text-slate-900">{{ number_format((float) $payment->amount, 0) }} MMK</span><span class="text-xs text-slate-500">{{ $payment->paid_at?->format('d M Y') }}</span></div>
                <p class="mt-1 text-sm text-slate-600">{{ $payment->payment_method ?? 'Method not specified' }}</p>@if ($payment->note)<p class="mt-1 text-xs text-slate-500">{{ $payment->note }}</p>@endif
            </a>
            @empty
            <p class="text-sm text-slate-500">No payments recorded yet.</p>
            @endforelse
        </div>
    </section>
</div>

@if ($invoice->paymentProofs->isNotEmpty())
<section class="mt-6 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-[0_1px_2px_rgba(15,60,58,0.05)]">
    <div class="flex flex-col gap-3 border-b border-slate-200 px-5 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-6">
        <div>
            <h2 class="font-semibold text-slate-900">Payment proofs</h2>
            <p class="mt-1 text-sm text-slate-500">Customer-submitted payment evidence for this invoice.</p>
        </div><a href="{{ route('admin.payment-proofs.index', ['search' => $invoice->invoice_no]) }}" class="text-sm font-semibold text-teal-700 transition hover:text-teal-900">Review queue</a>
    </div>
    <div class="grid gap-3 p-5 sm:grid-cols-2 sm:p-6">
        @foreach ($invoice->paymentProofs->sortByDesc('created_at') as $paymentProof)
        <a href="{{ route('admin.payment-proofs.show', $paymentProof) }}" class="rounded-lg border border-slate-200 bg-white p-4 transition hover:border-teal-200 hover:bg-teal-50/40">
            <div class="flex items-center justify-between gap-3"><span class="font-semibold text-slate-900">{{ number_format((float) $paymentProof->amount, 0) }} MMK</span><span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-700">{{ strtoupper($paymentProof->status->value) }}</span></div>
            <p class="mt-2 text-sm text-slate-600">{{ $paymentProof->payment_method }}</p>
            <p class="mt-1 text-xs text-slate-500">{{ $paymentProof->created_at?->format('d M Y, h:i A') }}</p>
        </a>
        @endforeach
    </div>
</section>
@endif

@if ($invoice->payment_status !== \App\Enums\PaymentStatus::Paid)
<section class="mt-6 rounded-xl border border-teal-100 bg-teal-50/40 p-5 sm:p-6">
    <h2 class="text-lg font-semibold text-teal-950">Record payment</h2>
    <p class="mt-1 text-sm text-teal-900/70">Remaining balance: {{ number_format((float) $invoice->remaining_amount, 0) }} MMK</p>
    <form method="POST" action="{{ route('admin.invoices.payments.store', $invoice) }}" class="mt-5 grid gap-5 md:grid-cols-4">@csrf
        <label class="block"><span class="mb-2 block text-sm font-medium text-teal-950">Amount</span><input type="number" name="amount" value="{{ old('amount') }}" min="1" max="{{ $invoice->remaining_amount }}" step="0.01" required class="h-11 w-full rounded-lg border-teal-200 bg-white px-4 text-sm focus:border-teal-600 focus:ring-teal-600"></label>
        <label class="block"><span class="mb-2 block text-sm font-medium text-teal-950">Payment method</span><input type="text" name="payment_method" value="{{ old('payment_method') }}" placeholder="Cash, KPay" class="h-11 w-full rounded-lg border-teal-200 bg-white px-4 text-sm placeholder:text-teal-900/40 focus:border-teal-600 focus:ring-teal-600"></label>
        <label class="block md:col-span-2"><span class="mb-2 block text-sm font-medium text-teal-950">Note</span><input type="text" name="note" value="{{ old('note') }}" class="h-11 w-full rounded-lg border-teal-200 bg-white px-4 text-sm focus:border-teal-600 focus:ring-teal-600"></label>
        <div class="md:col-span-4"><button type="submit" class="rounded-lg bg-teal-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-teal-800 focus:outline-none focus:ring-2 focus:ring-teal-600 focus:ring-offset-2 active:translate-y-px">Record payment</button></div>
    </form>
</section>
@endif

<a href="{{ route('admin.bookings.show', $invoice->booking) }}" class="mt-6 inline-flex rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-slate-400 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-teal-600 focus:ring-offset-2">View related booking</a>
@endsection