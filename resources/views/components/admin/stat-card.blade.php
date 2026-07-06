<div class="rounded-2xl bg-white p-6 border border-slate-200 shadow-sm">
    <p class="text-sm font-medium text-slate-500">
        {{ $title }}
    </p>

    <div class="mt-3 text-3xl font-bold text-slate-900">
        {{ $value }}
    </div>

    @if ($description)
        <p class="mt-2 text-sm text-slate-500">
            {{ $description }}
        </p>
    @endif
</div>