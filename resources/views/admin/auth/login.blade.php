<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | ServiceHub</title>

    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-100">
    <main class="min-h-screen flex items-center justify-center px-4">
        <div class="w-full max-w-md">
            <div class="mb-8 text-center">
                <h1 class="text-3xl font-bold text-slate-900">
                    ServiceHub Admin
                </h1>
                <p class="mt-2 text-slate-500">
                    Sign in to manage bookings, quotations, staff and reports.
                </p>
            </div>

            <div class="rounded-2xl bg-white p-8 shadow-sm border border-slate-200">
                @if ($errors->any())
                    <div class="mb-5 rounded-xl bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.login.store') }}" class="space-y-5">
                    @csrf

                    <div>
                        <label class="block text-sm font-medium text-slate-700">
                            Email
                        </label>
                        <input
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            required
                            autofocus
                            class="mt-1 w-full rounded-xl border border-slate-300 px-4 py-3 outline-none focus:border-slate-900"
                            placeholder="admin@example.com"
                        >
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700">
                            Password
                        </label>
                        <input
                            type="password"
                            name="password"
                            required
                            class="mt-1 w-full rounded-xl border border-slate-300 px-4 py-3 outline-none focus:border-slate-900"
                            placeholder="••••••••"
                        >
                    </div>

                    <label class="flex items-center gap-2 text-sm text-slate-600">
                        <input
                            type="checkbox"
                            name="remember"
                            value="1"
                            class="rounded border-slate-300"
                        >
                        Remember me
                    </label>

                    <button
                        type="submit"
                        class="w-full rounded-xl bg-slate-900 px-4 py-3 font-semibold text-white hover:bg-slate-800"
                    >
                        Sign In
                    </button>
                </form>
            </div>
        </div>
    </main>
</body>
</html>