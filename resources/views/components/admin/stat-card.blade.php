@props([
'title',
'value',
'description' => null,
'href' => null,
'actionLabel' => 'Open',
])

<article class="rounded-xl border border-slate-200 bg-white p-5 shadow-[0_1px_2px_rgba(15,23,42,0.04)] transition hover:border-slate-300 hover:shadow-[0_8px_24px_rgba(15,23,42,0.06)]">
    <p class="text-sm font-medium text-slate-600">{{ $title }}</p>

    <p class="mt-3 text-3xl font-semibold tracking-tight text-slate-950">{{ $value }}</p>

    <div class="mt-4 flex items-end justify-between gap-3">
        @if ($description)
        <p class="text-sm leading-5 text-slate-500">{{ $description }}</p>
        @endif

        @if ($href)
        <a
            href="{{ $href }}"
            class="shrink-0 text-sm font-semibold text-teal-700 transition hover:text-teal-900 focus:outline-none focus:ring-2 focus:ring-teal-600 focus:ring-offset-2">
            {{ $actionLabel }}
        </a>
        @endif
    </div>
</article>