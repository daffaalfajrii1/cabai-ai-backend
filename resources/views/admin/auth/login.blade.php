<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login Admin Cabai AI</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-stone-50 text-stone-900 antialiased">
    <main class="flex min-h-screen items-center justify-center px-4 py-10">
        <section class="w-full max-w-md rounded-lg border border-stone-200 bg-white p-6 shadow-sm">
            <div class="mb-6">
                <h1 class="text-2xl font-semibold tracking-normal text-emerald-800">Cabai AI Admin</h1>
                <p class="mt-1 text-sm text-stone-600">Masuk untuk mengelola data penyakit dan riwayat deteksi.</p>
            </div>

            @if ($errors->any())
                <div class="mb-5 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login.store') }}" class="space-y-4">
                @csrf
                <label class="block text-sm font-medium text-stone-700">
                    Email
                    <input
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        autofocus
                        class="mt-1 block w-full rounded-md border-stone-300 px-3 py-2 shadow-sm focus:border-emerald-600 focus:ring-emerald-600"
                    >
                </label>

                <label class="block text-sm font-medium text-stone-700">
                    Password
                    <input
                        type="password"
                        name="password"
                        required
                        class="mt-1 block w-full rounded-md border-stone-300 px-3 py-2 shadow-sm focus:border-emerald-600 focus:ring-emerald-600"
                    >
                </label>

                <label class="flex items-center gap-2 text-sm text-stone-700">
                    <input type="checkbox" name="remember" value="1" class="rounded border-stone-300 text-emerald-700 focus:ring-emerald-600">
                    Ingat sesi
                </label>

                <button type="submit" class="w-full rounded-md bg-emerald-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-800">
                    Login
                </button>
            </form>
        </section>
    </main>
</body>
</html>
