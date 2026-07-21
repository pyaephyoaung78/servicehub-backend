@extends('admin.layouts.app')

@section('title', $staffProfile->user?->name ?? 'Staff')
@section('page_title', 'Staff Profile')

@section('content')
    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <a href="{{ route('admin.staff.index') }}" class="text-sm font-medium text-blue-700 hover:text-blue-900">
                ← Back to staff
            </a>
            <h1 class="mt-2 text-2xl font-bold text-slate-900">{{ $staffProfile->user?->name }}</h1>
            <p class="mt-1 text-slate-500">{{ $staffProfile->user?->email }}</p>
        </div>

        <div class="flex flex-wrap gap-2">
            <span class="rounded-full px-4 py-2 text-sm font-semibold {{ $staffProfile->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-700' }}">
                {{ $staffProfile->is_active ? 'ACTIVE' : 'INACTIVE' }}
            </span>
            <span class="rounded-full px-4 py-2 text-sm font-semibold {{ $staffProfile->is_available ? 'bg-blue-100 text-blue-800' : 'bg-amber-100 text-amber-800' }}">
                {{ $staffProfile->is_available ? 'AVAILABLE' : 'UNAVAILABLE' }}
            </span>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid gap-6 xl:grid-cols-3">
        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm xl:col-span-2">
            <h2 class="text-lg font-semibold text-slate-900">Profile information</h2>
            <dl class="mt-5 grid gap-5 sm:grid-cols-2">
                <div>
                    <dt class="text-sm text-slate-500">Phone</dt>
                    <dd class="mt-1 font-medium text-slate-900">{{ $staffProfile->phone }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-slate-500">Joined</dt>
                    <dd class="mt-1 font-medium text-slate-900">{{ $staffProfile->created_at?->format('d M Y') }}</dd>
                </div>
                @if ($staffProfile->bio)
                    <div class="sm:col-span-2">
                        <dt class="text-sm text-slate-500">Bio</dt>
                        <dd class="mt-1 whitespace-pre-line text-slate-800">{{ $staffProfile->bio }}</dd>
                    </div>
                @endif
            </dl>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">Service skills</h2>
            <div class="mt-4 space-y-2">
                @forelse ($staffProfile->services as $service)
                    <div class="rounded-xl bg-slate-50 px-4 py-3">
                        <p class="font-medium text-slate-900">{{ $service->name }}</p>
                        <p class="text-xs text-slate-500">{{ $service->category?->name }}</p>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">No service skills assigned.</p>
                @endforelse
            </div>
        </section>
    </div>

    <section class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">Recent assignments</h2>
                <p class="mt-1 text-sm text-slate-500">Historical assignments are preserved when an account is deactivated.</p>
            </div>
            <a href="{{ route('admin.staff.edit', $staffProfile) }}" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                Edit profile
            </a>
        </div>

        <div class="mt-5 overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="border-b border-slate-200 text-left text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-3 py-3">Booking</th>
                        <th class="px-3 py-3">Service</th>
                        <th class="px-3 py-3">Assignment status</th>
                        <th class="px-3 py-3">Scheduled</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($staffProfile->bookingAssignments->sortByDesc('assigned_at')->take(10) as $assignment)
                        <tr>
                            <td class="px-3 py-3">#{{ $assignment->booking_id }}</td>
                            <td class="px-3 py-3">{{ $assignment->booking?->service_name }}</td>
                            <td class="px-3 py-3">{{ strtoupper($assignment->status->value) }}</td>
                            <td class="px-3 py-3 text-slate-600">{{ $assignment->booking?->scheduled_at?->format('d M Y, h:i A') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-3 py-8 text-center text-slate-500">No assignments yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    @if ($staffProfile->is_active)
        <section class="mt-6 rounded-2xl border border-amber-200 bg-amber-50 p-6">
            <h2 class="font-semibold text-amber-950">Deactivate account</h2>
            <p class="mt-1 text-sm text-amber-900">This disables the account and availability while preserving assignments and history.</p>
            <form method="POST" action="{{ route('admin.staff.destroy', $staffProfile) }}" class="mt-4" onsubmit="return confirm('Deactivate this staff account?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="rounded-xl bg-amber-700 px-4 py-2 text-sm font-medium text-white hover:bg-amber-800">
                    Deactivate staff
                </button>
            </form>
        </section>
    @endif
@endsection
