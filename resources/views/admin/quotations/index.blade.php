@extends('admin.layouts.app')

@section('title', 'Quotations')
@section('page_title', 'Quotations')

@section('content')
    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Quotation Management</h1>
            <p class="mt-1 text-slate-500">
                Prepare service prices and monitor customer responses.
            </p>
        </div>

        <a
            href="{{ route('admin.quotations.create') }}"
            class="w-fit rounded-xl bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700"
        >
            Create quotation
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
                    placeholder="Quotation number, customer or service"
                    class="w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500"
                >
            </label>

            <label class="block">
                <span class="mb-1 block text-sm font-medium text-slate-700">Status</span>
                <select
                    name="status"
                    class="w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500"
                >
                    <option value="">All statuses</option>
                    @foreach ($statuses as $status)
                        <option
                            value="{{ $status->value }}"
                            @selected(request('status') === $status->value)
                        >
                            {{ strtoupper($status->value) }}
                        </option>
                    @endforeach
                </select>
            </label>
        </div>

        <div class="mt-5 flex gap-3">
            <button
                type="submit"
                class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700"
            >
                Apply filters
            </button>
            <a
                href="{{ route('admin.quotations.index') }}"
                class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
            >
                Reset
            </a>
        </div>
    </form>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 px-6 py-4 text-sm text-slate-500">
            {{ $quotations->total() }} quotation{{ $quotations->total() === 1 ? '' : 's' }} found
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-6 py-3">Quotation</th>
                        <th class="px-6 py-3">Customer</th>
                        <th class="px-6 py-3">Service</th>
                        <th class="px-6 py-3">Total</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3">Valid until</th>
                        <th class="px-6 py-3"></th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                    @forelse ($quotations as $quotation)
                        <tr class="hover:bg-slate-50/80">
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
                                    class="font-medium text-blue-700 hover:text-blue-900"
                                >
                                    View
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
