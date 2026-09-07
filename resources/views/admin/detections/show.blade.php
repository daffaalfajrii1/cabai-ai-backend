@extends('admin.layouts.app', ['title' => 'Detail Deteksi'])

@section('content')
    <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold tracking-normal text-stone-950">Detail Deteksi #{{ $detection->id }}</h1>
            <p class="mt-1 text-sm text-stone-600">{{ $detection->created_at?->format('d M Y H:i') }}</p>
        </div>
        <a href="{{ route('admin.detections.index') }}" class="rounded-md border border-stone-300 px-4 py-2.5 text-sm font-semibold text-stone-700 hover:bg-stone-100">Kembali</a>
    </div>

    <section class="grid gap-6 lg:grid-cols-[380px_1fr]">
        <div class="rounded-lg border border-stone-200 bg-white p-5 shadow-sm">
            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($detection->image_path) }}" alt="Foto deteksi" class="aspect-square w-full rounded-md object-cover">
        </div>

        <div class="space-y-6">
            <div class="rounded-lg border border-stone-200 bg-white p-5 shadow-sm">
                <h2 class="text-base font-semibold tracking-normal text-stone-950">Prediction</h2>
                <dl class="mt-4 grid gap-4 text-sm sm:grid-cols-2">
                    <div>
                        <dt class="font-medium text-stone-500">User</dt>
                        <dd class="mt-1 text-stone-950">{{ $detection->user?->name ?? 'Tanpa user' }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-stone-500">Class AI</dt>
                        <dd class="mt-1 text-stone-950">{{ $detection->ai_class_name ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-stone-500">Confidence</dt>
                        <dd class="mt-1 text-stone-950">{{ $detection->confidence_percent === null ? '-' : number_format((float) $detection->confidence_percent, 2).'%' }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-stone-500">Needs Retake</dt>
                        <dd class="mt-1 text-stone-950">{{ $detection->needs_retake ? 'Ya' : 'Tidak' }}</dd>
                    </div>
                </dl>
            </div>

            <div class="rounded-lg border border-stone-200 bg-white p-5 shadow-sm">
                <h2 class="text-base font-semibold tracking-normal text-stone-950">Top 3</h2>
                <div class="mt-4 divide-y divide-stone-100">
                    @forelse (array_slice($detection->top_predictions ?? [], 0, 3) as $prediction)
                        <div class="flex items-center justify-between gap-4 py-3 text-sm">
                            <span class="font-medium text-stone-950">{{ $prediction['class_name'] ?? '-' }}</span>
                            <span class="text-stone-600">{{ isset($prediction['confidence']) ? number_format(((float) $prediction['confidence']) * 100, 2).'%' : '-' }}</span>
                        </div>
                    @empty
                        <div class="py-4 text-sm text-stone-500">Tidak ada top predictions.</div>
                    @endforelse
                </div>
            </div>

            <div class="rounded-lg border border-stone-200 bg-white p-5 shadow-sm">
                <h2 class="text-base font-semibold tracking-normal text-stone-950">Disease Detail</h2>
                @if ($detection->disease)
                    <div class="mt-4 space-y-4 text-sm">
                        <div>
                            <div class="font-medium text-stone-950">{{ $detection->disease->name }}</div>
                            <div class="mt-1 text-stone-500">{{ $detection->disease->slug }}</div>
                        </div>
                        <p class="leading-6 text-stone-700">{{ $detection->disease->description ?: '-' }}</p>
                    </div>
                @else
                    <p class="mt-4 text-sm text-stone-500">Belum ada disease mapping untuk class ini.</p>
                @endif
            </div>

            @if (app()->environment('local'))
                <div class="rounded-lg border border-stone-200 bg-white p-5 shadow-sm">
                    <h2 class="text-base font-semibold tracking-normal text-stone-950">Raw AI Response</h2>
                    <pre class="mt-4 overflow-x-auto rounded-md bg-stone-950 p-4 text-xs leading-5 text-stone-50">{{ json_encode($detection->raw_ai_response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                </div>
            @endif
        </div>
    </section>
@endsection
