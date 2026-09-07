@extends('admin.layouts.app', ['title' => 'Dashboard Cabai AI'])

@section('content')
    <div class="mb-8 flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold tracking-normal text-stone-950">Dashboard</h1>
            <p class="mt-1 text-sm text-stone-600">Ringkasan data Cabai AI hari ini.</p>
        </div>
        <div class="rounded-md border px-3 py-2 text-sm font-medium {{ ($aiHealth['online'] ?? false) ? 'border-emerald-200 bg-emerald-50 text-emerald-900' : 'border-red-200 bg-red-50 text-red-800' }}">
            AI Service {{ ($aiHealth['online'] ?? false) ? 'Online' : 'Offline' }}
        </div>
    </div>

    <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ([
            ['label' => 'Total User', 'value' => $totalUsers],
            ['label' => 'Total Deteksi', 'value' => $totalDetections],
            ['label' => 'Deteksi Hari Ini', 'value' => $detectionsToday],
            ['label' => 'Needs Retake', 'value' => $needsRetake],
        ] as $stat)
            <article class="rounded-lg border border-stone-200 bg-white p-5 shadow-sm">
                <div class="text-sm font-medium text-stone-500">{{ $stat['label'] }}</div>
                <div class="mt-2 text-3xl font-semibold tracking-normal text-stone-950">{{ number_format($stat['value']) }}</div>
            </article>
        @endforeach
    </section>

    <section class="mt-8 grid gap-6 lg:grid-cols-[1fr_360px]">
        <div class="rounded-lg border border-stone-200 bg-white shadow-sm">
            <div class="border-b border-stone-200 px-5 py-4">
                <h2 class="text-base font-semibold tracking-normal text-stone-950">Deteksi Terbaru</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-stone-200 text-sm">
                    <thead class="bg-stone-50 text-left text-xs font-semibold uppercase tracking-normal text-stone-500">
                        <tr>
                            <th class="px-5 py-3">User</th>
                            <th class="px-5 py-3">Hasil</th>
                            <th class="px-5 py-3">Confidence</th>
                            <th class="px-5 py-3">Tanggal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        @forelse ($latestDetections as $detection)
                            <tr>
                                <td class="px-5 py-3 text-stone-700">{{ $detection->user?->name ?? 'Tanpa user' }}</td>
                                <td class="px-5 py-3">
                                    <a href="{{ route('admin.detections.show', $detection) }}" class="font-medium text-emerald-800 hover:text-emerald-950">
                                        {{ $detection->ai_class_name ?? 'Tidak tersedia' }}
                                    </a>
                                </td>
                                <td class="px-5 py-3 text-stone-700">{{ $detection->confidence_percent === null ? '-' : number_format((float) $detection->confidence_percent, 2).'%' }}</td>
                                <td class="px-5 py-3 text-stone-700">{{ $detection->created_at?->format('d M Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-5 py-8 text-center text-stone-500">Belum ada deteksi.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="rounded-lg border border-stone-200 bg-white shadow-sm">
            <div class="border-b border-stone-200 px-5 py-4">
                <h2 class="text-base font-semibold tracking-normal text-stone-950">Penyakit Paling Sering</h2>
            </div>
            <div class="divide-y divide-stone-100">
                @forelse ($topDiseases as $disease)
                    <div class="flex items-center justify-between gap-4 px-5 py-4 text-sm">
                        <div>
                            <div class="font-medium text-stone-950">{{ $disease->name }}</div>
                            <div class="mt-1 text-stone-500">{{ $disease->ai_class_name }}</div>
                        </div>
                        <div class="rounded-md bg-stone-100 px-2.5 py-1 font-semibold text-stone-700">
                            {{ $disease->detections_count }}
                        </div>
                    </div>
                @empty
                    <div class="px-5 py-8 text-center text-sm text-stone-500">Belum ada data.</div>
                @endforelse
            </div>
        </div>
    </section>
@endsection
