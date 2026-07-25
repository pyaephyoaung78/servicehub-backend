@php
$selectedServiceIds = old(
'service_ids',
$staffProfile?->services?->pluck('id')->all() ?? []
);
$active = old('is_active', $staffProfile?->is_active ?? true);
$available = old('is_available', $staffProfile?->is_available ?? true);
@endphp

@if ($errors->any())
<div class="mb-6 max-w-4xl rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
    <p class="font-semibold">Please correct the following staff details.</p>
    <ul class="mt-1 list-inside list-disc">
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<form method="POST" action="{{ $formAction }}" class="max-w-4xl overflow-hidden rounded-xl border border-slate-200 bg-white shadow-[0_1px_2px_rgba(15,60,58,0.05)]">
    @csrf
    @if ($formMethod !== 'POST')
    @method($formMethod)
    @endif

    <div class="border-b border-slate-200 px-5 py-5 sm:px-6">
        <h2 class="text-lg font-semibold tracking-tight text-slate-950">Account and contact details</h2>
        <p class="mt-1 text-sm text-slate-500">Use an email address the staff member can access for their login.</p>
    </div>

    <div class="space-y-6 p-5 sm:p-6">
        <div class="grid gap-5 sm:grid-cols-2">
            <label class="block">
                <span class="mb-2 block text-sm font-medium text-slate-800">Name</span>
                <input type="text" name="name" value="{{ old('name', $staffProfile?->user?->name) }}" required class="h-11 w-full rounded-lg border-teal-200 bg-white px-4 text-sm focus:border-teal-600 focus:ring-teal-600">
            </label>

            <label class="block">
                <span class="mb-2 block text-sm font-medium text-slate-800">Email</span>
                <input type="email" name="email" value="{{ old('email', $staffProfile?->user?->email) }}" required class="h-11 w-full rounded-lg border-teal-200 bg-white px-4 text-sm focus:border-teal-600 focus:ring-teal-600">
            </label>

            @unless ($isEditing)
            <label class="block">
                <span class="mb-2 block text-sm font-medium text-slate-800">Temporary password</span>
                <input type="password" name="password" required minlength="8" class="h-11 w-full rounded-lg border-teal-200 bg-white px-4 text-sm focus:border-teal-600 focus:ring-teal-600">
            </label>
            @endunless

            <label class="block">
                <span class="mb-2 block text-sm font-medium text-slate-800">Phone</span>
                <input type="text" name="phone" value="{{ old('phone', $staffProfile?->phone) }}" required class="h-11 w-full rounded-lg border-teal-200 bg-white px-4 text-sm focus:border-teal-600 focus:ring-teal-600">
            </label>
        </div>

        <label class="block">
            <span class="mb-2 block text-sm font-medium text-slate-800">Bio</span>
            <textarea name="bio" rows="4" class="w-full rounded-lg border-teal-200 bg-white px-4 py-3 text-sm focus:border-teal-600 focus:ring-teal-600">{{ old('bio', $staffProfile?->bio) }}</textarea>
        </label>

        <section class="rounded-xl border border-teal-100 bg-teal-50/40 p-5">
            <h3 class="font-semibold text-teal-950">Account status</h3>
            <p class="mt-1 text-sm text-teal-900/70">Control whether this staff member can access their account and receive new assignments.</p>

            <div class="mt-5 grid gap-4 sm:grid-cols-2">
                <label class="flex items-start gap-3 rounded-lg border border-teal-100 bg-white p-4 transition hover:border-teal-200">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" @checked(filter_var($active, FILTER_VALIDATE_BOOLEAN)) class="mt-0.5 rounded border-teal-300 text-teal-700 focus:ring-teal-600">
                    <span>
                        <span class="block text-sm font-semibold text-slate-800">Active account</span>
                        <span class="mt-1 block text-xs leading-5 text-slate-500">Inactive staff cannot respond to assignments.</span>
                    </span>
                </label>

                <label class="flex items-start gap-3 rounded-lg border border-teal-100 bg-white p-4 transition hover:border-teal-200">
                    <input type="hidden" name="is_available" value="0">
                    <input type="checkbox" name="is_available" value="1" @checked(filter_var($available, FILTER_VALIDATE_BOOLEAN)) class="mt-0.5 rounded border-teal-300 text-teal-700 focus:ring-teal-600">
                    <span>
                        <span class="block text-sm font-semibold text-slate-800">Available for assignments</span>
                        <span class="mt-1 block text-xs leading-5 text-slate-500">Available staff appear in assignment selection.</span>
                    </span>
                </label>
            </div>
        </section>

        <fieldset>
            <legend class="text-base font-semibold text-slate-950">Service skills</legend>
            <p class="mt-1 text-sm text-slate-500">Select at least one service this staff member can perform.</p>

            <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($services as $service)
                <label class="flex items-start gap-3 rounded-lg border border-slate-200 bg-white p-4 transition hover:border-teal-200 hover:bg-teal-50/40">
                    <input
                        type="checkbox"
                        name="service_ids[]"
                        value="{{ $service->id }}"
                        @checked(in_array($service->id, $selectedServiceIds))
                    class="mt-0.5 rounded border-teal-300 text-teal-700 focus:ring-teal-600"
                    >
                    <span>
                        <span class="block text-sm font-semibold text-slate-800">{{ $service->name }}</span>
                        <span class="mt-1 block text-xs text-slate-500">{{ $service->category?->name }}</span>
                    </span>
                </label>
                @endforeach
            </div>
        </fieldset>
    </div>

    <div class="flex flex-col gap-3 border-t border-slate-200 bg-slate-50 px-5 py-5 sm:flex-row sm:items-center sm:px-6">
        <button type="submit" class="w-full rounded-lg bg-teal-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-teal-800 focus:outline-none focus:ring-2 focus:ring-teal-600 focus:ring-offset-2 active:translate-y-px sm:w-auto">
            {{ $isEditing ? 'Save changes' : 'Create staff account' }}
        </button>
        <a href="{{ $isEditing ? route('admin.staff.show', $staffProfile) : route('admin.staff.index') }}" class="w-full rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-center text-sm font-semibold text-slate-700 shadow-sm transition hover:border-slate-400 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-teal-600 focus:ring-offset-2 sm:w-auto">
            Cancel
        </a>
    </div>
</form>