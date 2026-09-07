@extends('admin.layouts.app', ['title' => 'Penyakit Cabai AI'])

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold tracking-normal text-stone-950">Penyakit</h1>
            <p class="mt-1 text-sm text-stone-600">Data mapping class AI dan konten penyakit.</p>
        </div>
        <a href="{{ route('admin.diseases.create') }}" class="rounded-md bg-emerald-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-800">
            Tambah
        </a>
    </div>

    <div class="rounded-lg border border-stone-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-stone-200 text-sm">
                <thead class="bg-stone-50 text-left text-xs font-semibold uppercase tracking-normal text-stone-500">
                    <tr>
                        <th class="px-5 py-3">Class AI</th>
                        <th class="px-5 py-3">Nama</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3">Deteksi</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse ($diseases as $disease)
                        <tr>
                            <td class="px-5 py-3 font-medium text-stone-950">{{ $disease->ai_class_name }}</td>
                            <td class="px-5 py-3 text-stone-700">{{ $disease->name }}</td>
                            <td class="px-5 py-3">
                                <span class="rounded-md px-2 py-1 text-xs font-semibold {{ $disease->is_active ? 'bg-emerald-100 text-emerald-900' : 'bg-stone-100 text-stone-600' }}">
                                    {{ $disease->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-stone-700">{{ $disease->detections_count ?? $disease->detections()->count() }}</td>
                            <td class="px-5 py-3 text-right">
                                <a href="{{ route('admin.diseases.show', $disease) }}" class="font-medium text-emerald-800 hover:text-emerald-950">Detail</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-8 text-center text-stone-500">Belum ada data penyakit.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-stone-200 px-5 py-4">
            {{ $diseases->links() }}
        </div>
    </div>
@endsection
