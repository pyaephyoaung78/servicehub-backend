@extends('admin.layouts.app')

@section('title', 'Invoices')
@section('page_title', 'Invoices')

@section('content')
    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Invoice Management</h1>
            <p class="mt-1 text-slate-500">
                Issue invoices for completed work and record customer payments.
            </p>
        </div>

        <a
            href="{{ route('admin.invoices.create') }}"
            class="w-fit rounded-xl bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700"
        >
            Create invoice
        </a>
    </div>

    @if (session('success'))
        <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    <form method="GET" class="mb-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="grid gap-4 md:grid-cols-2">
            <label class="block">
                <span class="mb-1 block text-sm font-medium text-slate-700">Search</span>
                <input
                    type="search"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Invoice number, customer or service"
                    class="w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500"
                >
            </label>

            <label class="block">
                <span class="mb-1 block text-sm font-medium text-slate-700">Payment status</span>
                <select name="payment_status" class="w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">All statuses</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status->value }}" @selected(request('payment_status') === $status->value)>
                            {{ strtoupper($status->value) }}
                        </option>
                    @endforeach
                </select>
            </label>
        </div>

        <div class="mt-5 flex gap-3">
            <button type="submit" class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">
                Apply filters
            </button>
            <a href="{{ route('admin.invoices.index') }}" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                Reset
            </a>
        </div>
    </form>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 px-6 py-4 text-sm text-slate-500">
            {{ $invoices->total() }} invoice{{ $invoices->total() === 1 ? '' : 's' }} found
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-6 py-3">Invoice</th>
                        <th class="px-6 py-3">Customer</th>
                        <th class="px-6 py-3">Service</th>
                        <th class="px-6 py-3">Total</th>
                        <th class="px-6 py-3">Remaining</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3"></th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                    @forelse ($invoices as $invoice)
                        <tr class="hover:bg-slate-50/80">
                            <td class="px-6 py-4">
                                <p class="font-medium text-slate-900">{{ $invoice->invoice_no }}</p>
                                <p class="text-xs text-slate-500">Booking #{{ $invoice->booking_id }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="font-medium text-slate-900">{{ $invoice->customer?->name }}</p>
                                <p class="text-xs text-slate-500">{{ $invoice->customer?->email }}</p>
                            </td>
                            <td class="px-6 py-4">{{ $invoice->service_name }}</td>
                            <td class="whitespace-nowrap px-6 py-4 font-medium text-slate-900">
                                {{ number_format((float) $invoice->total_amount, 0) }} MMK
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-slate-600">
                                {{ number_format((float) $invoice->remaining_amount, 0) }} MMK
                            </td>
                            <td class="px-6 py-4">
                                <span class="rounded-full px-3 py-1 text-xs font-medium {{ $invoice->payment_status === \App\Enums\PaymentStatus::Paid ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                                    {{ strtoupper($invoice->payment_status->value) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('admin.invoices.show', $invoice) }}" class="font-medium text-blue-700 hover:text-blue-900">
                                    View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-500">
                                No invoices match the current filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($invoices->hasPages())
            <div class="border-t border-slate-200 px-6 py-4">
                {{ $invoices->links() }}
            </div>
        @endif
    </div>
@endsection
