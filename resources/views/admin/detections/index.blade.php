@extends('admin.layouts.app', ['title' => $isReviewQueue ? 'Perlu Ditinjau' : 'Riwayat Deteksi'])

@section('content')
    @php
        $filterOptions = [
            'all' => 'Semua',
            'pending' => 'Belum Ditinjau',
            'verified' => 'Terverifikasi',
            'corrected' => 'Dikoreksi',
            'rejected' => 'Ditolak',
            'invalid' => 'Invalid',
            'needs-retake' => 'Needs Retake',
            'low-confidence' => 'Low Confidence',
        ];
    @endphp

    <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold tracking-normal text-stone-950">{{ $isReviewQueue ? 'Perlu Ditinjau' : 'Riwayat Deteksi' }}</h1>
            <p class="mt-1 text-sm text-stone-600">{{ $isReviewQueue ? 'Detection yang membutuhkan verifikasi admin.' : 'Semua hasil deteksi yang tersimpan.' }}</p>
        </div>
        <form method="GET" class="flex flex-wrap items-center gap-2">
            <select name="filter" class="rounded-md border-stone-300 text-sm shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                @foreach ($filterOptions as $value => $label)
                    <option value="{{ $value }}" @selected($filter === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <button type="submit" class="rounded-md bg-emerald-700 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-800">Filter</button>
        </form>
    </div>

    <div class="rounded-lg border border-stone-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-stone-200 text-sm">
                <thead class="bg-stone-50 text-left text-xs font-semibold uppercase tracking-normal text-stone-500">
                    <tr>
                        <th class="px-5 py-3">Foto</th>
                        <th class="px-5 py-3">User</th>
                        <th class="px-5 py-3">Hasil</th>
                        <th class="px-5 py-3">Confidence</th>
                        <th class="px-5 py-3">Reason</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3">Tanggal</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse ($detections as $detection)
                        <tr>
                            <td class="px-5 py-3">
                                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($detection->image_path) }}" alt="Foto deteksi" class="h-14 w-14 rounded-md object-cover">
                            </td>
                            <td class="px-5 py-3 text-stone-700">{{ $detection->user?->name ?? 'Tanpa user' }}</td>
                            <td class="px-5 py-3">
                                <a href="{{ route('admin.detections.show', $detection) }}" class="font-medium text-emerald-800 hover:text-emerald-950">
                                    {{ $detection->ai_class_name ?? 'Tidak tersedia' }}
                                </a>
                                <div class="mt-1 text-xs text-stone-500">{{ $detection->disease?->name ?? 'Belum termapping' }}</div>
                            </td>
                            <td class="px-5 py-3 text-stone-700">{{ $detection->confidence_percent === null ? '-' : number_format((float) $detection->confidence_percent, 2).'%' }}</td>
                            <td class="px-5 py-3">
                                <div class="flex max-w-xs flex-wrap gap-1">
                                    @foreach ($detection->reviewReasons() as $reason)
                                        <span class="rounded-md bg-stone-100 px-2 py-1 text-xs font-medium text-stone-700">{{ $reason }}</span>
                                    @endforeach
                                </div>
                                <span class="mt-2 inline-flex rounded-md px-2 py-1 text-xs font-semibold {{ $detection->reviewPriority() === 'HIGH' ? 'bg-red-100 text-red-800' : ($detection->reviewPriority() === 'MEDIUM' ? 'bg-amber-100 text-amber-800' : 'bg-stone-100 text-stone-700') }}">{{ $detection->reviewPriority() }}</span>
                            </td>
                            <td class="px-5 py-3">
                                <span class="rounded-md px-2 py-1 text-xs font-semibold {{ $detection->reviewStatus() === 'verified' ? 'bg-emerald-100 text-emerald-800' : ($detection->reviewStatus() === 'corrected' ? 'bg-violet-100 text-violet-800' : ($detection->reviewStatus() === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-stone-100 text-stone-700')) }}">
                                    {{ $detection->reviewStatusLabel() }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-stone-700">{{ $detection->created_at?->format('d M Y H:i') }}</td>
                            <td class="px-5 py-3 text-right">
                                <a href="{{ route('admin.detections.show', $detection) }}" class="font-medium text-emerald-800 hover:text-emerald-950">Review</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-8 text-center text-stone-500">Belum ada riwayat deteksi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-stone-200 px-5 py-4">
            {{ $detections->links() }}
        </div>
    </div>
@endsection
