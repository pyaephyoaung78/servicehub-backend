<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Dashboard') | ServiceHub</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-50 font-sans text-slate-900 antialiased">
    <div class="admin-shell min-h-screen lg:flex">
        <button
            type="button"
            data-sidebar-overlay
            class="fixed inset-0 z-30 hidden bg-slate-950/35 lg:hidden"
            aria-label="Close navigation menu"></button>

        @include('admin.partials.sidebar')

        <div class="min-w-0 flex-1">
            @include('admin.partials.topbar')

            <main class="mx-auto w-full max-w-[1600px] p-5 sm:p-6 lg:p-8">
                @yield('content')
            </main>
        </div>
    </div>
</body>

</html>