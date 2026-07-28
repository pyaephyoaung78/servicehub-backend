@extends('admin.layouts.app')

@section('title', $staffProfile->user?->name ?? 'Staff')
@section('page_title', 'Staff Profile')

@section('content')
<section class="mb-8 overflow-hidden rounded-xl bg-teal-950 px-5 py-6 shadow-[0_14px_32px_rgba(15,60,58,0.14)] sm:px-7 sm:py-7">
    <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <a href="{{ route('admin.staff.index') }}" class="inline-flex items-center gap-1.5 rounded-lg border border-teal-700 bg-teal-900/70 px-2.5 py-1.5 text-xs font-semibold text-teal-50 transition hover:bg-teal-800 focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-teal-950 active:translate-y-px">
                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                </svg>
                Back to staff
            </a>
            <p class="mt-5 text-sm font-medium text-teal-300">Staff profile</p>
            <h1 class="mt-2 text-2xl font-semibold tracking-tight text-white sm:text-3xl">{{ $staffProfile->user?->name }}</h1>
            <p class="mt-2 text-sm text-teal-50/85">{{ $staffProfile->user?->email }}</p>
        </div>

        <div class="flex flex-wrap gap-2">
            <span class="rounded-full border border-teal-700 bg-teal-900 px-3 py-1.5 text-xs font-semibold text-teal-50">{{ $staffProfile->is_active ? 'ACTIVE' : 'INACTIVE' }}</span>
            <span class="rounded-full border border-teal-700 bg-teal-900 px-3 py-1.5 text-xs font-semibold text-teal-50">{{ $staffProfile->is_available ? 'AVAILABLE' : 'UNAVAILABLE' }}</span>
        </div>
    </div>
</section>

@if (session('success'))
<div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>
@endif

<div class="grid gap-6 xl:grid-cols-3">
    <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-[0_1px_2px_rgba(15,60,58,0.05)] xl:col-span-2">
        <div class="border-b border-slate-200 px-5 py-5 sm:px-6">
            <h2 class="text-lg font-semibold tracking-tight text-slate-950">Profile information</h2>
            <p class="mt-1 text-sm text-slate-500">Contact details and workforce record.</p>
        </div>
        <dl class="grid gap-4 p-5 sm:grid-cols-2 sm:p-6">
            <div class="rounded-lg bg-teal-50/50 p-4">
                <dt class="text-sm font-medium text-teal-900/70">Phone</dt>
                <dd class="mt-2 font-semibold text-slate-900">{{ $staffProfile->phone }}</dd>
            </div>
            <div class="rounded-lg bg-teal-50/50 p-4">
                <dt class="text-sm font-medium text-teal-900/70">Joined</dt>
                <dd class="mt-2 font-semibold text-slate-900">{{ $staffProfile->created_at?->format('d M Y') }}</dd>
            </div>
            @if ($staffProfile->bio)
            <div class="rounded-lg bg-teal-50/50 p-4 sm:col-span-2">
                <dt class="text-sm font-medium text-teal-900/70">Bio</dt>
                <dd class="mt-2 whitespace-pre-line text-slate-800">{{ $staffProfile->bio }}</dd>
            </div>
            @endif
        </dl>
    </section>

    <section class="rounded-xl border border-teal-100 bg-teal-50/40 p-5 shadow-[0_1px_2px_rgba(15,60,58,0.05)] sm:p-6">
        <h2 class="text-lg font-semibold tracking-tight text-teal-950">Service skills</h2>
        <p class="mt-1 text-sm text-teal-900/70">Services this staff member can perform.</p>
        <div class="mt-5 space-y-2">
            @forelse ($staffProfile->services as $service)
            <div class="rounded-lg border border-teal-100 bg-white px-4 py-3">
                <p class="font-semibold text-slate-900">{{ $service->name }}</p>
                <p class="mt-1 text-xs text-slate-500">{{ $service->category?->name }}</p>
            </div>
            @empty
            <p class="text-sm text-slate-500">No service skills assigned.</p>
            @endforelse
        </div>
    </section>
</div>

<section class="mt-6 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-[0_1px_2px_rgba(15,60,58,0.05)]">
    <div class="flex flex-col gap-4 border-b border-slate-200 px-5 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-6">
        <div>
            <h2 class="text-lg font-semibold tracking-tight text-slate-950">Recent assignments</h2>
            <p class="mt-1 text-sm text-slate-500">Historical assignments remain available when an account is deactivated.</p>
        </div>
        <a href="{{ route('admin.staff.edit', $staffProfile) }}" class="w-fit rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-slate-400 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-teal-600 focus:ring-offset-2">
            Edit profile
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full min-w-[650px] text-sm">
            <thead class="bg-teal-950 text-left text-xs font-semibold tracking-wide text-teal-50">
                <tr>
                    <th class="px-5 py-3.5 sm:px-6">Booking</th>
                    <th class="px-5 py-3.5 sm:px-6">Service</th>
                    <th class="px-5 py-3.5 sm:px-6">Assignment status</th>
                    <th class="px-5 py-3.5 sm:px-6">Scheduled</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($staffProfile->bookingAssignments->sortByDesc('assigned_at')->take(10) as $assignment)
                <tr class="transition hover:bg-teal-50/60">
                    <td class="px-5 py-4 font-medium text-slate-900 sm:px-6">#{{ $assignment->booking_id }}</td>
                    <td class="px-5 py-4 text-slate-700 sm:px-6">{{ $assignment->booking?->service_name }}</td>
                    <td class="px-5 py-4 sm:px-6"><span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-700">{{ strtoupper($assignment->status->value) }}</span></td>
                    <td class="whitespace-nowrap px-5 py-4 text-slate-600 sm:px-6">{{ $assignment->booking?->scheduled_at?->format('d M Y, h:i A') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-6 py-10 text-center text-slate-500">No assignments yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>

@if ($staffProfile->is_active)
<section class="mt-6 rounded-xl border border-amber-200 bg-amber-50 p-5 sm:p-6">
    <h2 class="font-semibold text-amber-950">Deactivate account</h2>
    <p class="mt-1 text-sm leading-6 text-amber-900">This disables the account and availability while preserving assignments and history.</p>
    <form method="POST" action="{{ route('admin.staff.destroy', $staffProfile) }}" class="mt-4" onsubmit="return confirm('Deactivate this staff account?');">
        @csrf
        @method('DELETE')
        <button type="submit" class="rounded-lg bg-amber-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-amber-800 focus:outline-none focus:ring-2 focus:ring-amber-600 focus:ring-offset-2 active:translate-y-px">Deactivate staff</button>
    </form>
</section>
@endif
@endsection