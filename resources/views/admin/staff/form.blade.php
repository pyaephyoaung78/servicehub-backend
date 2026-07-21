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
        <ul class="list-inside list-disc">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ $formAction }}" class="max-w-4xl rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
    @csrf
    @if ($formMethod !== 'POST')
        @method($formMethod)
    @endif

    <div class="grid gap-5 sm:grid-cols-2">
        <label class="block">
            <span class="mb-1 block text-sm font-medium text-slate-700">Name</span>
            <input type="text" name="name" value="{{ old('name', $staffProfile?->user?->name) }}" required class="w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">
        </label>

        <label class="block">
            <span class="mb-1 block text-sm font-medium text-slate-700">Email</span>
            <input type="email" name="email" value="{{ old('email', $staffProfile?->user?->email) }}" required class="w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">
        </label>

        @unless ($isEditing)
            <label class="block">
                <span class="mb-1 block text-sm font-medium text-slate-700">Temporary password</span>
                <input type="password" name="password" required minlength="8" class="w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">
            </label>
        @endunless

        <label class="block">
            <span class="mb-1 block text-sm font-medium text-slate-700">Phone</span>
            <input type="text" name="phone" value="{{ old('phone', $staffProfile?->phone) }}" required class="w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">
        </label>
    </div>

    <label class="mt-5 block">
        <span class="mb-1 block text-sm font-medium text-slate-700">Bio</span>
        <textarea name="bio" rows="4" class="w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">{{ old('bio', $staffProfile?->bio) }}</textarea>
    </label>

    <div class="mt-5 grid gap-4 sm:grid-cols-2">
        <label class="flex items-center gap-3 rounded-xl border border-slate-200 p-4">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1" @checked(filter_var($active, FILTER_VALIDATE_BOOLEAN)) class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
            <span>
                <span class="block text-sm font-medium text-slate-800">Active account</span>
                <span class="block text-xs text-slate-500">Inactive staff cannot respond to assignments.</span>
            </span>
        </label>

        <label class="flex items-center gap-3 rounded-xl border border-slate-200 p-4">
            <input type="hidden" name="is_available" value="0">
            <input type="checkbox" name="is_available" value="1" @checked(filter_var($available, FILTER_VALIDATE_BOOLEAN)) class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
            <span>
                <span class="block text-sm font-medium text-slate-800">Available for assignments</span>
                <span class="block text-xs text-slate-500">Available staff appear in assignment selection.</span>
            </span>
        </label>
    </div>

    <fieldset class="mt-6">
        <legend class="text-sm font-semibold text-slate-900">Service skills</legend>
        <p class="mt-1 text-xs text-slate-500">Select at least one service this staff member can perform.</p>

        <div class="mt-3 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($services as $service)
                <label class="flex items-start gap-3 rounded-xl border border-slate-200 p-3 hover:bg-slate-50">
                    <input
                        type="checkbox"
                        name="service_ids[]"
                        value="{{ $service->id }}"
                        @checked(in_array($service->id, $selectedServiceIds))
                        class="mt-1 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                    >
                    <span>
                        <span class="block text-sm font-medium text-slate-800">{{ $service->name }}</span>
                        <span class="block text-xs text-slate-500">{{ $service->category?->name }}</span>
                    </span>
                </label>
            @endforeach
        </div>
    </fieldset>

    <div class="mt-6 flex gap-3">
        <button type="submit" class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">
            {{ $isEditing ? 'Save changes' : 'Create staff account' }}
        </button>
        <a href="{{ $isEditing ? route('admin.staff.show', $staffProfile) : route('admin.staff.index') }}" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
            Cancel
        </a>
    </div>
</form>
