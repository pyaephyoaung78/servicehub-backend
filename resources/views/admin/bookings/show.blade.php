@extends('admin.layouts.app')

@section('title', 'Booking #' . $booking->id)
@section('page_title', 'Booking Details')

@section('content')
    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <a
                href="{{ route('admin.bookings.index') }}"
                class="text-sm font-medium text-blue-700 hover:text-blue-900"
            >
                ← Back to bookings
            </a>
            <h1 class="mt-2 text-2xl font-bold text-slate-900">
                Booking #{{ $booking->id }}
            </h1>
            <p class="mt-1 text-slate-500">
                Created {{ $booking->created_at?->format('d M Y, h:i A') }}
            </p>
        </div>

        <span class="w-fit rounded-full bg-slate-200 px-4 py-2 text-sm font-semibold text-slate-700">
            {{ strtoupper(str_replace('_', ' ', $booking->status->value)) }}
        </span>
    </div>

    @if (session('success'))
        <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            <p class="font-semibold">The booking action could not be completed.</p>
            <ul class="mt-1 list-inside list-disc">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid gap-6 xl:grid-cols-3">
        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm xl:col-span-2">
            <h2 class="text-lg font-semibold text-slate-900">Customer request</h2>

            <dl class="mt-5 grid gap-5 sm:grid-cols-2">
                <div>
                    <dt class="text-sm text-slate-500">Customer</dt>
                    <dd class="mt-1 font-medium text-slate-900">{{ $booking->customer?->name }}</dd>
                    <dd class="text-sm text-slate-600">{{ $booking->customer?->email }}</dd>
                </div>

                <div>
                    <dt class="text-sm text-slate-500">Phone</dt>
                    <dd class="mt-1 font-medium text-slate-900">{{ $booking->phone }}</dd>
                </div>

                <div>
                    <dt class="text-sm text-slate-500">Service</dt>
                    <dd class="mt-1 font-medium text-slate-900">{{ $booking->service_name }}</dd>
                    <dd class="text-sm text-slate-600">
                        {{ number_format((float) $booking->service_price, 0) }} MMK
                    </dd>
                </div>

                <div>
                    <dt class="text-sm text-slate-500">Scheduled at</dt>
                    <dd class="mt-1 font-medium text-slate-900">
                        {{ $booking->scheduled_at?->format('d M Y, h:i A') }}
                    </dd>
                </div>

                <div class="sm:col-span-2">
                    <dt class="text-sm text-slate-500">Address</dt>
                    <dd class="mt-1 whitespace-pre-line font-medium text-slate-900">{{ $booking->address }}</dd>
                </div>

                @if ($booking->customer_note)
                    <div class="sm:col-span-2">
                        <dt class="text-sm text-slate-500">Customer note</dt>
                        <dd class="mt-1 whitespace-pre-line text-slate-800">{{ $booking->customer_note }}</dd>
                    </div>
                @endif
            </dl>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">Workflow</h2>

            <dl class="mt-5 space-y-4 text-sm">
                <div>
                    <dt class="text-slate-500">Quotation</dt>
                    <dd class="mt-1 font-medium text-slate-900">
                        @if ($booking->quotation)
                            <a
                                href="{{ route('admin.quotations.show', $booking->quotation) }}"
                                class="text-blue-700 hover:text-blue-900"
                            >
                                {{ $booking->quotation->quotation_no }}
                            </a>
                            — {{ strtoupper($booking->quotation->status->value) }}
                        @else
                            @if ($booking->status === \App\Enums\BookingStatus::Pending)
                                <a
                                    href="{{ route('admin.quotations.create', ['booking' => $booking->id]) }}"
                                    class="text-blue-700 hover:text-blue-900"
                                >
                                    Create quotation
                                </a>
                            @else
                                Not sent
                            @endif
                        @endif
                    </dd>
                </div>

                <div>
                    <dt class="text-slate-500">Assigned staff</dt>
                    <dd class="mt-1 font-medium text-slate-900">
                        {{ $booking->latestAssignment?->staffProfile?->user?->name ?? 'Not assigned' }}
                    </dd>
                </div>

                <div>
                    <dt class="text-slate-500">Invoice</dt>
                    <dd class="mt-1 font-medium text-slate-900">
                        @if ($booking->invoice)
                            <a
                                href="{{ route('admin.invoices.show', $booking->invoice) }}"
                                class="text-blue-700 hover:text-blue-900"
                            >
                                {{ $booking->invoice->invoice_no }}
                            </a>
                            — {{ strtoupper($booking->invoice->payment_status->value) }}
                        @elseif ($booking->status === \App\Enums\BookingStatus::Completed)
                            <a
                                href="{{ route('admin.invoices.create', ['booking' => $booking->id]) }}"
                                class="text-blue-700 hover:text-blue-900"
                            >
                                Create invoice
                            </a>
                        @else
                            Not issued
                        @endif
                    </dd>
                </div>
            </dl>
        </section>
    </div>

    @if ($booking->cancellation_reason || $booking->rejection_reason)
        <section class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">Closure record</h2>

            @if ($booking->cancellation_reason)
                <p class="mt-3 text-sm text-slate-700">
                    <span class="font-semibold">Cancelled:</span>
                    {{ $booking->cancellation_reason }}
                    @if ($booking->cancelledBy)
                        by {{ $booking->cancelledBy->name }}
                    @endif
                </p>
            @endif

            @if ($booking->rejection_reason)
                <p class="mt-3 text-sm text-slate-700">
                    <span class="font-semibold">Rejected:</span>
                    {{ $booking->rejection_reason }}
                    @if ($booking->rejectedBy)
                        by {{ $booking->rejectedBy->name }}
                    @endif
                </p>
            @endif
        </section>
    @endif

    @if (in_array($booking->status, [
        \App\Enums\BookingStatus::Pending,
        \App\Enums\BookingStatus::Assigned,
        \App\Enums\BookingStatus::Accepted,
    ], true))
        <section class="mt-6 rounded-2xl border border-amber-200 bg-amber-50 p-6">
            <h2 class="text-lg font-semibold text-amber-950">Booking actions</h2>
            <p class="mt-1 text-sm text-amber-900">
                These actions are permanent and preserve an audit record instead of deleting the booking.
            </p>

            <div class="mt-5 grid gap-5 lg:grid-cols-2">
                @if ($booking->status === \App\Enums\BookingStatus::Pending)
                    <form method="POST" action="{{ route('admin.bookings.reject', $booking) }}" class="rounded-xl border border-red-200 bg-white p-4">
                        @csrf
                        @method('PATCH')
                        <h3 class="font-semibold text-red-800">Reject booking</h3>
                        <p class="mt-1 text-sm text-slate-600">Use when the request cannot be fulfilled.</p>
                        <label class="mt-4 block">
                            <span class="mb-1 block text-sm font-medium text-slate-700">Reason</span>
                            <textarea name="reason" rows="3" required class="w-full rounded-lg border-slate-300 text-sm focus:border-red-500 focus:ring-red-500">{{ old('reason') }}</textarea>
                        </label>
                        <button type="submit" class="mt-3 rounded-xl bg-red-700 px-4 py-2 text-sm font-medium text-white hover:bg-red-800">
                            Reject booking
                        </button>
                    </form>
                @endif

                <form method="POST" action="{{ route('admin.bookings.cancel', $booking) }}" class="rounded-xl border border-slate-300 bg-white p-4">
                    @csrf
                    @method('PATCH')
                    <h3 class="font-semibold text-slate-900">Cancel booking</h3>
                    <p class="mt-1 text-sm text-slate-600">Use for an approved booking that must no longer proceed.</p>
                    <label class="mt-4 block">
                        <span class="mb-1 block text-sm font-medium text-slate-700">Reason</span>
                        <textarea name="reason" rows="3" required class="w-full rounded-lg border-slate-300 text-sm focus:border-slate-500 focus:ring-slate-500">{{ old('reason') }}</textarea>
                    </label>
                    <button type="submit" class="mt-3 rounded-xl bg-slate-800 px-4 py-2 text-sm font-medium text-white hover:bg-slate-950">
                        Cancel booking
                    </button>
                </form>
            </div>
        </section>
    @endif
@endsection
