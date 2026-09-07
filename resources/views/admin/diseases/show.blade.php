@extends('admin.layouts.app', ['title' => $disease->name])

@section('content')
    <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold tracking-normal text-stone-950">{{ $disease->name }}</h1>
            <p class="mt-1 text-sm text-stone-600">{{ $disease->ai_class_name }}</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('admin.diseases.edit', $disease) }}" class="rounded-md bg-emerald-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-800">Edit</a>
            <a href="{{ route('admin.diseases.index') }}" class="rounded-md border border-stone-300 px-4 py-2.5 text-sm font-semibold text-stone-700 hover:bg-stone-100">Kembali</a>
        </div>
    </div>

    <section class="grid gap-5 lg:grid-cols-[320px_1fr]">
        <div class="rounded-lg border border-stone-200 bg-white p-5 shadow-sm">
            <dl class="space-y-4 text-sm">
                <div>
                    <dt class="font-medium text-stone-500">Slug</dt>
                    <dd class="mt-1 text-stone-950">{{ $disease->slug }}</dd>
                </div>
                <div>
                    <dt class="font-medium text-stone-500">Nama Ilmiah</dt>
                    <dd class="mt-1 text-stone-950">{{ $disease->scientific_name ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="font-medium text-stone-500">Status</dt>
                    <dd class="mt-1 text-stone-950">{{ $disease->is_active ? 'Aktif' : 'Nonaktif' }}</dd>
                </div>
                <div>
                    <dt class="font-medium text-stone-500">Daun Sehat</dt>
                    <dd class="mt-1 text-stone-950">{{ $disease->is_healthy ? 'Ya' : 'Tidak' }}</dd>
                </div>
                <div>
                    <dt class="font-medium text-stone-500">Total Deteksi</dt>
                    <dd class="mt-1 text-stone-950">{{ $disease->detections_count }}</dd>
                </div>
            </dl>
        </div>

        <div class="rounded-lg border border-stone-200 bg-white p-5 shadow-sm">
            <dl class="space-y-6">
                @foreach ([
                    'description' => 'Deskripsi',
                    'symptoms' => 'Gejala',
                    'cause' => 'Penyebab',
                    'treatment' => 'Penanganan',
                    'prevention' => 'Pencegahan',
                ] as $field => $label)
                    <div>
                        <dt class="text-sm font-semibold text-stone-950">{{ $label }}</dt>
                        <dd class="mt-2 whitespace-pre-line text-sm leading-6 text-stone-700">{{ $disease->{$field} ?: '-' }}</dd>
                    </div>
                @endforeach
            </dl>
        </div>
    </section>
@endsection
