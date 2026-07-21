@extends('admin.layouts.app')

@section('title', $quotation->quotation_no)
@section('page_title', 'Quotation Details')

@section('content')
    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <a
                href="{{ route('admin.quotations.index') }}"
                class="text-sm font-medium text-blue-700 hover:text-blue-900"
            >
                ← Back to quotations
            </a>
            <h1 class="mt-2 text-2xl font-bold text-slate-900">
                {{ $quotation->quotation_no }}
            </h1>
            <p class="mt-1 text-slate-500">
                Booking #{{ $quotation->booking_id }} · Sent {{ $quotation->sent_at?->format('d M Y, h:i A') ?? 'Not sent' }}
            </p>
        </div>

        <span class="w-fit rounded-full bg-slate-200 px-4 py-2 text-sm font-semibold text-slate-700">
            {{ strtoupper($quotation->status->value) }}
        </span>
    </div>

    @if (session('success'))
        <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid gap-6 xl:grid-cols-3">
        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm xl:col-span-2">
            <h2 class="text-lg font-semibold text-slate-900">Quotation amount</h2>

            <dl class="mt-5 space-y-4">
                <div class="flex justify-between gap-4 border-b border-slate-100 pb-3">
                    <dt class="text-slate-500">Service</dt>
                    <dd class="font-medium text-slate-900">{{ $quotation->service_name }}</dd>
                </div>
                <div class="flex justify-between gap-4 border-b border-slate-100 pb-3">
                    <dt class="text-slate-500">Service price</dt>
                    <dd class="font-medium text-slate-900">{{ number_format((float) $quotation->service_price, 0) }} MMK</dd>
                </div>
                <div class="flex justify-between gap-4 border-b border-slate-100 pb-3">
                    <dt class="text-slate-500">Extra fee</dt>
                    <dd class="font-medium text-slate-900">{{ number_format((float) $quotation->extra_fee, 0) }} MMK</dd>
                </div>
                <div class="flex justify-between gap-4 border-b border-slate-100 pb-3">
                    <dt class="text-slate-500">Discount</dt>
                    <dd class="font-medium text-slate-900">{{ number_format((float) $quotation->discount_amount, 0) }} MMK</dd>
                </div>
                <div class="flex justify-between gap-4 text-lg">
                    <dt class="font-semibold text-slate-900">Total</dt>
                    <dd class="font-bold text-slate-900">{{ number_format((float) $quotation->total_amount, 0) }} MMK</dd>
                </div>
            </dl>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">Customer response</h2>

            <dl class="mt-5 space-y-4 text-sm">
                <div>
                    <dt class="text-slate-500">Customer</dt>
                    <dd class="mt-1 font-medium text-slate-900">{{ $quotation->customer?->name }}</dd>
                    <dd class="text-slate-600">{{ $quotation->customer?->email }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">Valid until</dt>
                    <dd class="mt-1 font-medium text-slate-900">
                        {{ $quotation->valid_until?->format('d M Y, h:i A') ?? 'No expiry' }}
                    </dd>
                </div>
                <div>
                    <dt class="text-slate-500">Accepted at</dt>
                    <dd class="mt-1 font-medium text-slate-900">
                        {{ $quotation->accepted_at?->format('d M Y, h:i A') ?? '—' }}
                    </dd>
                </div>
                <div>
                    <dt class="text-slate-500">Rejected at</dt>
                    <dd class="mt-1 font-medium text-slate-900">
                        {{ $quotation->rejected_at?->format('d M Y, h:i A') ?? '—' }}
                    </dd>
                </div>
            </dl>
        </section>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        @if ($quotation->admin_note)
            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="font-semibold text-slate-900">Admin note</h2>
                <p class="mt-3 whitespace-pre-line text-sm text-slate-700">{{ $quotation->admin_note }}</p>
            </section>
        @endif

        @if ($quotation->customer_response_note)
            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="font-semibold text-slate-900">Customer response note</h2>
                <p class="mt-3 whitespace-pre-line text-sm text-slate-700">{{ $quotation->customer_response_note }}</p>
            </section>
        @endif
    </div>

    <section class="mt-6 rounded-2xl border border-blue-200 bg-blue-50 p-6">
        <p class="text-sm text-blue-900">
            Quotations are sent to the customer through the Flutter app. After acceptance,
            staff assignment becomes available from the related booking.
        </p>
        <a
            href="{{ route('admin.bookings.show', $quotation->booking) }}"
            class="mt-3 inline-block text-sm font-semibold text-blue-800 hover:text-blue-950"
        >
            View related booking →
        </a>
    </section>
@endsection
