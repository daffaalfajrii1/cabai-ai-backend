@extends('admin.layouts.app', ['title' => $user->name])

@section('content')
    <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold tracking-normal text-stone-950">{{ $user->name }}</h1>
            <p class="mt-1 text-sm text-stone-600">{{ $user->email }}</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('admin.users.edit', $user) }}" class="rounded-md bg-emerald-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-800">Edit</a>
            <a href="{{ route('admin.users.index') }}" class="rounded-md border border-stone-300 px-4 py-2.5 text-sm font-semibold text-stone-700 hover:bg-stone-100">Kembali</a>
        </div>
    </div>

    <section class="grid gap-6 lg:grid-cols-[320px_1fr]">
        <div class="rounded-lg border border-stone-200 bg-white p-5 shadow-sm">
            <dl class="space-y-4 text-sm">
                <div>
                    <dt class="font-medium text-stone-500">Role</dt>
                    <dd class="mt-1 text-stone-950">{{ ucfirst($user->role) }}</dd>
                </div>
                <div>
                    <dt class="font-medium text-stone-500">Status</dt>
                    <dd class="mt-1 text-stone-950">{{ $user->is_active ? 'Aktif' : 'Nonaktif' }}</dd>
                </div>
                <div>
                    <dt class="font-medium text-stone-500">Tanggal Registrasi</dt>
                    <dd class="mt-1 text-stone-950">{{ $user->created_at?->format('d M Y H:i') }}</dd>
                </div>
                <div>
                    <dt class="font-medium text-stone-500">Jumlah Deteksi</dt>
                    <dd class="mt-1 text-stone-950">{{ $user->detections_count }}</dd>
                </div>
            </dl>
        </div>

        <div class="rounded-lg border border-stone-200 bg-white shadow-sm">
            <div class="border-b border-stone-200 px-5 py-4">
                <h2 class="text-base font-semibold tracking-normal text-stone-950">Deteksi Terbaru</h2>
            </div>
            <div class="divide-y divide-stone-100">
                @forelse ($user->detections as $detection)
                    <div class="flex flex-wrap items-center justify-between gap-4 px-5 py-4 text-sm">
                        <div>
                            <a href="{{ route('admin.detections.show', $detection) }}" class="font-medium text-stone-950 hover:text-emerald-900">
                                {{ $detection->ai_class_name ?? 'Tidak tersedia' }}
                            </a>
                            <div class="mt-1 text-stone-500">{{ $detection->created_at?->format('d M Y H:i') }}</div>
                        </div>
                        <div class="text-stone-700">{{ $detection->confidence_percent === null ? '-' : number_format((float) $detection->confidence_percent, 2).'%' }}</div>
                    </div>
                @empty
                    <div class="px-5 py-8 text-center text-sm text-stone-500">Belum ada deteksi.</div>
                @endforelse
            </div>
        </div>
    </section>
@endsection
