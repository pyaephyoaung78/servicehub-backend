@extends('admin.layouts.app')
@section('title', 'Payment Proofs')
@section('page_title', 'Payment Proofs')
@section('content')
<section class="mb-8 overflow-hidden rounded-xl bg-teal-950 px-5 py-6 shadow-[0_14px_32px_rgba(15,60,58,0.14)] sm:px-7 sm:py-7">
    <p class="text-sm font-medium text-teal-300">Finance operations</p>
    <h1 class="mt-2 text-2xl font-semibold tracking-tight text-white sm:text-3xl">Payment proof review</h1>
    <p class="mt-2 max-w-2xl text-sm leading-6 text-teal-50/85">Verify customer payment evidence before adding it to an invoice.</p>
</section>
<form method="GET" class="mb-6 rounded-xl border border-teal-100 bg-teal-50/40 p-5 shadow-[0_1px_2px_rgba(15,60,58,0.06)] sm:p-6">
    <div class="mb-6">
        <h2 class="text-base font-semibold text-teal-950">Find payment proofs</h2>
        <p class="mt-1 text-sm text-teal-900/70">Search submitted evidence or filter the review queue by current status.</p>
    </div>
    <div class="grid gap-5 md:grid-cols-2"><label class="block"><span class="mb-2 block text-sm font-medium text-teal-950">Search</span><input type="search" name="search" value="{{ request('search') }}" placeholder="Invoice, customer or payment method" class="h-11 w-full rounded-lg border-teal-200 bg-white px-4 text-sm placeholder:text-teal-900/40 focus:border-teal-600 focus:ring-teal-600"></label><label class="block"><span class="mb-2 block text-sm font-medium text-teal-950">Status</span><select name="status" class="h-11 w-full rounded-lg border-teal-200 bg-white px-4 text-sm focus:border-teal-600 focus:ring-teal-600">
                <option value="">All statuses</option>@foreach ($statuses as $status)<option value="{{ $status->value }}" @selected(request('status')===$status->value)>{{ ucfirst($status->value) }}</option>@endforeach
            </select></label></div>
    <div class="mt-6 flex flex-col gap-3 border-t border-teal-100 pt-5 sm:flex-row sm:items-center"><button type="submit" class="w-full rounded-lg bg-teal-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-teal-800 focus:outline-none focus:ring-2 focus:ring-teal-600 focus:ring-offset-2 active:translate-y-px sm:w-auto">Apply filters</button><a href="{{ route('admin.payment-proofs.index') }}" class="w-full rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-center text-sm font-semibold text-slate-700 shadow-sm transition hover:border-slate-400 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-teal-600 focus:ring-offset-2 sm:w-auto">Reset</a></div>
</form>
<div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-[0_1px_2px_rgba(15,60,58,0.05)]">
    <div class="border-b border-slate-200 bg-white px-5 py-4 text-sm font-medium text-slate-600 sm:px-6">{{ $paymentProofs->total() }} proof{{ $paymentProofs->total() === 1 ? '' : 's' }} found</div>
    <div class="overflow-x-auto">
        <table class="w-full min-w-[950px] text-sm">
            <thead class="bg-teal-950 text-left text-xs font-semibold tracking-wide text-teal-50">
                <tr>
                    <th class="px-6 py-3.5">Submitted</th>
                    <th class="px-6 py-3.5">Invoice</th>
                    <th class="px-6 py-3.5">Customer</th>
                    <th class="px-6 py-3.5">Amount</th>
                    <th class="px-6 py-3.5">Method</th>
                    <th class="px-6 py-3.5">Status</th>
                    <th class="px-6 py-3.5 text-center"><span>Action</span></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($paymentProofs as $paymentProof)
                <tr class="transition hover:bg-teal-50/60">
                    <td class="whitespace-nowrap px-6 py-4 text-slate-600">{{ $paymentProof->created_at?->format('d M Y, h:i A') }}</td>
                    <td class="px-6 py-4">
                        <p class="font-semibold text-slate-900">{{ $paymentProof->invoice?->invoice_no }}</p>
                        <p class="text-xs text-slate-500">Invoice balance: {{ number_format((float) $paymentProof->invoice?->remaining_amount, 0) }} MMK</p>
                    </td>
                    <td class="px-6 py-4">
                        <p class="font-semibold text-slate-900">{{ $paymentProof->customer?->name }}</p>
                        <p class="text-xs text-slate-500">{{ $paymentProof->customer?->email }}</p>
                    </td>
                    <td class="whitespace-nowrap px-6 py-4 font-semibold text-emerald-700">{{ number_format((float) $paymentProof->amount, 0) }} MMK</td>
                    <td class="px-6 py-4 text-slate-600">{{ $paymentProof->payment_method }}</td>
                    <td class="px-6 py-4"><span class="rounded-full px-3 py-1 text-xs font-medium {{ $paymentProof->status === \App\Enums\PaymentProofStatus::Pending ? 'bg-amber-50 text-amber-700' : ($paymentProof->status === \App\Enums\PaymentProofStatus::Approved ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700') }}">{{ strtoupper($paymentProof->status->value) }}</span></td>
                    <td class="px-6 py-4 text-center"><a href="{{ route('admin.payment-proofs.show', $paymentProof) }}" class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-teal-700 transition hover:bg-teal-50 hover:text-teal-900 focus:outline-none focus:ring-2 focus:ring-teal-600 focus:ring-offset-2" aria-label="Review payment proof #{{ $paymentProof->id }}"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12S5.75 5.25 12 5.25 21.75 12 21.75 12 18.25 18.75 12 18.75 2.25 12 2.25 12Z" />
                                <circle cx="12" cy="12" r="2.75" />
                            </svg><span class="sr-only">Review payment proof</span></a></td>
                </tr>
                @empty<tr>
                    <td colspan="7" class="px-6 py-12 text-center text-slate-500">No payment proofs match the current filters.</td>
                </tr>@endforelse
            </tbody>
        </table>
    </div>@if ($paymentProofs->hasPages())<div class="border-t border-slate-200 px-6 py-4">{{ $paymentProofs->links() }}</div>@endif
</div>
@endsection