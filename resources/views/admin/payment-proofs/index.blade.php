@extends('admin.layouts.app')

@section('title', 'Payment Proofs')
@section('page_title', 'Payment Proofs')

@section('content')
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-slate-900">Payment Proof Review</h1>
        <p class="mt-1 text-slate-500">
            Verify customer payment evidence before adding it to an invoice.
        </p>
    </div>

    <form method="GET" class="mb-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="grid gap-4 md:grid-cols-3">
            <label class="block md:col-span-2">
                <span class="mb-1 block text-sm font-medium text-slate-700">Search</span>
                <input
                    type="search"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Invoice, customer or payment method"
                    class="w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500"
                >
            </label>
            <label class="block">
                <span class="mb-1 block text-sm font-medium text-slate-700">Status</span>
                <select name="status" class="w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">All statuses</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status->value }}" @selected(request('status') === $status->value)>
                            {{ ucfirst($status->value) }}
                        </option>
                    @endforeach
                </select>
            </label>
        </div>

        <div class="mt-5 flex gap-3">
            <button type="submit" class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">
                Apply filters
            </button>
            <a href="{{ route('admin.payment-proofs.index') }}" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                Reset
            </a>
        </div>
    </form>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 px-6 py-4 text-sm text-slate-500">
            {{ $paymentProofs->total() }} proof{{ $paymentProofs->total() === 1 ? '' : 's' }} found
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-6 py-3">Submitted</th>
                        <th class="px-6 py-3">Invoice</th>
                        <th class="px-6 py-3">Customer</th>
                        <th class="px-6 py-3">Amount</th>
                        <th class="px-6 py-3">Method</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($paymentProofs as $paymentProof)
                        <tr class="hover:bg-slate-50/80">
                            <td class="whitespace-nowrap px-6 py-4 text-slate-600">
                                {{ $paymentProof->created_at?->format('d M Y, h:i A') }}
                            </td>
                            <td class="px-6 py-4">
                                <p class="font-medium text-slate-900">{{ $paymentProof->invoice?->invoice_no }}</p>
                                <p class="text-xs text-slate-500">Invoice balance: {{ number_format((float) $paymentProof->invoice?->remaining_amount, 0) }} MMK</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="font-medium text-slate-900">{{ $paymentProof->customer?->name }}</p>
                                <p class="text-xs text-slate-500">{{ $paymentProof->customer?->email }}</p>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 font-semibold text-emerald-700">
                                {{ number_format((float) $paymentProof->amount, 0) }} MMK
                            </td>
                            <td class="px-6 py-4 text-slate-600">{{ $paymentProof->payment_method }}</td>
                            <td class="px-6 py-4">
                                <span class="rounded-full px-3 py-1 text-xs font-medium {{ $paymentProof->status === \App\Enums\PaymentProofStatus::Pending ? 'bg-amber-50 text-amber-700' : ($paymentProof->status === \App\Enums\PaymentProofStatus::Approved ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700') }}">
                                    {{ strtoupper($paymentProof->status->value) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('admin.payment-proofs.show', $paymentProof) }}" class="font-medium text-blue-700 hover:text-blue-900">
                                    Review
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-500">
                                No payment proofs match the current filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($paymentProofs->hasPages())
            <div class="border-t border-slate-200 px-6 py-4">
                {{ $paymentProofs->links() }}
            </div>
        @endif
    </div>
@endsection
