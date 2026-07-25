@extends('admin.layouts.app')

@section('title', 'Staff')
@section('page_title', 'Staff')

@section('content')
<section class="mb-8 overflow-hidden rounded-xl bg-teal-950 px-5 py-6 shadow-[0_14px_32px_rgba(15,60,58,0.14)] sm:px-7 sm:py-7">
    <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm font-medium text-teal-300">Workforce operations</p>
            <h1 class="mt-2 text-2xl font-semibold tracking-tight text-white sm:text-3xl">Staff management</h1>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-teal-50/85">Manage staff accounts, skills, employment status, and availability.</p>
        </div>

        <a
            href="{{ route('admin.staff.create') }}"
            class="w-fit rounded-lg bg-white px-4 py-2.5 text-sm font-semibold text-teal-950 shadow-sm transition hover:bg-teal-50 focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-teal-950 active:translate-y-px">
            Add staff
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
        <h2 class="text-base font-semibold text-teal-950">Find staff</h2>
        <p class="mt-1 text-sm text-teal-900/70">Search by contact details or filter the workforce by current status.</p>
    </div>

    <div class="grid gap-5 md:grid-cols-2">
        <label class="block md:col-span-2">
            <span class="mb-2 block text-sm font-medium text-teal-950">Search</span>
            <input
                type="search"
                name="search"
                value="{{ request('search') }}"
                placeholder="Name, email or phone"
                class="h-11 w-full rounded-lg border-teal-200 bg-white px-4 text-sm placeholder:text-teal-900/40 focus:border-teal-600 focus:ring-teal-600">
        </label>

        <label class="block">
            <span class="mb-2 block text-sm font-medium text-teal-950">Employment status</span>
            <select name="is_active" class="h-11 w-full rounded-lg border-teal-200 bg-white px-4 text-sm focus:border-teal-600 focus:ring-teal-600">
                <option value="">All staff</option>
                <option value="1" @selected(request('is_active')==='1' )>Active</option>
                <option value="0" @selected(request('is_active')==='0' )>Inactive</option>
            </select>
        </label>

        <label class="block">
            <span class="mb-2 block text-sm font-medium text-teal-950">Availability</span>
            <select name="is_available" class="h-11 w-full rounded-lg border-teal-200 bg-white px-4 text-sm focus:border-teal-600 focus:ring-teal-600">
                <option value="">All availability</option>
                <option value="1" @selected(request('is_available')==='1' )>Available</option>
                <option value="0" @selected(request('is_available')==='0' )>Unavailable</option>
            </select>
        </label>
    </div>

    <div class="mt-6 flex flex-col gap-3 border-t border-teal-100 pt-5 sm:flex-row sm:items-center">
        <button type="submit" class="w-full rounded-lg bg-teal-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-teal-800 focus:outline-none focus:ring-2 focus:ring-teal-600 focus:ring-offset-2 active:translate-y-px sm:w-auto">
            Apply filters
        </button>
        <a href="{{ route('admin.staff.index') }}" class="w-full rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-center text-sm font-semibold text-slate-700 shadow-sm transition hover:border-slate-400 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-teal-600 focus:ring-offset-2 sm:w-auto">
            Reset
        </a>
    </div>
</form>

<div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-[0_1px_2px_rgba(15,60,58,0.05)]">
    <div class="border-b border-slate-200 bg-white px-5 py-4 text-sm font-medium text-slate-600 sm:px-6">
        {{ $staff->total() }} staff member{{ $staff->total() === 1 ? '' : 's' }} found
    </div>

    <div class="overflow-x-auto">
        <table class="w-full min-w-[850px] text-sm">
            <thead class="bg-teal-950 text-left text-xs font-semibold tracking-wide text-teal-50">
                <tr>
                    <th class="px-6 py-3.5">Staff member</th>
                    <th class="px-6 py-3.5">Phone</th>
                    <th class="px-6 py-3.5">Skills</th>
                    <th class="px-6 py-3.5">Employment</th>
                    <th class="px-6 py-3.5">Availability</th>
                    <th class="px-6 py-3.5 text-center">Actions</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-slate-100">
                @forelse ($staff as $staffProfile)
                <tr class="transition hover:bg-teal-50/60">
                    <td class="px-6 py-4">
                        <p class="font-semibold text-slate-900">{{ $staffProfile->user?->name }}</p>
                        <p class="text-xs text-slate-500">{{ $staffProfile->user?->email }}</p>
                    </td>
                    <td class="px-6 py-4 text-slate-600">{{ $staffProfile->phone }}</td>
                    <td class="px-6 py-4">
                        <div class="flex max-w-sm flex-wrap gap-1.5">
                            @forelse ($staffProfile->services as $service)
                            <span class="rounded-full bg-teal-50 px-2.5 py-1 text-xs font-medium text-teal-800">{{ $service->name }}</span>
                            @empty
                            <span class="text-slate-500">No skills</span>
                            @endforelse
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="rounded-full px-3 py-1 text-xs font-medium {{ $staffProfile->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                            {{ $staffProfile->is_active ? 'ACTIVE' : 'INACTIVE' }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="rounded-full px-3 py-1 text-xs font-medium {{ $staffProfile->is_available ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                            {{ $staffProfile->is_available ? 'AVAILABLE' : 'UNAVAILABLE' }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <a
                            href="{{ route('admin.staff.show', $staffProfile) }}"
                            class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-teal-700 transition hover:bg-teal-50 hover:text-teal-900 focus:outline-none focus:ring-2 focus:ring-teal-600 focus:ring-offset-2"
                            aria-label="Open staff profile for {{ $staffProfile->user?->name }}">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12S5.75 5.25 12 5.25 21.75 12 21.75 12 18.25 18.75 12 18.75 2.25 12 2.25 12Z" />
                                <circle cx="12" cy="12" r="2.75" />
                            </svg>
                            <span class="sr-only">Open staff profile</span>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-slate-500">No staff members match the current filters.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($staff->hasPages())
    <div class="border-t border-slate-200 px-6 py-4">{{ $staff->links() }}</div>
    @endif
</div>
@endsection