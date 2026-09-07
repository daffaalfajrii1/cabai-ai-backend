@csrf

@if ($errors->any())
    <div class="rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
        {{ $errors->first() }}
    </div>
@endif

@if (! $disease->exists)
    <label class="block text-sm font-medium text-stone-700">
        Class AI
        <input name="ai_class_name" value="{{ old('ai_class_name', $disease->ai_class_name) }}" required class="mt-1 block w-full rounded-md border-stone-300 px-3 py-2 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
    </label>
@else
    <div class="rounded-md border border-stone-200 bg-stone-50 px-4 py-3 text-sm">
        <div class="font-medium text-stone-700">Class AI</div>
        <div class="mt-1 text-stone-950">{{ $disease->ai_class_name }}</div>
    </div>
@endif

<label class="block text-sm font-medium text-stone-700">
    Nama
    <input name="name" value="{{ old('name', $disease->name) }}" required class="mt-1 block w-full rounded-md border-stone-300 px-3 py-2 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
</label>

<label class="block text-sm font-medium text-stone-700">
    Nama Ilmiah
    <input name="scientific_name" value="{{ old('scientific_name', $disease->scientific_name) }}" class="mt-1 block w-full rounded-md border-stone-300 px-3 py-2 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
</label>

@foreach ([
    'description' => 'Deskripsi',
    'symptoms' => 'Gejala',
    'cause' => 'Penyebab',
    'treatment' => 'Penanganan',
    'prevention' => 'Pencegahan',
] as $field => $label)
    <label class="block text-sm font-medium text-stone-700">
        {{ $label }}
        <textarea name="{{ $field }}" rows="4" class="mt-1 block w-full rounded-md border-stone-300 px-3 py-2 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">{{ old($field, $disease->{$field}) }}</textarea>
    </label>
@endforeach

<label class="flex items-center gap-2 text-sm font-medium text-stone-700">
    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $disease->is_active)) class="rounded border-stone-300 text-emerald-700 focus:ring-emerald-600">
    Aktif
</label>

<div class="flex flex-wrap items-center gap-3">
    <button type="submit" class="rounded-md bg-emerald-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-800">
        Simpan
    </button>
    <a href="{{ route('admin.diseases.index') }}" class="rounded-md border border-stone-300 px-4 py-2.5 text-sm font-semibold text-stone-700 hover:bg-stone-100">
        Batal
    </a>
</div>
