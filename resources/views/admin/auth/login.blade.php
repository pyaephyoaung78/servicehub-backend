<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | ServiceHub</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-w-[320px] bg-slate-50 font-sans text-slate-900 antialiased">
    <main class="grid min-h-[100dvh] lg:grid-cols-2">
        <section class="relative hidden overflow-hidden bg-teal-950 p-10 lg:flex lg:flex-col lg:justify-between xl:p-14">
            <div>
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 items-center justify-center overflow-hidden rounded-xl bg-slate-50" aria-hidden="true">
                        <img src="{{ asset('images/servicehub-logo-mark-v1.png') }}" alt="" class="h-full w-full scale-[1.4] object-contain">
                    </span>
                    <span>
                        <span class="block text-lg font-semibold tracking-tight text-white">ServiceHub</span>
                        <span class="block text-sm text-teal-200">Admin operations</span>
                    </span>
                </div>

                <div class="mt-20 max-w-md xl:mt-28">
                    <p class="text-sm font-medium text-teal-300">Service management workspace</p>
                    <h1 class="mt-4 text-4xl font-semibold tracking-tight text-white xl:text-5xl">Run your service operations with clarity.</h1>
                    <p class="mt-5 text-base leading-7 text-teal-50/85">Manage bookings, customer quotations, staff, billing, payments, and business reporting in one place.</p>
                </div>
            </div>

            <p class="text-sm text-teal-200/80">Secure access for authorized ServiceHub administrators.</p>
        </section>

        <section class="flex items-center justify-center px-5 py-10 sm:px-8 lg:px-12">
            <div class="w-full max-w-md">
                <div class="mb-8 lg:hidden">
                    <div class="flex items-center gap-3">
                        <span class="flex h-10 w-10 items-center justify-center overflow-hidden rounded-xl bg-slate-50" aria-hidden="true">
                            <img src="{{ asset('images/servicehub-logo-mark-v1.png') }}" alt="" class="h-full w-full scale-[1.45] object-contain">
                        </span>
                        <span>
                            <span class="block text-lg font-semibold tracking-tight text-slate-950">ServiceHub</span>
                            <span class="block text-sm text-slate-500">Admin operations</span>
                        </span>
                    </div>
                </div>

                <div>
                    <p class="text-sm font-medium text-teal-700">Welcome back</p>
                    <h2 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">Sign in to your account</h2>
                    <p class="mt-3 text-sm leading-6 text-slate-600">Use your administrator credentials to continue to the ServiceHub dashboard.</p>
                </div>

                @if ($errors->any())
                    <div class="mt-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.login.store') }}" class="mt-8 space-y-5">
                    @csrf

                    <label class="block">
                        <span class="mb-2 block text-sm font-medium text-slate-800">Email</span>
                        <input
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            required
                            autofocus
                            autocomplete="email"
                            class="h-12 w-full rounded-lg border border-teal-200 bg-white px-4 text-sm placeholder:text-teal-900/40 focus:border-teal-600 focus:ring-teal-600"
                            placeholder="admin@example.com"
                        >
                    </label>

                    <label class="block">
                        <span class="mb-2 block text-sm font-medium text-slate-800">Password</span>
                        <input
                            type="password"
                            name="password"
                            required
                            autocomplete="current-password"
                            class="h-12 w-full rounded-lg border border-teal-200 bg-white px-4 text-sm placeholder:text-teal-900/40 focus:border-teal-600 focus:ring-teal-600"
                            placeholder="Enter your password"
                        >
                    </label>

                    <label class="flex items-center gap-2.5 text-sm text-slate-600">
                        <input type="checkbox" name="remember" value="1" class="rounded border-teal-300 text-teal-700 focus:ring-teal-600">
                        Remember me on this device
                    </label>

                    <button type="submit" class="w-full rounded-lg bg-teal-700 px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-teal-800 focus:outline-none focus:ring-2 focus:ring-teal-600 focus:ring-offset-2 active:translate-y-px">
                        Sign in
                    </button>
                </form>
            </div>
        </section>
    </main>
</body>
</html>
