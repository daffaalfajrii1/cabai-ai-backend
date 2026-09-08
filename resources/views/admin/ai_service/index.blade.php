@extends('admin.layouts.app', ['title' => 'AI Service'])

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-semibold tracking-normal text-stone-950">AI Service</h1>
        <p class="mt-1 text-sm text-stone-600">Status koneksi FastAPI dan konfigurasi engine.</p>
    </div>

    <section class="grid gap-6 lg:grid-cols-2">
        <div class="rounded-lg border border-stone-200 bg-white p-5 shadow-sm">
            <h2 class="text-base font-semibold tracking-normal text-stone-950">FastAPI</h2>
            <dl class="mt-4 space-y-4 text-sm">
                <div class="flex items-center justify-between gap-4">
                    <dt class="text-stone-500">Status</dt>
                    <dd class="font-semibold {{ ($aiHealth['online'] ?? false) ? 'text-emerald-800' : 'text-red-800' }}">{{ ($aiHealth['online'] ?? false) ? 'Online' : 'Offline' }}</dd>
                </div>
                <div class="flex items-center justify-between gap-4">
                    <dt class="text-stone-500">HTTP Status</dt>
                    <dd class="font-medium text-stone-950">{{ $aiHealth['status'] ?? '-' }}</dd>
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

        <div class="rounded-lg border border-stone-200 bg-white p-5 shadow-sm">
            <h2 class="text-base font-semibold tracking-normal text-stone-950">Engine</h2>
            <dl class="mt-4 space-y-4 text-sm">
                <div class="flex items-center justify-between gap-4">
                    <dt class="text-stone-500">Validator</dt>
                    <dd class="font-medium text-stone-950">CLIP</dd>
                </div>
                <div class="flex items-center justify-between gap-4">
                    <dt class="text-stone-500">Detector</dt>
                    <dd class="font-medium text-stone-950">YOLOE</dd>
                </div>
                <div class="flex items-center justify-between gap-4">
                    <dt class="text-stone-500">Classifier</dt>
                    <dd class="font-medium text-stone-950">EfficientNet V4</dd>
                </div>
                <div class="flex items-center justify-between gap-4">
                    <dt class="text-stone-500">Classes</dt>
                    <dd class="font-medium text-stone-950">{{ $classesCount }}</dd>
                </div>
            </dl>
        </div>
    </section>

    @if (app()->environment('local'))
        <section class="mt-6 rounded-lg border border-stone-200 bg-white p-5 shadow-sm">
            <h2 class="text-base font-semibold tracking-normal text-stone-950">Model Info</h2>
            <pre class="mt-4 overflow-x-auto rounded-md bg-stone-950 p-4 text-xs leading-5 text-stone-50">{{ json_encode($modelInfo['data'] ?? null, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
        </section>
    @endif
@endsection
