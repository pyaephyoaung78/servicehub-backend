@extends('admin.layouts.app')

@section('title', 'Staff')
@section('page_title', 'Staff')

@section('content')
    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Staff Management</h1>
            <p class="mt-1 text-slate-500">
                Manage staff accounts, skills, employment status, and availability.
            </p>
        </div>

        <a
            href="{{ route('admin.staff.create') }}"
            class="w-fit rounded-xl bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700"
        >
            Add staff
        </a>
    </div>

    @if (session('success'))
        <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    <form method="GET" class="mb-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="grid gap-4 md:grid-cols-3">
            <label class="block">
                <span class="mb-1 block text-sm font-medium text-slate-700">Search</span>
                <input
                    type="search"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Name, email or phone"
                    class="w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500"
                >
            </label>

            <label class="block">
                <span class="mb-1 block text-sm font-medium text-slate-700">Employment status</span>
                <select name="is_active" class="w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">All staff</option>
                    <option value="1" @selected(request('is_active') === '1')>Active</option>
                    <option value="0" @selected(request('is_active') === '0')>Inactive</option>
                </select>
            </label>

            <label class="block">
                <span class="mb-1 block text-sm font-medium text-slate-700">Availability</span>
                <select name="is_available" class="w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">All availability</option>
                    <option value="1" @selected(request('is_available') === '1')>Available</option>
                    <option value="0" @selected(request('is_available') === '0')>Unavailable</option>
                </select>
            </label>
        </div>

        <div class="mt-5 flex gap-3">
            <button type="submit" class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">
                Apply filters
            </button>
            <a href="{{ route('admin.staff.index') }}" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                Reset
            </a>
        </div>
    </form>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 px-6 py-4 text-sm text-slate-500">
            {{ $staff->total() }} staff member{{ $staff->total() === 1 ? '' : 's' }} found
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-6 py-3">Staff member</th>
                        <th class="px-6 py-3">Phone</th>
                        <th class="px-6 py-3">Skills</th>
                        <th class="px-6 py-3">Employment</th>
                        <th class="px-6 py-3">Availability</th>
                        <th class="px-6 py-3"></th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                    @forelse ($staff as $staffProfile)
                        <tr class="hover:bg-slate-50/80">
                            <td class="px-6 py-4">
                                <p class="font-medium text-slate-900">{{ $staffProfile->user?->name }}</p>
                                <p class="text-xs text-slate-500">{{ $staffProfile->user?->email }}</p>
                            </td>
                            <td class="px-6 py-4 text-slate-600">{{ $staffProfile->phone }}</td>
                            <td class="px-6 py-4">
                                <div class="flex max-w-sm flex-wrap gap-1">
                                    @forelse ($staffProfile->services as $service)
                                        <span class="rounded-full bg-blue-50 px-2.5 py-1 text-xs font-medium text-blue-700">
                                            {{ $service->name }}
                                        </span>
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
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('admin.staff.show', $staffProfile) }}" class="font-medium text-blue-700 hover:text-blue-900">
                                    View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                                No staff members match the current filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($staff->hasPages())
            <div class="border-t border-slate-200 px-6 py-4">
                {{ $staff->links() }}
            </div>
        @endif
    </div>
@endsection
