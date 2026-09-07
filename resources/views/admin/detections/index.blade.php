@extends('admin.layouts.app', ['title' => 'Riwayat Deteksi'])

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-semibold tracking-normal text-stone-950">Riwayat Deteksi</h1>
        <p class="mt-1 text-sm text-stone-600">Semua hasil deteksi yang tersimpan.</p>
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
                        <th class="px-5 py-3">Retake</th>
                        <th class="px-5 py-3">Tanggal</th>
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
                            <td class="px-5 py-3 text-stone-700">{{ $detection->needs_retake ? 'Ya' : 'Tidak' }}</td>
                            <td class="px-5 py-3 text-stone-700">{{ $detection->created_at?->format('d M Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-8 text-center text-stone-500">Belum ada riwayat deteksi.</td>
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
