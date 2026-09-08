@extends('admin.layouts.app', ['title' => 'Detail Deteksi'])

@section('content')
    @php
        $ai = $detection->raw_ai_response ?? [];
        $validator = data_get($ai, 'validator', []);
        $objectDetection = data_get($ai, 'object_detection', []);
        $bbox = data_get($objectDetection, 'bbox');
        $bboxStyle = null;

        if (is_array($bbox)) {
            if (isset($bbox['x'], $bbox['y'], $bbox['width'], $bbox['height'])) {
                $x1 = (float) $bbox['x'];
                $y1 = (float) $bbox['y'];
                $x2 = $x1 + (float) $bbox['width'];
                $y2 = $y1 + (float) $bbox['height'];
            } else {
                $values = array_values($bbox);
                $x1 = isset($values[0]) ? (float) $values[0] : null;
                $y1 = isset($values[1]) ? (float) $values[1] : null;
                $x2 = isset($values[2]) ? (float) $values[2] : null;
                $y2 = isset($values[3]) ? (float) $values[3] : null;
            }

            $imageWidth = (float) data_get($objectDetection, 'image_width', 0);
            $imageHeight = (float) data_get($objectDetection, 'image_height', 0);

            if (isset($x1, $y1, $x2, $y2)) {
                if ($imageWidth > 0 && $imageHeight > 0 && max($x1, $y1, $x2, $y2) > 1) {
                    $left = max(0, min(100, ($x1 / $imageWidth) * 100));
                    $top = max(0, min(100, ($y1 / $imageHeight) * 100));
                    $width = max(0, min(100 - $left, (($x2 - $x1) / $imageWidth) * 100));
                    $height = max(0, min(100 - $top, (($y2 - $y1) / $imageHeight) * 100));
                } else {
                    $left = max(0, min(100, $x1 * 100));
                    $top = max(0, min(100, $y1 * 100));
                    $width = max(0, min(100 - $left, ($x2 - $x1) * 100));
                    $height = max(0, min(100 - $top, ($y2 - $y1) * 100));
                }

                $bboxStyle = "left: {$left}%; top: {$top}%; width: {$width}%; height: {$height}%;";
            }
        }
    @endphp

    <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold tracking-normal text-stone-950">Detail Deteksi #{{ $detection->id }}</h1>
            <p class="mt-1 text-sm text-stone-600">{{ $detection->created_at?->format('d M Y H:i') }}</p>
        </div>
        <a href="{{ route('admin.detections.index') }}" class="rounded-md border border-stone-300 px-4 py-2.5 text-sm font-semibold text-stone-700 hover:bg-stone-100">Kembali</a>
    </div>

    <section class="grid gap-6 lg:grid-cols-[380px_1fr]">
        <div class="rounded-lg border border-stone-200 bg-white p-5 shadow-sm">
            <div class="relative overflow-hidden rounded-md bg-stone-100">
                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($detection->image_path) }}" alt="Foto deteksi" class="aspect-square w-full object-cover">
                @if ($bboxStyle)
                    <div class="absolute border-2 border-emerald-400 shadow-[0_0_0_9999px_rgba(0,0,0,0.10)]" style="{{ $bboxStyle }}"></div>
                @endif
            </div>
            <div class="mt-4 rounded-md border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-600">
                Bbox overlay: {{ $bboxStyle ? 'Tersedia' : 'Tidak tersedia' }}
            </div>
        </div>

        <div class="space-y-6">
            <div class="rounded-lg border border-stone-200 bg-white p-5 shadow-sm">
                <h2 class="text-base font-semibold tracking-normal text-stone-950">Hasil AI</h2>
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
                        <dt class="font-medium text-stone-500">Penyakit</dt>
                        <dd class="mt-1 text-stone-950">{{ $detection->disease?->name ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-stone-500">Confidence</dt>
                        <dd class="mt-1 text-stone-950">{{ $detection->confidence_percent === null ? '-' : number_format((float) $detection->confidence_percent, 2).'%' }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-stone-500">Classification Source</dt>
                        <dd class="mt-1 text-stone-950">{{ $detection->classification_source ?? data_get($ai, 'classification_source', '-') }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-stone-500">Valid Input</dt>
                        <dd class="mt-1 text-stone-950">{{ $detection->valid_input ? 'Ya' : 'Tidak' }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-stone-500">Needs Retake</dt>
                        <dd class="mt-1 text-stone-950">{{ $detection->needs_retake ? 'Ya' : 'Tidak' }}</dd>
                    </div>
                </dl>
            </div>

            <div class="rounded-lg border border-stone-200 bg-white p-5 shadow-sm">
                <h2 class="text-base font-semibold tracking-normal text-stone-950">Validator</h2>
                <dl class="mt-4 grid gap-4 text-sm sm:grid-cols-2">
                    @foreach ([
                        'method' => 'Method',
                        'positive_score' => 'Positive Score',
                        'best_label' => 'Best Label',
                        'threshold' => 'Threshold',
                    ] as $field => $label)
                        <div>
                            <dt class="font-medium text-stone-500">{{ $label }}</dt>
                            <dd class="mt-1 text-stone-950">{{ data_get($validator, $field, '-') }}</dd>
                        </div>
                    @endforeach
                </dl>
            </div>

            <div class="rounded-lg border border-stone-200 bg-white p-5 shadow-sm">
                <h2 class="text-base font-semibold tracking-normal text-stone-950">Object Detection</h2>
                <dl class="mt-4 grid gap-4 text-sm sm:grid-cols-2">
                    @foreach ([
                        'class_name' => 'Class Name',
                        'confidence' => 'Confidence',
                        'bbox' => 'Bbox',
                        'image_width' => 'Image Width',
                        'image_height' => 'Image Height',
                    ] as $field => $label)
                        <div class="{{ $field === 'bbox' ? 'sm:col-span-2' : '' }}">
                            <dt class="font-medium text-stone-500">{{ $label }}</dt>
                            <dd class="mt-1 break-words text-stone-950">
                                @php
                                    $value = data_get($objectDetection, $field);
                                @endphp
                                {{ is_array($value) ? json_encode($value) : ($value ?? '-') }}
                            </dd>
                        </div>
                    @endforeach
                </dl>
            </div>

            <div class="rounded-lg border border-stone-200 bg-white p-5 shadow-sm">
                <h2 class="text-base font-semibold tracking-normal text-stone-950">Top Predictions</h2>
                <div class="mt-4 space-y-3">
                    @forelse (array_slice($detection->top_predictions ?? [], 0, 3) as $prediction)
                        @php
                            $confidence = isset($prediction['confidence_percent'])
                                ? (float) $prediction['confidence_percent']
                                : (isset($prediction['confidence']) ? (float) $prediction['confidence'] * 100 : 0);
                            $confidence = max(0, min(100, $confidence));
                        @endphp
                        <div class="text-sm">
                            <div class="flex items-center justify-between gap-4">
                                <span class="font-medium text-stone-950">{{ $prediction['class_name'] ?? '-' }}</span>
                                <span class="text-stone-600">{{ number_format($confidence, 2) }}%</span>
                            </div>
                            <div class="mt-2 h-2 rounded-full bg-stone-100">
                                <div class="h-2 rounded-full bg-emerald-600" style="width: {{ $confidence }}%"></div>
                            </div>
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

            <div class="rounded-lg border border-stone-200 bg-white p-5 shadow-sm">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <h2 class="text-base font-semibold tracking-normal text-stone-950">Admin Review</h2>
                        <p class="mt-1 text-sm text-stone-500">{{ $detection->reviewer ? 'Direview oleh '.$detection->reviewer->name : 'Belum ada reviewer.' }}</p>
                    </div>
                    <span class="rounded-md px-2.5 py-1 text-xs font-semibold {{ $detection->reviewStatus() === 'verified' ? 'bg-emerald-100 text-emerald-800' : ($detection->reviewStatus() === 'corrected' ? 'bg-violet-100 text-violet-800' : ($detection->reviewStatus() === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-stone-100 text-stone-700')) }}">
                        {{ $detection->reviewStatusLabel() }}
                    </span>
                </div>

                @if ($errors->any())
                    <div class="mt-4 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                        {{ $errors->first() }}
                    </div>
                @endif

                <dl class="mt-4 grid gap-4 text-sm sm:grid-cols-2">
                    <div>
                        <dt class="font-medium text-stone-500">Reviewer</dt>
                        <dd class="mt-1 text-stone-950">{{ $detection->reviewer?->name ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-stone-500">Waktu Review</dt>
                        <dd class="mt-1 text-stone-950">{{ $detection->reviewed_at?->format('d M Y H:i') ?? '-' }}</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="font-medium text-stone-500">Penyakit Koreksi</dt>
                        <dd class="mt-1 text-stone-950">{{ $detection->correctedDisease?->name ?? '-' }}</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="font-medium text-stone-500">Catatan</dt>
                        <dd class="mt-1 whitespace-pre-line text-stone-950">{{ $detection->review_notes ?: '-' }}</dd>
                    </div>
                </dl>

                <form method="POST" action="{{ route('admin.detections.review.update', $detection) }}" class="mt-6 space-y-4">
                    @csrf
                    @method('PATCH')
                    <label class="block text-sm font-medium text-stone-700">
                        Status Review
                        <select name="review_status" required class="mt-1 block w-full rounded-md border-stone-300 px-3 py-2 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                            @foreach ($reviewStatuses as $value => $label)
                                <option value="{{ $value }}" @selected(old('review_status', $detection->reviewStatus()) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label class="block text-sm font-medium text-stone-700">
                        Penyakit Hasil Koreksi
                        <select name="corrected_disease_id" class="mt-1 block w-full rounded-md border-stone-300 px-3 py-2 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                            <option value="">Tidak ada koreksi</option>
                            @foreach ($diseases as $disease)
                                <option value="{{ $disease->id }}" @selected((string) old('corrected_disease_id', $detection->corrected_disease_id) === (string) $disease->id)>{{ $disease->name }} ({{ $disease->ai_class_name }})</option>
                            @endforeach
                        </select>
                    </label>

                    <label class="block text-sm font-medium text-stone-700">
                        Catatan
                        <textarea name="review_notes" rows="4" class="mt-1 block w-full rounded-md border-stone-300 px-3 py-2 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">{{ old('review_notes', $detection->review_notes) }}</textarea>
                    </label>

                    <button type="submit" class="rounded-md bg-emerald-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-800">
                        Simpan Review
                    </button>
                </form>
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
