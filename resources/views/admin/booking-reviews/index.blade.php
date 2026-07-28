@extends('admin.layouts.app')

@section('title', 'Customer Reviews')
@section('page_title', 'Customer Reviews')

@section('content')
<section class="mb-8 overflow-hidden rounded-xl bg-teal-950 px-5 py-6 shadow-[0_14px_32px_rgba(15,60,58,0.14)] sm:px-7 sm:py-7">
    <p class="text-sm font-medium text-teal-300">Customer experience</p>
    <h1 class="mt-2 text-2xl font-semibold tracking-tight text-white sm:text-3xl">Review moderation</h1>
    <p class="mt-2 max-w-2xl text-sm leading-6 text-teal-50/85">Approve genuine feedback for your service record, or hide content that should not be shown to customers.</p>
</section>

<form method="GET" class="mb-6 rounded-xl border border-teal-100 bg-teal-50/40 p-5 shadow-[0_1px_2px_rgba(15,60,58,0.06)] sm:p-6">
    <div class="mb-6">
        <h2 class="text-base font-semibold text-teal-950">Find reviews</h2>
        <p class="mt-1 text-sm text-teal-900/70">Search feedback, customers, or services and focus on the moderation queue.</p>
    </div>
    <div class="grid gap-5 md:grid-cols-3">
        <label class="block md:col-span-1">
            <span class="mb-2 block text-sm font-medium text-teal-950">Search</span>
            <input type="search" name="search" value="{{ request('search') }}" placeholder="Customer, service or feedback" class="h-11 w-full rounded-lg border-teal-200 bg-white px-4 text-sm placeholder:text-teal-900/40 focus:border-teal-600 focus:ring-teal-600">
        </label>
        <label class="block">
            <span class="mb-2 block text-sm font-medium text-teal-950">Moderation status</span>
            <select name="status" class="h-11 w-full rounded-lg border-teal-200 bg-white px-4 text-sm focus:border-teal-600 focus:ring-teal-600">
                <option value="">All statuses</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
        </label>
        <label class="block">
            <span class="mb-2 block text-sm font-medium text-teal-950">Rating</span>
            <select name="rating" class="h-11 w-full rounded-lg border-teal-200 bg-white px-4 text-sm focus:border-teal-600 focus:ring-teal-600">
                <option value="">All ratings</option>
                @for ($rating = 5; $rating >= 1; $rating--)
                    <option value="{{ $rating }}" @selected((string) request('rating') === (string) $rating)>{{ $rating }} star{{ $rating === 1 ? '' : 's' }}</option>
                @endfor
            </select>
        </label>
    </div>
    <div class="mt-6 flex flex-col gap-3 border-t border-teal-100 pt-5 sm:flex-row sm:items-center">
        <button type="submit" class="w-full rounded-lg bg-teal-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-teal-800 focus:outline-none focus:ring-2 focus:ring-teal-600 focus:ring-offset-2 active:translate-y-px sm:w-auto">Apply filters</button>
        <a href="{{ route('admin.booking-reviews.index') }}" class="w-full rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-center text-sm font-semibold text-slate-700 shadow-sm transition hover:border-slate-400 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-teal-600 focus:ring-offset-2 sm:w-auto">Reset</a>
    </div>
</form>

<div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-[0_1px_2px_rgba(15,60,58,0.05)]">
    <div class="border-b border-slate-200 bg-white px-5 py-4 text-sm font-medium text-slate-600 sm:px-6">{{ $reviews->total() }} review{{ $reviews->total() === 1 ? '' : 's' }} found</div>
    <div class="overflow-x-auto">
        <table class="w-full min-w-[1120px] text-sm">
            <thead class="bg-teal-950 text-left text-xs font-semibold tracking-wide text-teal-50">
                <tr>
                    <th class="px-6 py-3.5">Submitted</th>
                    <th class="px-6 py-3.5">Customer</th>
                    <th class="px-6 py-3.5">Service & staff</th>
                    <th class="px-6 py-3.5">Feedback</th>
                    <th class="px-6 py-3.5">Status</th>
                    <th class="px-6 py-3.5 text-center">Moderate</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($reviews as $review)
                    @php($statusClass = match ($review->status) { 'approved' => 'bg-emerald-50 text-emerald-700', 'hidden' => 'bg-slate-100 text-slate-600', default => 'bg-amber-50 text-amber-700' })
                    <tr class="align-top transition hover:bg-teal-50/60">
                        <td class="whitespace-nowrap px-6 py-4 text-slate-600">{{ $review->created_at?->format('d M Y, h:i A') }}</td>
                        <td class="px-6 py-4">
                            <p class="font-semibold text-slate-900">{{ $review->customer?->name }}</p>
                            <p class="text-xs text-slate-500">{{ $review->customer?->email }}</p>
                        </td>
                        <td class="px-6 py-4">
                            <a href="{{ route('admin.bookings.show', $review->booking) }}" class="font-semibold text-teal-700 transition hover:text-teal-900 focus:outline-none focus:ring-2 focus:ring-teal-600 focus:ring-offset-2">{{ $review->service?->name }}</a>
                            <p class="mt-1 text-xs text-slate-500">{{ $review->staffProfile?->user?->name ? 'Staff: '.$review->staffProfile->user->name : 'No staff assignment recorded' }}</p>
                        </td>
                        <td class="max-w-sm px-6 py-4">
                            <p class="font-semibold text-amber-600" aria-label="{{ $review->rating }} out of 5 stars">{{ str_repeat('★', $review->rating) }}<span class="text-slate-300">{{ str_repeat('★', 5 - $review->rating) }}</span></p>
                            <p class="mt-1.5 text-sm leading-6 text-slate-600">{{ $review->comment ?: 'No written feedback provided.' }}</p>
                        </td>
                        <td class="px-6 py-4">
                            <span class="rounded-full px-3 py-1 text-xs font-medium {{ $statusClass }}">{{ strtoupper($review->status) }}</span>
                            @if ($review->reviewedBy)
                                <p class="mt-2 text-xs text-slate-500">By {{ $review->reviewedBy->name }}</p>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if ($review->status === 'pending')
                                <div class="flex justify-center gap-2">
                                    <form method="POST" action="{{ route('admin.booking-reviews.moderate', $review) }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="approved">
                                        <button type="submit" class="rounded-lg bg-emerald-600 px-3 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2">Approve</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.booking-reviews.moderate', $review) }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="hidden">
                                        <button type="submit" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-700 shadow-sm transition hover:border-slate-400 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-teal-600 focus:ring-offset-2">Hide</button>
                                    </form>
                                </div>
                            @else
                                <span class="text-xs text-slate-500">Reviewed {{ $review->reviewed_at?->format('d M Y') }}</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-6 py-12 text-center text-slate-500">No reviews match the current filters.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($reviews->hasPages())
        <div class="border-t border-slate-200 px-6 py-4">{{ $reviews->links() }}</div>
    @endif
</div>
@endsection
