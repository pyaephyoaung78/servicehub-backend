<header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-6 lg:px-8">
    <div>
        <h2 class="font-semibold text-slate-900">
            @yield('page_title', 'Dashboard')
        </h2>
        <p class="text-xs text-slate-500">
            Business management dashboard
        </p>
    </div>

    <div class="flex items-center gap-4">
        <div class="hidden sm:block text-right">
            <p class="text-sm font-medium text-slate-900">
                {{ auth()->user()->name }}
            </p>
            <p class="text-xs text-slate-500">
                {{ auth()->user()->email }}
            </p>
        </div>

        <form method="POST" action="{{ route('admin.logout') }}">
            @csrf
            <button
                type="submit"
                class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
            >
                Logout
            </button>
        </form>
    </div>
</header>