<header class="sticky top-0 z-20 flex h-[72px] items-center justify-between border-b border-slate-200/90 bg-slate-50/95 px-5 backdrop-blur sm:px-6 lg:px-8">
    <div class="flex min-w-0 items-center gap-3">
        <button
            type="button"
            data-sidebar-toggle
            class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 shadow-sm transition hover:border-slate-400 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-teal-600 focus:ring-offset-2 lg:hidden"
            aria-controls="admin-sidebar"
            aria-expanded="false">
            Menu
        </button>
        <div class="min-w-0">
            <p class="truncate text-[15px] font-semibold tracking-tight text-slate-950">
                @yield('page_title', 'Dashboard')
            </p>
            <p class="hidden text-xs text-slate-500 sm:block">ServiceHub administration</p>
        </div>
    </div>

    <div class="flex items-center gap-3 sm:gap-4">
        <div class="hidden text-right sm:block">
            <p class="text-sm font-medium text-slate-800">{{ auth()->user()->name }}</p>
            <p class="max-w-48 truncate text-xs text-slate-500">{{ auth()->user()->email }}</p>
        </div>

        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-teal-100 text-sm font-semibold text-teal-800" aria-hidden="true">
            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
        </span>

        <form method="POST" action="{{ route('admin.logout') }}">
            @csrf
            <button
                type="submit"
                class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 shadow-sm transition hover:border-slate-400 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-teal-600 focus:ring-offset-2">
                Sign out
            </button>
        </form>
    </div>
</header>