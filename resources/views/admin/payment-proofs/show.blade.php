@extends('admin.layouts.app')

@section('title', 'Payment Proof #' . $paymentProof->id)
@section('page_title', 'Payment Proof Details')

@section('content')
<section class="mb-8 overflow-hidden rounded-xl bg-teal-950 px-5 py-6 shadow-[0_14px_32px_rgba(15,60,58,0.14)] sm:px-7 sm:py-7">
    <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <a href="{{ route('admin.payment-proofs.index') }}" class="inline-flex items-center gap-1.5 rounded-lg border border-teal-700 bg-teal-900/70 px-2.5 py-1.5 text-xs font-semibold text-teal-50 transition hover:bg-teal-800 focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-teal-950 active:translate-y-px">Back to payment proofs</a>
            <p class="mt-5 text-sm font-medium text-teal-300">Payment evidence</p>
            <h1 class="mt-2 text-2xl font-semibold tracking-tight text-white sm:text-3xl">Payment Proof #{{ $paymentProof->id }}</h1>
            <p class="mt-2 text-sm text-teal-50/85">Submitted {{ $paymentProof->created_at?->format('d M Y, h:i A') }}</p>
        </div>

        <span class="w-fit rounded-full border border-teal-700 bg-teal-900 px-3 py-1.5 text-xs font-semibold text-teal-50">
            {{ strtoupper($paymentProof->status->value) }}
        </span>
    </div>
</section>

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
    <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-[0_1px_2px_rgba(15,60,58,0.05)] sm:p-6 xl:col-span-2">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">Uploaded proof</h2>
                <p class="mt-1 text-sm text-slate-500">
                    {{ $paymentProof->proof_original_name ?? 'Payment proof file' }}
                </p>
            </div>
            <a href="{{ route('admin.payment-proofs.file', $paymentProof) }}" target="_blank" class="rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-slate-400 hover:bg-slate-50">
                Open file
            </a>
        </div>

        @if (str_starts_with($paymentProof->proof_mime_type ?? '', 'image/'))
        <img
            src="{{ route('admin.payment-proofs.file', $paymentProof) }}"
            alt="Uploaded payment proof"
            class="mt-5 max-h-[32rem] w-full rounded-lg border border-slate-200 object-contain">
        @else
        <div class="mt-5 rounded-xl bg-slate-50 p-8 text-center text-sm text-slate-600">
            This file is a PDF. Use “Open file” to view it.
        </div>
        @endif
    </section>

    <section class="rounded-xl border border-teal-100 bg-teal-50/40 p-5 shadow-[0_1px_2px_rgba(15,60,58,0.05)] sm:p-6">
        <h2 class="text-lg font-semibold text-slate-900">Payment details</h2>
        <dl class="mt-5 space-y-4 text-sm">
            <div>
                <dt class="text-slate-500">Amount</dt>
                <dd class="mt-1 text-xl font-bold text-emerald-700">{{ number_format((float) $paymentProof->amount, 0) }} MMK</dd>
            </div>
            <div>
                <dt class="text-slate-500">Payment method</dt>
                <dd class="mt-1 font-medium text-slate-900">{{ $paymentProof->payment_method }}</dd>
            </div>
            <div>
                <dt class="text-slate-500">Customer</dt>
                <dd class="mt-1 font-medium text-slate-900">{{ $paymentProof->customer?->name }}</dd>
                <dd class="text-slate-600">{{ $paymentProof->customer?->email }}</dd>
            </div>
            <div>
                <dt class="text-slate-500">Customer note</dt>
                <dd class="mt-1 whitespace-pre-line text-slate-800">{{ $paymentProof->note ?? 'No note' }}</dd>
            </div>
        </dl>

        @if ($paymentProof->invoice)
        <a href="{{ route('admin.invoices.show', $paymentProof->invoice) }}" class="mt-5 inline-flex rounded-lg border border-teal-200 bg-white px-3.5 py-2 text-sm font-semibold text-teal-800 transition hover:bg-teal-50">
            View invoice
        </a>
        @endif
    </section>
</div>

@if ($paymentProof->reviewed_at)
<section class="mt-6 rounded-xl border border-slate-200 bg-white p-5 shadow-[0_1px_2px_rgba(15,60,58,0.05)] sm:p-6">
    <h2 class="font-semibold text-slate-900">Review record</h2>
    <p class="mt-2 text-sm text-slate-600">
        Reviewed {{ $paymentProof->reviewed_at->format('d M Y, h:i A') }}
        by {{ $paymentProof->reviewedBy?->name ?? 'Unknown admin' }}.
    </p>
    <p class="mt-2 whitespace-pre-line text-sm text-slate-800">{{ $paymentProof->review_note ?? 'No review note.' }}</p>

    @if ($paymentProof->invoicePayment)
    <a href="{{ route('admin.payments.show', $paymentProof->invoicePayment) }}" class="mt-3 inline-flex rounded-lg border border-teal-200 bg-white px-3.5 py-2 text-sm font-semibold text-teal-800 transition hover:bg-teal-50">
        View recorded payment
    </a>
    @endif
</section>
@endif

@if ($paymentProof->status === \App\Enums\PaymentProofStatus::Pending)
<section class="mt-6 grid gap-6 lg:grid-cols-2">
    <form method="POST" action="{{ route('admin.payment-proofs.approve', $paymentProof) }}" class="rounded-xl border border-emerald-200 bg-emerald-50 p-5 sm:p-6">
        @csrf
        <h2 class="font-semibold text-emerald-950">Approve proof</h2>
        <p class="mt-1 text-sm text-emerald-900">
            Approval will record {{ number_format((float) $paymentProof->amount, 0) }} MMK on the invoice.
        </p>
        <label class="mt-4 block">
            <span class="mb-1 block text-sm font-medium text-slate-700">Review note</span>
            <textarea name="review_note" rows="3" class="w-full rounded-lg border-emerald-200 bg-white px-4 py-3 text-sm focus:border-emerald-500 focus:ring-emerald-500" placeholder="Optional approval note"></textarea>
        </label>
        <button type="submit" class="mt-4 rounded-lg bg-emerald-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2">
            Approve and record payment
        </button>
    </form>

    <form method="POST" action="{{ route('admin.payment-proofs.reject', $paymentProof) }}" class="rounded-xl border border-red-200 bg-red-50 p-5 sm:p-6">
        @csrf
        <h2 class="font-semibold text-red-950">Reject proof</h2>
        <p class="mt-1 text-sm text-red-900">Keep a clear reason so the customer knows what to correct.</p>
        <label class="mt-4 block">
            <span class="mb-1 block text-sm font-medium text-slate-700">Rejection reason</span>
            <textarea name="review_note" rows="3" required class="w-full rounded-lg border-red-200 bg-white px-4 py-3 text-sm focus:border-red-500 focus:ring-red-500" placeholder="Explain why this proof cannot be accepted"></textarea>
        </label>
        <button type="submit" class="mt-4 rounded-lg bg-red-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-red-800 focus:outline-none focus:ring-2 focus:ring-red-600 focus:ring-offset-2">
            Reject proof
        </button>
    </form>
</section>
@endif
@endsection