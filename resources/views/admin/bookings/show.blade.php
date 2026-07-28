@extends('admin.layouts.app')

@section('title', 'Booking #' . $booking->id)
@section('page_title', 'Booking Details')

@section('content')
<section class="mb-8 overflow-hidden rounded-xl bg-teal-950 px-5 py-6 shadow-[0_14px_32px_rgba(15,60,58,0.14)] sm:px-7 sm:py-7">
    <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <a
                href="{{ route('admin.bookings.index') }}"
                class="inline-flex items-center gap-1.5 rounded-lg border border-teal-700 bg-teal-900/70 px-2.5 py-1.5 text-xs font-semibold text-teal-50 transition hover:bg-teal-800 focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-teal-950 active:translate-y-px">
                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                </svg>
                Back to bookings
            </a>
            <p class="mt-5 text-sm font-medium text-teal-300">Customer service request</p>
            <h1 class="mt-2 text-2xl font-semibold tracking-tight text-white sm:text-3xl">Booking #{{ $booking->id }}</h1>
            <p class="mt-2 text-sm text-teal-50/85">Created {{ $booking->created_at?->format('d M Y, h:i A') }}</p>
        </div>

        <span class="w-fit rounded-full border border-teal-700 bg-teal-900 px-4 py-2 text-sm font-semibold text-teal-50">
            {{ strtoupper(str_replace('_', ' ', $booking->status->value)) }}
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
    <p class="font-semibold">The booking action could not be completed.</p>
    <ul class="mt-1 list-inside list-disc">
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<div class="grid gap-6 xl:grid-cols-3">
    <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-[0_1px_2px_rgba(15,60,58,0.05)] xl:col-span-2">
        <div class="border-b border-slate-200 px-5 py-5 sm:px-6">
            <h2 class="text-lg font-semibold tracking-tight text-slate-950">Customer request</h2>
            <p class="mt-1 text-sm text-slate-500">Service and contact information submitted by the customer.</p>
        </div>

        <dl class="grid gap-4 p-5 sm:grid-cols-2 sm:p-6">
            <div class="rounded-lg bg-teal-50/50 p-4">
                <dt class="text-sm font-medium text-teal-900/70">Customer</dt>
                <dd class="mt-2 font-semibold text-slate-900">{{ $booking->customer?->name }}</dd>
                <dd class="mt-1 text-sm text-slate-600">{{ $booking->customer?->email }}</dd>
            </div>

            <div class="rounded-lg bg-teal-50/50 p-4">
                <dt class="text-sm font-medium text-teal-900/70">Phone</dt>
                <dd class="mt-2 font-semibold text-slate-900">{{ $booking->phone }}</dd>
            </div>

            <div class="rounded-lg bg-teal-50/50 p-4">
                <dt class="text-sm font-medium text-teal-900/70">Service</dt>
                <dd class="mt-2 font-semibold text-slate-900">{{ $booking->service_name }}</dd>
                <dd class="mt-1 text-sm text-slate-600">{{ number_format((float) $booking->service_price, 0) }} MMK</dd>
            </div>

            <div class="rounded-lg bg-teal-50/50 p-4">
                <dt class="text-sm font-medium text-teal-900/70">Scheduled at</dt>
                <dd class="mt-2 font-semibold text-slate-900">{{ $booking->scheduled_at?->format('d M Y, h:i A') }}</dd>
            </div>

            <div class="rounded-lg bg-teal-50/50 p-4 sm:col-span-2">
                <dt class="text-sm font-medium text-teal-900/70">Address</dt>
                <dd class="mt-2 whitespace-pre-line font-semibold text-slate-900">{{ $booking->address }}</dd>
            </div>

            @if ($booking->customer_note)
            <div class="rounded-lg border border-amber-100 bg-amber-50/70 p-4 sm:col-span-2">
                <dt class="text-sm font-medium text-amber-900/80">Customer note</dt>
                <dd class="mt-2 whitespace-pre-line text-slate-800">{{ $booking->customer_note }}</dd>
            </div>
            @endif
        </dl>
    </section>

    <section class="rounded-xl border border-teal-100 bg-teal-50/40 p-5 shadow-[0_1px_2px_rgba(15,60,58,0.05)] sm:p-6">
        <h2 class="text-lg font-semibold tracking-tight text-teal-950">Workflow</h2>
        <p class="mt-1 text-sm text-teal-900/70">Related records for this service request.</p>

        <dl class="mt-5 divide-y divide-teal-100 text-sm">
            <div class="py-4 first:pt-0">
                <dt class="font-medium text-teal-900/70">Quotation</dt>
                <dd class="mt-2 font-semibold text-slate-900">
                    @if ($booking->quotation)
                    <a
                        href="{{ route('admin.quotations.show', $booking->quotation) }}"
                        class="text-teal-700 transition hover:text-teal-900 focus:outline-none focus:ring-2 focus:ring-teal-600 focus:ring-offset-2">
                        {{ $booking->quotation->quotation_no }}
                    </a>
                    <span class="ml-1 text-xs font-medium text-slate-500">{{ strtoupper($booking->quotation->status->value) }}</span>
                    @elseif ($booking->status === \App\Enums\BookingStatus::Pending)
                    <a
                        href="{{ route('admin.quotations.create', ['booking' => $booking->id]) }}"
                        class="text-teal-700 transition hover:text-teal-900 focus:outline-none focus:ring-2 focus:ring-teal-600 focus:ring-offset-2">
                        Create quotation
                    </a>
                    @else
                    <span class="text-slate-600">Not sent</span>
                    @endif
                </dd>
            </div>

            <div class="py-4">
                <dt class="font-medium text-teal-900/70">Assigned staff</dt>
                <dd class="mt-2 font-semibold text-slate-900">
                    {{ $booking->latestAssignment?->staffProfile?->user?->name ?? 'Not assigned' }}
                </dd>
            </div>

            <div class="pb-0 pt-4">
                <dt class="font-medium text-teal-900/70">Invoice</dt>
                <dd class="mt-2 font-semibold text-slate-900">
                    @if ($booking->invoice)
                    <a
                        href="{{ route('admin.invoices.show', $booking->invoice) }}"
                        class="text-teal-700 transition hover:text-teal-900 focus:outline-none focus:ring-2 focus:ring-teal-600 focus:ring-offset-2">
                        {{ $booking->invoice->invoice_no }}
                    </a>
                    <span class="ml-1 text-xs font-medium text-slate-500">{{ strtoupper($booking->invoice->payment_status->value) }}</span>
                    @elseif ($booking->status === \App\Enums\BookingStatus::Completed)
                    <a
                        href="{{ route('admin.invoices.create', ['booking' => $booking->id]) }}"
                        class="text-teal-700 transition hover:text-teal-900 focus:outline-none focus:ring-2 focus:ring-teal-600 focus:ring-offset-2">
                        Create invoice
                    </a>
                    @else
                    <span class="text-slate-600">Not issued</span>
                    @endif
                </dd>
            </div>
        </dl>
    </section>
</div>

<div class="mt-6 grid gap-6 xl:grid-cols-2">
    <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-[0_1px_2px_rgba(15,60,58,0.05)] sm:p-6">
        <h2 class="text-lg font-semibold tracking-tight text-slate-950">Booking conversation</h2>
        <p class="mt-1 text-sm text-slate-500">Customer and assigned staff messages, including private attachments.</p>
        @forelse ($booking->messages as $message)
        <div class="mt-4 rounded-lg border border-slate-100 bg-slate-50 p-4">
            <p class="text-sm font-semibold text-slate-900">{{ $message->sender?->name }} <span class="font-normal text-slate-500">· {{ ucfirst($message->sender?->role ?? 'user') }}</span></p>
            @if ($message->body)<p class="mt-2 whitespace-pre-line text-sm text-slate-700">{{ $message->body }}</p>@endif
            @if ($message->attachment_path)<a class="mt-2 inline-flex text-sm font-semibold text-teal-700 hover:text-teal-900" href="{{ route('admin.bookings.messages.attachment', [$booking, $message]) }}" target="_blank">Open attachment: {{ $message->attachment_original_name }}</a>@endif
            <p class="mt-2 text-xs text-slate-400">{{ $message->created_at?->format('d M Y, h:i A') }}</p>
        </div>
        @empty
        <p class="mt-5 text-sm text-slate-500">No messages have been sent for this booking.</p>
        @endforelse
    </section>

    <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-[0_1px_2px_rgba(15,60,58,0.05)] sm:p-6">
        <h2 class="text-lg font-semibold tracking-tight text-slate-950">Before &amp; after proof</h2>
        <p class="mt-1 text-sm text-slate-500">Photos submitted by the assigned staff while work is in progress.</p>
        <div class="mt-5 grid gap-4 sm:grid-cols-2">
            @forelse ($booking->serviceProofs as $proof)
            <a href="{{ route('admin.bookings.service-proofs.file', [$booking, $proof]) }}" target="_blank" class="overflow-hidden rounded-lg border border-slate-200 bg-slate-50 transition hover:border-teal-300">
                <img src="{{ route('admin.bookings.service-proofs.file', [$booking, $proof]) }}" alt="{{ ucfirst($proof->kind) }} service proof" class="h-36 w-full object-cover">
                <div class="p-3"><p class="text-sm font-semibold text-slate-900">{{ strtoupper($proof->kind) }}</p><p class="mt-1 text-xs text-slate-500">{{ $proof->staffProfile?->user?->name }} · {{ $proof->captured_at?->format('d M, h:i A') }}</p></div>
            </a>
            @empty
            <p class="text-sm text-slate-500 sm:col-span-2">No service photos have been uploaded yet.</p>
            @endforelse
        </div>
    </section>
</div>

<section class="mt-6 rounded-xl border border-slate-200 bg-white p-5 shadow-[0_1px_2px_rgba(15,60,58,0.05)] sm:p-6">
    <div class="flex items-start justify-between gap-4">
        <div>
            <h2 class="text-lg font-semibold tracking-tight text-slate-950">Service timeline</h2>
            <p class="mt-1 text-sm text-slate-500">A permanent record of customer, staff, and system workflow updates.</p>
        </div>
        <span class="rounded-full bg-teal-50 px-3 py-1 text-xs font-semibold text-teal-800">{{ $booking->timelineEvents->count() }} events</span>
    </div>

    @if ($booking->timelineEvents->isEmpty())
    <p class="mt-5 rounded-lg border border-dashed border-slate-200 bg-slate-50 px-4 py-5 text-sm text-slate-500">Timeline events will appear as this booking progresses.</p>
    @else
    <ol class="mt-6 space-y-5 border-l-2 border-teal-100 pl-5">
        @foreach ($booking->timelineEvents as $event)
        <li class="relative">
            <span class="absolute -left-[1.78rem] top-1.5 h-3 w-3 rounded-full border-2 border-white bg-teal-600 shadow-sm"></span>
            <p class="font-semibold text-slate-900">{{ $event->title }}</p>
            @if ($event->description)
            <p class="mt-1 text-sm leading-6 text-slate-600">{{ $event->description }}</p>
            @endif
            <p class="mt-1.5 text-xs font-medium text-slate-400">
                {{ $event->occurred_at?->format('d M Y, h:i A') }}
                @if ($event->actor)
                <span class="text-slate-500">· {{ $event->actor->name }} ({{ ucfirst($event->actor->role) }})</span>
                @endif
            </p>
        </li>
        @endforeach
    </ol>
    @endif
</section>

@if ($booking->cancellation_reason || $booking->rejection_reason)
<section class="mt-6 rounded-xl border border-slate-200 bg-white p-5 shadow-[0_1px_2px_rgba(15,60,58,0.05)] sm:p-6">
    <h2 class="text-lg font-semibold tracking-tight text-slate-950">Closure record</h2>

    @if ($booking->cancellation_reason)
    <p class="mt-3 text-sm leading-6 text-slate-700">
        <span class="font-semibold text-slate-900">Cancelled:</span>
        {{ $booking->cancellation_reason }}
        @if ($booking->cancelledBy)
        by {{ $booking->cancelledBy->name }}
        @endif
    </p>
    @endif

    @if ($booking->rejection_reason)
    <p class="mt-3 text-sm leading-6 text-slate-700">
        <span class="font-semibold text-slate-900">Rejected:</span>
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
<section class="mt-6 rounded-xl border border-amber-200 bg-amber-50 p-5 sm:p-6">
    <h2 class="text-lg font-semibold tracking-tight text-amber-950">Booking actions</h2>
    <p class="mt-1 text-sm leading-6 text-amber-900">
        These actions are permanent and preserve an audit record instead of deleting the booking.
    </p>

    <div class="mt-5 grid gap-5 lg:grid-cols-2">
        @if ($booking->status === \App\Enums\BookingStatus::Pending)
        <form method="POST" action="{{ route('admin.bookings.reject', $booking) }}" class="rounded-lg border border-red-200 bg-white p-5">
            @csrf
            @method('PATCH')
            <h3 class="font-semibold text-red-800">Reject booking</h3>
            <p class="mt-1 text-sm text-slate-600">Use when the request cannot be fulfilled.</p>
            <label class="mt-4 block">
                <span class="mb-2 block text-sm font-medium text-slate-700">Reason</span>
                <textarea name="reason" rows="3" required class="w-full rounded-lg border-slate-300 px-4 py-3 text-sm focus:border-red-500 focus:ring-red-500">{{ old('reason') }}</textarea>
            </label>
            <button type="submit" class="mt-4 rounded-lg bg-red-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-red-800 focus:outline-none focus:ring-2 focus:ring-red-600 focus:ring-offset-2 active:translate-y-px">
                Reject booking
            </button>
        </form>
        @endif

        <form method="POST" action="{{ route('admin.bookings.cancel', $booking) }}" class="rounded-lg border border-slate-300 bg-white p-5">
            @csrf
            @method('PATCH')
            <h3 class="font-semibold text-slate-900">Cancel booking</h3>
            <p class="mt-1 text-sm text-slate-600">Use for an approved booking that must no longer proceed.</p>
            <label class="mt-4 block">
                <span class="mb-2 block text-sm font-medium text-slate-700">Reason</span>
                <textarea name="reason" rows="3" required class="w-full rounded-lg border-slate-300 px-4 py-3 text-sm focus:border-slate-500 focus:ring-slate-500">{{ old('reason') }}</textarea>
            </label>
            <button type="submit" class="mt-4 rounded-lg bg-slate-800 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-950 focus:outline-none focus:ring-2 focus:ring-slate-700 focus:ring-offset-2 active:translate-y-px">
                Cancel booking
            </button>
        </form>
    </div>
</section>
@endif
@endsection
