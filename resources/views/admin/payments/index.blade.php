@extends('admin.layouts.app')

@section('title', 'Payments')
@section('page_title', 'Payments')

@section('content')
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-slate-900">Payment Ledger</h1>
        <p class="mt-1 text-slate-500">
            Review every payment received against customer invoices.
        </p>
    </div>

    <form method="GET" class="mb-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <label class="block xl:col-span-2">
                <span class="mb-1 block text-sm font-medium text-slate-700">Search</span>
                <input
                    type="search"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Invoice, customer, method or note"
                    class="w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500"
                >
            </label>
            <label class="block">
                <span class="mb-1 block text-sm font-medium text-slate-700">Payment method</span>
                <input
                    type="text"
                    name="payment_method"
                    value="{{ request('payment_method') }}"
                    placeholder="Cash, KPay, WavePay"
                    class="w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500"
                >
            </label>
            <label class="block">
                <span class="mb-1 block text-sm font-medium text-slate-700">Paid from</span>
                <input
                    type="date"
                    name="date_from"
                    value="{{ request('date_from') }}"
                    class="w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500"
                >
            </label>
            <label class="block">
                <span class="mb-1 block text-sm font-medium text-slate-700">Paid to</span>
                <input
                    type="date"
                    name="date_to"
                    value="{{ request('date_to') }}"
                    class="w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500"
                >
            </label>
        </div>

        <div class="mt-5 flex gap-3">
            <button type="submit" class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">
                Apply filters
            </button>
            <a href="{{ route('admin.payments.index') }}" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                Reset
            </a>
        </div>
    </form>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 px-6 py-4 text-sm text-slate-500">
            {{ $payments->total() }} payment{{ $payments->total() === 1 ? '' : 's' }} found
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-6 py-3">Paid at</th>
                        <th class="px-6 py-3">Invoice</th>
                        <th class="px-6 py-3">Customer</th>
                        <th class="px-6 py-3">Amount</th>
                        <th class="px-6 py-3">Method</th>
                        <th class="px-6 py-3">Received by</th>
                        <th class="px-6 py-3"></th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                    @forelse ($payments as $payment)
                        <tr class="hover:bg-slate-50/80">
                            <td class="whitespace-nowrap px-6 py-4 text-slate-600">
                                {{ $payment->paid_at?->format('d M Y, h:i A') }}
                            </td>
                            <td class="px-6 py-4">
                                <p class="font-medium text-slate-900">{{ $payment->invoice?->invoice_no }}</p>
                                <p class="text-xs text-slate-500">{{ $payment->invoice?->service_name }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="font-medium text-slate-900">{{ $payment->invoice?->customer?->name }}</p>
                                <p class="text-xs text-slate-500">{{ $payment->invoice?->customer?->email }}</p>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 font-semibold text-emerald-700">
                                {{ number_format((float) $payment->amount, 0) }} MMK
                            </td>
                            <td class="px-6 py-4 text-slate-600">
                                {{ $payment->payment_method ?? 'Not specified' }}
                            </td>
                            <td class="px-6 py-4 text-slate-600">
                                {{ $payment->receivedBy?->name ?? 'System' }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('admin.payments.show', $payment) }}" class="font-medium text-blue-700 hover:text-blue-900">
                                    View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-500">
                                No payments match the current filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($payments->hasPages())
            <div class="border-t border-slate-200 px-6 py-4">
                {{ $payments->links() }}
            </div>
        @endif
    </div>
@endsection
