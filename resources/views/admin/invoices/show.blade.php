@extends('admin.layouts.app')

@section('title', $invoice->invoice_no)
@section('page_title', 'Invoice Details')

@section('content')
    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <a href="{{ route('admin.invoices.index') }}" class="text-sm font-medium text-blue-700 hover:text-blue-900">
                ← Back to invoices
            </a>
            <h1 class="mt-2 text-2xl font-bold text-slate-900">{{ $invoice->invoice_no }}</h1>
            <p class="mt-1 text-slate-500">
                Booking #{{ $invoice->booking_id }} · Issued {{ $invoice->issued_at?->format('d M Y, h:i A') }}
            </p>
        </div>

        <span class="w-fit rounded-full px-4 py-2 text-sm font-semibold {{ $invoice->payment_status === \App\Enums\PaymentStatus::Paid ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
            {{ strtoupper($invoice->payment_status->value) }}
        </span>
    </div>

    @if (session('success'))
        <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            <ul class="list-inside list-disc">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid gap-6 xl:grid-cols-3">
        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm xl:col-span-2">
            <h2 class="text-lg font-semibold text-slate-900">Invoice summary</h2>
            <dl class="mt-5 space-y-4">
                <div class="flex justify-between gap-4 border-b border-slate-100 pb-3">
                    <dt class="text-slate-500">Customer</dt>
                    <dd class="text-right font-medium text-slate-900">{{ $invoice->customer?->name }}<br><span class="text-sm font-normal text-slate-500">{{ $invoice->customer?->email }}</span></dd>
                </div>
                <div class="flex justify-between gap-4 border-b border-slate-100 pb-3">
                    <dt class="text-slate-500">Service</dt>
                    <dd class="font-medium text-slate-900">{{ $invoice->service_name }}</dd>
                </div>
                <div class="flex justify-between gap-4 border-b border-slate-100 pb-3">
                    <dt class="text-slate-500">Service price</dt>
                    <dd class="font-medium text-slate-900">{{ number_format((float) $invoice->service_price, 0) }} MMK</dd>
                </div>
                <div class="flex justify-between gap-4 border-b border-slate-100 pb-3">
                    <dt class="text-slate-500">Extra fee</dt>
                    <dd class="font-medium text-slate-900">{{ number_format((float) $invoice->extra_fee, 0) }} MMK</dd>
                </div>
                <div class="flex justify-between gap-4 border-b border-slate-100 pb-3">
                    <dt class="text-slate-500">Discount</dt>
                    <dd class="font-medium text-slate-900">{{ number_format((float) $invoice->discount_amount, 0) }} MMK</dd>
                </div>
                <div class="flex justify-between gap-4 border-b border-slate-100 pb-3 text-lg">
                    <dt class="font-semibold text-slate-900">Total</dt>
                    <dd class="font-bold text-slate-900">{{ number_format((float) $invoice->total_amount, 0) }} MMK</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-slate-500">Paid / remaining</dt>
                    <dd class="text-right font-semibold text-slate-900">
                        {{ number_format((float) $invoice->paid_amount, 0) }} /
                        {{ number_format((float) $invoice->remaining_amount, 0) }} MMK
                    </dd>
                </div>
            </dl>

            @if ($invoice->booking?->quotation?->status === \App\Enums\QuotationStatus::Accepted)
                <div class="mt-6 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-900">
                    <p class="font-semibold">Pricing source: accepted quotation</p>
                    <p class="mt-1">
                        This invoice uses
                        <a
                            href="{{ route('admin.quotations.show', $invoice->booking->quotation) }}"
                            class="font-semibold underline hover:text-emerald-950"
                        >
                            {{ $invoice->booking->quotation->quotation_no }}
                        </a>
                        pricing.
                    </p>
                </div>
            @endif

            @if ($invoice->note)
                <div class="mt-6 rounded-xl bg-slate-50 p-4 text-sm text-slate-700">
                    <p class="font-semibold text-slate-900">Note</p>
                    <p class="mt-1 whitespace-pre-line">{{ $invoice->note }}</p>
                </div>
            @endif
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">Payment history</h2>
            <div class="mt-4 space-y-3">
                @forelse ($invoice->payments->sortByDesc('paid_at') as $payment)
                    <a href="{{ route('admin.payments.show', $payment) }}" class="block rounded-xl bg-slate-50 p-4 hover:bg-slate-100">
                        <div class="flex justify-between gap-3">
                            <span class="font-semibold text-slate-900">{{ number_format((float) $payment->amount, 0) }} MMK</span>
                            <span class="text-xs text-slate-500">{{ $payment->paid_at?->format('d M Y') }}</span>
                        </div>
                        <p class="mt-1 text-sm text-slate-600">{{ $payment->payment_method ?? 'Method not specified' }}</p>
                        @if ($payment->note)
                            <p class="mt-1 text-xs text-slate-500">{{ $payment->note }}</p>
                        @endif
                    </a>
                @empty
                    <p class="text-sm text-slate-500">No payments recorded yet.</p>
                @endforelse
            </div>
        </section>
    </div>

    @if ($invoice->payment_status !== \App\Enums\PaymentStatus::Paid)
        <section class="mt-6 rounded-2xl border border-blue-200 bg-blue-50 p-6">
            <h2 class="text-lg font-semibold text-blue-950">Record payment</h2>
            <p class="mt-1 text-sm text-blue-900">
                Remaining balance: {{ number_format((float) $invoice->remaining_amount, 0) }} MMK
            </p>

            <form method="POST" action="{{ route('admin.invoices.payments.store', $invoice) }}" class="mt-5 grid gap-4 md:grid-cols-4">
                @csrf
                <label class="block">
                    <span class="mb-1 block text-sm font-medium text-slate-700">Amount</span>
                    <input type="number" name="amount" value="{{ old('amount') }}" min="1" max="{{ $invoice->remaining_amount }}" step="0.01" required class="w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                </label>
                <label class="block">
                    <span class="mb-1 block text-sm font-medium text-slate-700">Payment method</span>
                    <input type="text" name="payment_method" value="{{ old('payment_method') }}" placeholder="Cash, KPay" class="w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                </label>
                <label class="block md:col-span-2">
                    <span class="mb-1 block text-sm font-medium text-slate-700">Note</span>
                    <input type="text" name="note" value="{{ old('note') }}" class="w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                </label>
                <div class="md:col-span-4">
                    <button type="submit" class="rounded-xl bg-blue-700 px-4 py-2 text-sm font-medium text-white hover:bg-blue-800">
                        Record payment
                    </button>
                </div>
            </form>
        </section>
    @endif

    <div class="mt-6">
        <a href="{{ route('admin.bookings.show', $invoice->booking) }}" class="text-sm font-semibold text-blue-700 hover:text-blue-900">
            View related booking →
        </a>
    </div>
@endsection
