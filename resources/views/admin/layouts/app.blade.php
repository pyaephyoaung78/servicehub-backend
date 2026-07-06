<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Dashboard') | ServiceHub</title>

    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 text-slate-900">
    <div class="min-h-screen flex">
        @include('admin.partials.sidebar')

        <div class="flex-1 min-w-0">
            @include('admin.partials.topbar')

            <main class="p-6 lg:p-8">
                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>