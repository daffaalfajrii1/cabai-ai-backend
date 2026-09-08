@extends('admin.layouts.app', ['title' => 'Laporan'])

@section('content')
    <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold tracking-normal text-stone-950">Laporan</h1>
            <p class="mt-1 text-sm text-stone-600">Filter detection dan export CSV.</p>
        </div>
        <a href="{{ route('admin.reports.export', request()->query()) }}" class="rounded-md bg-emerald-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-800">Export CSV</a>
    </div>

    <form method="GET" class="mb-6 grid gap-3 rounded-lg border border-stone-200 bg-white p-4 shadow-sm md:grid-cols-4">
        <label class="block text-sm font-medium text-stone-700">
            Tanggal Mulai
            <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="mt-1 block w-full rounded-md border-stone-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
        </label>
        <label class="block text-sm font-medium text-stone-700">
            Tanggal Akhir
            <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="mt-1 block w-full rounded-md border-stone-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
        </label>
        <label class="block text-sm font-medium text-stone-700">
            Penyakit
            <select name="disease_id" class="mt-1 block w-full rounded-md border-stone-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                <option value="">Semua</option>
                @foreach ($diseases as $disease)
                    <option value="{{ $disease->id }}" @selected((string) ($filters['disease_id'] ?? '') === (string) $disease->id)>{{ $disease->name }}</option>
                @endforeach
            </select>
        </label>
        <label class="block text-sm font-medium text-stone-700">
            User
            <select name="user_id" class="mt-1 block w-full rounded-md border-stone-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                <option value="">Semua</option>
                @foreach ($users as $user)
                    <option value="{{ $user->id }}" @selected((string) ($filters['user_id'] ?? '') === (string) $user->id)>{{ $user->name }}</option>
                @endforeach
            </select>
        </label>
        <label class="block text-sm font-medium text-stone-700">
            Validitas
            <select name="validity" class="mt-1 block w-full rounded-md border-stone-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                <option value="">Semua</option>
                <option value="valid" @selected(($filters['validity'] ?? '') === 'valid')>Valid</option>
                <option value="invalid" @selected(($filters['validity'] ?? '') === 'invalid')>Invalid</option>
            </select>
        </label>
        <label class="block text-sm font-medium text-stone-700">
            Review Status
            <select name="review_status" class="mt-1 block w-full rounded-md border-stone-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                <option value="">Semua</option>
                @foreach ($reviewStatuses as $value => $label)
                    <option value="{{ $value }}" @selected(($filters['review_status'] ?? '') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </label>
        <label class="block text-sm font-medium text-stone-700">
            Classification Source
            <select name="classification_source" class="mt-1 block w-full rounded-md border-stone-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                <option value="">Semua</option>
                @foreach ($classificationSources as $source)
                    <option value="{{ $source }}" @selected(($filters['classification_source'] ?? '') === $source)>{{ $source }}</option>
                @endforeach
            </select>
        </label>
        <div class="flex items-end gap-2">
            <button type="submit" class="w-full rounded-md bg-emerald-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-800">Filter</button>
        </div>
    </form>

    <section class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ([
            'Total Deteksi' => number_format($summary['total']),
            'Valid' => number_format($summary['valid']),
            'Invalid' => number_format($summary['invalid']),
            'Needs Retake' => number_format($summary['needs_retake']),
            'Healthy' => number_format($summary['healthy']),
            'Disease' => number_format($summary['disease']),
            'Average Confidence' => $summary['average_confidence'] === null ? '-' : number_format((float) $summary['average_confidence'], 2).'%',
        ] as $label => $value)
            <article class="rounded-lg border border-stone-200 bg-white p-5 shadow-sm">
                <div class="text-sm font-medium text-stone-500">{{ $label }}</div>
                <div class="mt-2 text-3xl font-semibold tracking-normal text-stone-950">{{ $value }}</div>
            </article>
        @endforeach
    </section>

    <div class="rounded-lg border border-stone-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-stone-200 text-sm">
                <thead class="bg-stone-50 text-left text-xs font-semibold uppercase tracking-normal text-stone-500">
                    <tr>
                        <th class="px-5 py-3">Tanggal</th>
                        <th class="px-5 py-3">User</th>
                        <th class="px-5 py-3">AI Class</th>
                        <th class="px-5 py-3">Penyakit</th>
                        <th class="px-5 py-3">Confidence</th>
                        <th class="px-5 py-3">Valid</th>
                        <th class="px-5 py-3">Review</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse ($detections as $detection)
                        <tr>
                            <td class="px-5 py-3 text-stone-700">{{ $detection->created_at?->format('d M Y H:i') }}</td>
                            <td class="px-5 py-3 text-stone-700">{{ $detection->user?->name ?? 'Tanpa user' }}</td>
                            <td class="px-5 py-3 font-medium text-stone-950">{{ $detection->ai_class_name ?? '-' }}</td>
                            <td class="px-5 py-3 text-stone-700">{{ $detection->disease?->name ?? '-' }}</td>
                            <td class="px-5 py-3 text-stone-700">{{ $detection->confidence_percent === null ? '-' : number_format((float) $detection->confidence_percent, 2).'%' }}</td>
                            <td class="px-5 py-3 text-stone-700">{{ $detection->valid_input ? 'Valid' : 'Invalid' }}</td>
                            <td class="px-5 py-3">
                                <a href="{{ route('admin.detections.show', $detection) }}" class="font-medium text-emerald-800 hover:text-emerald-950">{{ $detection->reviewStatusLabel() }}</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-8 text-center text-stone-500">Tidak ada data laporan.</td>
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
