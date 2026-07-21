<aside class="hidden lg:flex lg:w-72 lg:flex-col bg-slate-950 text-white">
    <div class="h-16 flex items-center px-6 border-b border-white/10">
        <div>
            <h1 class="font-bold text-lg">
                ServiceHub
            </h1>
            <p class="text-xs text-slate-400">
                Admin Panel
            </p>
        </div>
    </div>

    <nav class="flex-1 px-4 py-5 space-y-1">
        <a
            href="{{ route('admin.dashboard') }}"
            @class([
                'flex items-center rounded-xl px-4 py-3 text-sm font-medium',
                'bg-white/10 text-white' => request()->routeIs('admin.dashboard'),
                'text-slate-300 hover:bg-white/10 hover:text-white' => ! request()->routeIs('admin.dashboard'),
            ])
        >
            Dashboard
        </a>

        <a
            href="{{ route('admin.bookings.index') }}"
            @class([
                'flex items-center rounded-xl px-4 py-3 text-sm font-medium',
                'bg-white/10 text-white' => request()->routeIs('admin.bookings.*'),
                'text-slate-300 hover:bg-white/10 hover:text-white' => ! request()->routeIs('admin.bookings.*'),
            ])
        >
            Bookings
        </a>

        <a
            href="{{ route('admin.quotations.index') }}"
            @class([
                'flex items-center rounded-xl px-4 py-3 text-sm font-medium',
                'bg-white/10 text-white' => request()->routeIs('admin.quotations.*'),
                'text-slate-300 hover:bg-white/10 hover:text-white' => ! request()->routeIs('admin.quotations.*'),
            ])
        >
            Quotations
        </a>

        <a
            href="{{ route('admin.staff.index') }}"
            @class([
                'flex items-center rounded-xl px-4 py-3 text-sm font-medium',
                'bg-white/10 text-white' => request()->routeIs('admin.staff.*'),
                'text-slate-300 hover:bg-white/10 hover:text-white' => ! request()->routeIs('admin.staff.*'),
            ])
        >
            Staff
        </a>

        <a
            href="#"
            class="flex items-center rounded-xl px-4 py-3 text-sm font-medium text-slate-300 hover:bg-white/10 hover:text-white"
        >
            Invoices
        </a>

        <a
            href="#"
            class="flex items-center rounded-xl px-4 py-3 text-sm font-medium text-slate-300 hover:bg-white/10 hover:text-white"
        >
            Payments
        </a>

        <a
            href="#"
            class="flex items-center rounded-xl px-4 py-3 text-sm font-medium text-slate-300 hover:bg-white/10 hover:text-white"
        >
            Reports
        </a>
    </nav>
</aside>
