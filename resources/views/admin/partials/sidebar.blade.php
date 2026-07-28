<aside
    id="admin-sidebar"
    class="fixed inset-y-0 left-0 z-40 flex w-72 -translate-x-full flex-col border-r border-slate-800 bg-slate-950 text-white shadow-2xl transition-transform duration-200 ease-out lg:sticky lg:top-0 lg:h-screen lg:translate-x-0 lg:shadow-none">
    <div class="flex h-[72px] items-center justify-between border-b border-slate-800 px-5">
        <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-400 focus:ring-offset-2 focus:ring-offset-slate-950">
            <span class="flex h-9 w-9 items-center justify-center overflow-hidden rounded-xl bg-slate-50" aria-hidden="true">
                <img src="{{ asset('images/servicehub-logo-mark-v1.png') }}" alt="" class="h-full w-full scale-[1.4] object-contain">
            </span>
            <span>
                <span class="block text-[15px] font-semibold tracking-tight text-white">ServiceHub</span>
                <span class="block text-xs text-slate-400">Operations</span>
            </span>
        </a>

        <button
            type="button"
            data-sidebar-close
            class="rounded-lg px-2.5 py-1.5 text-xs font-medium text-slate-300 transition hover:bg-slate-800 hover:text-white focus:outline-none focus:ring-2 focus:ring-teal-400 lg:hidden">
            Close
        </button>
    </div>

    <nav class="flex-1 overflow-y-auto px-3 py-5" aria-label="Admin navigation">
        <p class="mb-2 px-3 text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-500">Workspace</p>
        <a
            href="{{ route('admin.dashboard') }}"
            @class([ 'mb-1 flex items-center rounded-lg px-3 py-2.5 text-sm font-medium transition focus:outline-none focus:ring-2 focus:ring-teal-400' , 'bg-teal-600 text-white shadow-sm'=> request()->routeIs('admin.dashboard'),
            'text-slate-300 hover:bg-slate-800 hover:text-white' => ! request()->routeIs('admin.dashboard'),
            ])
            >
            Dashboard
        </a>

        <p class="mb-2 mt-6 px-3 text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-500">Service workflow</p>
        <a
            href="{{ route('admin.bookings.index') }}"
            @class([ 'mb-1 flex items-center rounded-lg px-3 py-2.5 text-sm font-medium transition focus:outline-none focus:ring-2 focus:ring-teal-400' , 'bg-teal-600 text-white shadow-sm'=> request()->routeIs('admin.bookings.*'),
            'text-slate-300 hover:bg-slate-800 hover:text-white' => ! request()->routeIs('admin.bookings.*'),
            ])
            >
            Bookings
        </a>

        <a
            href="{{ route('admin.quotations.index') }}"
            @class([ 'mb-1 flex items-center rounded-lg px-3 py-2.5 text-sm font-medium transition focus:outline-none focus:ring-2 focus:ring-teal-400' , 'bg-teal-600 text-white shadow-sm'=> request()->routeIs('admin.quotations.*'),
            'text-slate-300 hover:bg-slate-800 hover:text-white' => ! request()->routeIs('admin.quotations.*'),
            ])
            >
            Quotations
        </a>

        <a
            href="{{ route('admin.staff.index') }}"
            @class([ 'mb-1 flex items-center rounded-lg px-3 py-2.5 text-sm font-medium transition focus:outline-none focus:ring-2 focus:ring-teal-400' , 'bg-teal-600 text-white shadow-sm'=> request()->routeIs('admin.staff.*'),
            'text-slate-300 hover:bg-slate-800 hover:text-white' => ! request()->routeIs('admin.staff.*'),
            ])
            >
            Staff
        </a>

        <p class="mb-2 mt-6 px-3 text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-500">Finance</p>
        <a
            href="{{ route('admin.invoices.index') }}"
            @class([ 'mb-1 flex items-center rounded-lg px-3 py-2.5 text-sm font-medium transition focus:outline-none focus:ring-2 focus:ring-teal-400' , 'bg-teal-600 text-white shadow-sm'=> request()->routeIs('admin.invoices.*'),
            'text-slate-300 hover:bg-slate-800 hover:text-white' => ! request()->routeIs('admin.invoices.*'),
            ])
            >
            Invoices
        </a>

        <a
            href="{{ route('admin.payments.index') }}"
            @class([ 'mb-1 flex items-center rounded-lg px-3 py-2.5 text-sm font-medium transition focus:outline-none focus:ring-2 focus:ring-teal-400' , 'bg-teal-600 text-white shadow-sm'=> request()->routeIs('admin.payments.*'),
            'text-slate-300 hover:bg-slate-800 hover:text-white' => ! request()->routeIs('admin.payments.*'),
            ])
            >
            Payments
        </a>

        <a
            href="{{ route('admin.payment-proofs.index') }}"
            @class([ 'mb-1 flex items-center rounded-lg px-3 py-2.5 text-sm font-medium transition focus:outline-none focus:ring-2 focus:ring-teal-400' , 'bg-teal-600 text-white shadow-sm'=> request()->routeIs('admin.payment-proofs.*'),
            'text-slate-300 hover:bg-slate-800 hover:text-white' => ! request()->routeIs('admin.payment-proofs.*'),
            ])
            >
            Payment Proofs
        </a>

        <p class="mb-2 mt-6 px-3 text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-500">Customer experience</p>
        <a
            href="{{ route('admin.booking-reviews.index') }}"
            @class([ 'mb-1 flex items-center rounded-lg px-3 py-2.5 text-sm font-medium transition focus:outline-none focus:ring-2 focus:ring-teal-400' , 'bg-teal-600 text-white shadow-sm'=> request()->routeIs('admin.booking-reviews.*'),
            'text-slate-300 hover:bg-slate-800 hover:text-white' => ! request()->routeIs('admin.booking-reviews.*'),
            ])
            >
            Customer Reviews
        </a>

        <p class="mb-2 mt-6 px-3 text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-500">Insights</p>
        <a
            href="{{ route('admin.reports.index') }}"
            @class([ 'mb-1 flex items-center rounded-lg px-3 py-2.5 text-sm font-medium transition focus:outline-none focus:ring-2 focus:ring-teal-400' , 'bg-teal-600 text-white shadow-sm'=> request()->routeIs('admin.reports.*'),
            'text-slate-300 hover:bg-slate-800 hover:text-white' => ! request()->routeIs('admin.reports.*'),
            ])
            >
            Reports
        </a>
    </nav>

    <div class="border-t border-slate-800 px-5 py-4 text-xs leading-5 text-slate-500">
        Manage services, customers, and payments from one workspace.
    </div>
</aside>
