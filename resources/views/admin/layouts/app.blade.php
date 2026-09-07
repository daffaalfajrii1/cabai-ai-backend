<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Cabai AI Admin' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-stone-50 text-stone-900 antialiased">
    <header class="border-b border-stone-200 bg-white">
        <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-4 px-4 py-4 sm:px-6 lg:px-8">
            <a href="{{ route('dashboard') }}" class="text-lg font-semibold tracking-normal text-emerald-800">
                Cabai AI
            </a>

            <nav class="flex flex-wrap items-center gap-2 text-sm">
                <a href="{{ route('dashboard') }}" class="rounded-md px-3 py-2 font-medium {{ request()->routeIs('dashboard') ? 'bg-emerald-100 text-emerald-900' : 'text-stone-600 hover:bg-stone-100 hover:text-stone-950' }}">
                    Dashboard
                </a>
                <a href="{{ route('admin.diseases.index') }}" class="rounded-md px-3 py-2 font-medium {{ request()->routeIs('admin.diseases.*') ? 'bg-emerald-100 text-emerald-900' : 'text-stone-600 hover:bg-stone-100 hover:text-stone-950' }}">
                    Penyakit
                </a>
                <a href="{{ route('admin.detections.index') }}" class="rounded-md px-3 py-2 font-medium {{ request()->routeIs('admin.detections.*') ? 'bg-emerald-100 text-emerald-900' : 'text-stone-600 hover:bg-stone-100 hover:text-stone-950' }}">
                    Deteksi
                </a>
            </nav>

            <div class="flex items-center gap-3 text-sm">
                <span class="max-w-44 truncate text-stone-600">{{ auth()->user()?->email }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="rounded-md border border-stone-300 px-3 py-2 font-medium text-stone-700 hover:bg-stone-100">
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </header>

    <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        @if (session('status'))
            <div class="mb-6 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">
                {{ session('status') }}
            </div>
        @endif

        @yield('content')
    </main>
</body>
</html>
