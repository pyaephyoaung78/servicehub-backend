@extends('admin.layouts.app')

@section('title', 'Quotations')
@section('page_title', 'Quotations')

@section('content')
<section class="mb-8 overflow-hidden rounded-xl bg-teal-950 px-5 py-6 shadow-[0_14px_32px_rgba(15,60,58,0.14)] sm:px-7 sm:py-7">
    <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm font-medium text-teal-300">Service pricing</p>
            <h1 class="mt-2 text-2xl font-semibold tracking-tight text-white sm:text-3xl">Quotation Management</h1>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-teal-50/85">
                Prepare service prices and monitor customer responses.
            </p>
        </div>

        <a
            href="{{ route('admin.quotations.create') }}"
            class="w-fit rounded-lg bg-white px-4 py-2.5 text-sm font-semibold text-teal-950 shadow-sm transition hover:bg-teal-50 focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-teal-950 active:translate-y-px">
            Create quotation
        </a>
    </div>
</section>

@if (session('success'))
<div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
    {{ session('success') }}
</div>
@endif

<form method="GET" class="mb-6 rounded-xl border border-teal-100 bg-teal-50/40 p-5 shadow-[0_1px_2px_rgba(15,60,58,0.06)] sm:p-6">
    <div class="mb-6">
        <h2 class="text-base font-semibold text-teal-950">Find quotations</h2>
        <p class="mt-1 text-sm text-teal-900/70">Search service prices or narrow the list by the customer response status.</p>
    </div>

    <div class="grid gap-5 md:grid-cols-2">
        <label class="block">
            <span class="mb-2 block text-sm font-medium text-teal-950">Search</span>
            <input
                type="search"
                name="search"
                value="{{ request('search') }}"
                placeholder="Quotation number, customer or service"
                class="h-11 w-full rounded-lg border-teal-200 bg-white px-4 text-sm placeholder:text-teal-900/40 focus:border-teal-600 focus:ring-teal-600">
        </label>

        <label class="block">
            <span class="mb-2 block text-sm font-medium text-teal-950">Status</span>
            <select
                name="status"
                class="h-11 w-full rounded-lg border-teal-200 bg-white px-4 text-sm focus:border-teal-600 focus:ring-teal-600">
                <option value="">All statuses</option>
                @foreach ($statuses as $status)
                <option
                    value="{{ $status->value }}"
                    @selected(request('status')===$status->value)
                    >
                    {{ strtoupper($status->value) }}
                </option>
                @endforeach
            </select>
        </label>
    </div>

    <div class="mt-6 flex flex-col gap-3 border-t border-teal-100 pt-5 sm:flex-row sm:items-center">
        <button
            type="submit"
            class="w-full rounded-lg bg-teal-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-teal-800 focus:outline-none focus:ring-2 focus:ring-teal-600 focus:ring-offset-2 active:translate-y-px sm:w-auto">
            Apply filters
        </button>
        <a
            href="{{ route('admin.quotations.index') }}"
            class="w-full rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-center text-sm font-semibold text-slate-700 shadow-sm transition hover:border-slate-400 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-teal-600 focus:ring-offset-2 sm:w-auto">
            Reset
        </a>
    </div>
</form>

<div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-[0_1px_2px_rgba(15,60,58,0.05)]">
    <div class="border-b border-slate-200 bg-white px-5 py-4 text-sm font-medium text-slate-600 sm:px-6">
        {{ $quotations->total() }} quotation{{ $quotations->total() === 1 ? '' : 's' }} found
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-teal-950 text-left text-xs font-semibold tracking-wide text-teal-50">
                <tr>
                    <th class="px-6 py-3">Quotation</th>
                    <th class="px-6 py-3">Customer</th>
                    <th class="px-6 py-3">Service</th>
                    <th class="px-6 py-3">Total</th>
                    <th class="px-6 py-3">Status</th>
                    <th class="px-6 py-3">Valid until</th>
                    <th class="px-6 py-3">Action</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-slate-100">
                @forelse ($quotations as $quotation)
                <tr class="transition hover:bg-teal-50/60">
                    <td class="px-6 py-4">
                        <p class="font-medium text-slate-900">{{ $quotation->quotation_no }}</p>
                        <p class="text-xs text-slate-500">
                            Booking #{{ $quotation->booking_id }}
                        </p>
                    </td>
                    <td class="px-6 py-4">
                        <p class="font-medium text-slate-900">{{ $quotation->customer?->name }}</p>
                        <p class="text-xs text-slate-500">{{ $quotation->customer?->email }}</p>
                    </td>
                    <td class="px-6 py-4">{{ $quotation->service_name }}</td>
                    <td class="whitespace-nowrap px-6 py-4 font-medium text-slate-900">
                        {{ number_format((float) $quotation->total_amount, 0) }} MMK
                    </td>
                    <td class="px-6 py-4">
                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-700">
                            {{ strtoupper($quotation->status->value) }}
                        </span>
                    </td>
                    <td class="whitespace-nowrap px-6 py-4 text-slate-600">
                        {{ $quotation->valid_until?->format('d M Y, h:i A') ?? 'No expiry' }}
                    </td>
                    <td class="px-6 py-4 text-right">
                        <a
                            href="{{ route('admin.quotations.show', $quotation) }}"
                            class="font-semibold text-teal-700 transition hover:text-teal-900 focus:outline-none focus:ring-2 focus:ring-teal-600 focus:ring-offset-2">
                            Open
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center text-slate-500">
                        No quotations match the current filters.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($quotations->hasPages())
    <div class="border-t border-slate-200 px-6 py-4">
        {{ $quotations->links() }}
    </div>
    @endif
</div>
@endsection