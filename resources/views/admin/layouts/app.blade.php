<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Cabai AI Admin' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-stone-100 text-stone-900 antialiased">
    @php
        $navSections = [
            'Utama' => [
                ['label' => 'Dashboard', 'route' => 'dashboard', 'active' => ['dashboard']],
            ],
            'Monitoring' => [
                ['label' => 'Deteksi', 'route' => 'admin.detections.index', 'active' => ['admin.detections.*']],
                ['label' => 'Perlu Ditinjau', 'route' => 'admin.reviews.index', 'active' => ['admin.reviews.*']],
            ],
            'Master Data' => [
                ['label' => 'Penyakit', 'route' => 'admin.diseases.index', 'active' => ['admin.diseases.*']],
                ['label' => 'Users', 'route' => 'admin.users.index', 'active' => ['admin.users.*']],
            ],
            'Laporan' => [
                ['label' => 'Laporan', 'route' => 'admin.reports.index', 'active' => ['admin.reports.*']],
            ],
            'System' => [
                ['label' => 'AI Service', 'route' => 'admin.ai-service.index', 'active' => ['admin.ai-service.*']],
                ['label' => 'Audit Log', 'route' => 'admin.audit-logs.index', 'active' => ['admin.audit-logs.*']],
            ],
        ];
    @endphp

    <div class="min-h-screen lg:flex">
        <aside class="border-b border-stone-200 bg-white lg:fixed lg:inset-y-0 lg:left-0 lg:w-72 lg:border-b-0 lg:border-r">
            <div class="flex h-full flex-col">
                <div class="border-b border-stone-200 px-5 py-5">
                    <a href="{{ route('dashboard') }}" class="block text-lg font-semibold tracking-normal text-emerald-800">
                        Cabai AI
                    </a>
                    <div class="mt-1 text-sm text-stone-500">Admin Console</div>
                </div>

                <nav class="flex-1 space-y-5 overflow-y-auto px-4 py-5">
                    @foreach ($navSections as $section => $items)
                        <div>
                            <div class="px-2 text-xs font-semibold uppercase tracking-normal text-stone-400">{{ $section }}</div>
                            <div class="mt-2 space-y-1">
                                @foreach ($items as $item)
                                    @php
                                        $active = request()->routeIs(...$item['active']);
                                    @endphp
                                    <a href="{{ route($item['route']) }}" class="block rounded-md px-3 py-2 text-sm font-medium {{ $active ? 'bg-emerald-100 text-emerald-950' : 'text-stone-600 hover:bg-stone-100 hover:text-stone-950' }}">
                                        {{ $item['label'] }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </nav>

                <div class="border-t border-stone-200 px-5 py-4">
                    <div class="min-w-0 text-sm">
                        <div class="truncate font-medium text-stone-950">{{ auth()->user()?->name }}</div>
                        <div class="truncate text-stone-500">{{ auth()->user()?->email }}</div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}" class="mt-3">
                        @csrf
                        <button type="submit" class="w-full rounded-md border border-stone-300 px-3 py-2 text-sm font-semibold text-stone-700 hover:bg-stone-100">
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <main class="min-w-0 flex-1 px-4 py-6 sm:px-6 lg:ml-72 lg:px-8 lg:py-8">
            @if (session('status'))
                <div class="mb-6 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">
                    {{ session('status') }}
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</body>
</html>
