@extends('admin.layouts.app', ['title' => 'Dashboard Cabai AI'])

@section('content')
    @php
        $statCards = [
            ['label' => 'Total User', 'value' => number_format($totalUsers), 'tone' => 'emerald'],
            ['label' => 'Total Deteksi', 'value' => number_format($totalDetections), 'tone' => 'sky'],
            ['label' => 'Deteksi Hari Ini', 'value' => number_format($detectionsToday), 'tone' => 'amber'],
            ['label' => 'Invalid Input', 'value' => number_format($invalidInputs), 'tone' => 'red'],
            ['label' => 'Needs Retake', 'value' => number_format($needsRetake), 'tone' => 'orange'],
            ['label' => 'Belum Ditinjau', 'value' => number_format($pendingReviews), 'tone' => 'stone'],
            ['label' => 'Dikoreksi Admin', 'value' => number_format($correctedDetections), 'tone' => 'violet'],
            ['label' => 'Average Confidence', 'value' => $averageConfidence === null ? '-' : number_format((float) $averageConfidence, 2).'%', 'tone' => 'teal'],
        ];

        $toneClasses = [
            'emerald' => 'border-emerald-200 bg-emerald-50 text-emerald-900',
            'sky' => 'border-sky-200 bg-sky-50 text-sky-900',
            'amber' => 'border-amber-200 bg-amber-50 text-amber-900',
            'red' => 'border-red-200 bg-red-50 text-red-900',
            'orange' => 'border-orange-200 bg-orange-50 text-orange-900',
            'stone' => 'border-stone-200 bg-white text-stone-950',
            'violet' => 'border-violet-200 bg-violet-50 text-violet-900',
            'teal' => 'border-teal-200 bg-teal-50 text-teal-900',
        ];
    @endphp

    <div class="mb-8 flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold tracking-normal text-stone-950">Dashboard</h1>
            <p class="mt-1 text-sm text-stone-600">Ringkasan admin dan monitoring deteksi Cabai AI.</p>
        </div>
        <div class="rounded-md border px-3 py-2 text-sm font-medium {{ ($aiHealth['online'] ?? false) ? 'border-emerald-200 bg-emerald-50 text-emerald-900' : 'border-red-200 bg-red-50 text-red-800' }}">
            AI Service {{ ($aiHealth['online'] ?? false) ? 'Online' : 'Offline' }}
        </div>
    </div>

    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($statCards as $stat)
            <article class="rounded-lg border p-5 shadow-sm {{ $toneClasses[$stat['tone']] }}">
                <div class="text-sm font-medium text-stone-500">{{ $stat['label'] }}</div>
                <div class="mt-2 text-3xl font-semibold tracking-normal">{{ $stat['value'] }}</div>
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
                                    <div class="mt-1 text-xs text-stone-500">{{ $detection->reviewStatusLabel() }}</div>
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

    <section class="mt-8 grid gap-6 lg:grid-cols-[1fr_360px]">
        <div class="rounded-lg border border-stone-200 bg-white shadow-sm">
            <div class="flex items-center justify-between gap-4 border-b border-stone-200 px-5 py-4">
                <h2 class="text-base font-semibold tracking-normal text-stone-950">Perlu Ditinjau</h2>
                <a href="{{ route('admin.reviews.index') }}" class="text-sm font-semibold text-emerald-800 hover:text-emerald-950">Lihat semua</a>
            </div>
            <div class="divide-y divide-stone-100">
                @forelse ($reviewDetections as $detection)
                    <div class="flex flex-wrap items-center justify-between gap-4 px-5 py-4 text-sm">
                        <div class="min-w-0">
                            <a href="{{ route('admin.detections.show', $detection) }}" class="font-medium text-stone-950 hover:text-emerald-900">
                                {{ $detection->ai_class_name ?? 'Tidak tersedia' }}
                            </a>
                            <div class="mt-1 text-stone-500">{{ implode(', ', $detection->reviewReasons()) }}</div>
                        </div>
                        <span class="rounded-md px-2.5 py-1 text-xs font-semibold {{ $detection->reviewPriority() === 'HIGH' ? 'bg-red-100 text-red-800' : ($detection->reviewPriority() === 'MEDIUM' ? 'bg-amber-100 text-amber-800' : 'bg-stone-100 text-stone-700') }}">
                            {{ $detection->reviewPriority() }}
                        </span>
                    </div>
                @empty
                    <div class="px-5 py-8 text-center text-sm text-stone-500">Tidak ada detection prioritas.</div>
                @endforelse
            </div>
        </div>

        <div class="rounded-lg border border-stone-200 bg-white shadow-sm">
            <div class="border-b border-stone-200 px-5 py-4">
                <h2 class="text-base font-semibold tracking-normal text-stone-950">AI Engine</h2>
            </div>
            <dl class="space-y-4 p-5 text-sm">
                <div class="flex items-center justify-between gap-4">
                    <dt class="text-stone-500">FastAPI</dt>
                    <dd class="font-semibold {{ ($aiHealth['online'] ?? false) ? 'text-emerald-800' : 'text-red-800' }}">{{ ($aiHealth['online'] ?? false) ? 'Online' : 'Offline' }}</dd>
                </div>
                <div class="flex items-center justify-between gap-4">
                    <dt class="text-stone-500">Validator</dt>
                    <dd class="font-medium text-stone-950">{{ $aiEngine['validator'] }}</dd>
                </div>
                <div class="flex items-center justify-between gap-4">
                    <dt class="text-stone-500">Detector</dt>
                    <dd class="font-medium text-stone-950">{{ $aiEngine['detector'] }}</dd>
                </div>
                <div class="flex items-center justify-between gap-4">
                    <dt class="text-stone-500">Classifier</dt>
                    <dd class="font-medium text-stone-950">{{ $aiEngine['classifier'] }}</dd>
                </div>
                <div class="flex items-center justify-between gap-4">
                    <dt class="text-stone-500">Classes</dt>
                    <dd class="font-medium text-stone-950">{{ $aiEngine['classes'] }}</dd>
                </div>
                <div class="flex items-center justify-between gap-4">
                    <dt class="text-stone-500">Last Check</dt>
                    <dd class="font-medium text-stone-950">{{ ($aiHealth['checked_at'] ?? null)?->format('d M Y H:i:s') ?? '-' }}</dd>
                </div>
                <div class="flex items-center justify-between gap-4">
                    <dt class="text-stone-500">Response Time</dt>
                    <dd class="font-medium text-stone-950">{{ isset($aiHealth['response_time_ms']) ? number_format((float) $aiHealth['response_time_ms'], 2).' ms' : '-' }}</dd>
                </div>
            </dl>
        </div>
    </section>
@endsection
