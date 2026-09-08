@extends('admin.layouts.app', ['title' => 'Audit Log'])

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-semibold tracking-normal text-stone-950">Audit Log</h1>
        <p class="mt-1 text-sm text-stone-600">Aktivitas penting admin.</p>
    </div>

    <div class="rounded-lg border border-stone-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-stone-200 text-sm">
                <thead class="bg-stone-50 text-left text-xs font-semibold uppercase tracking-normal text-stone-500">
                    <tr>
                        <th class="px-5 py-3">Waktu</th>
                        <th class="px-5 py-3">Admin</th>
                        <th class="px-5 py-3">Action</th>
                        <th class="px-5 py-3">Subject</th>
                        <th class="px-5 py-3">Deskripsi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse ($logs as $log)
                        <tr>
                            <td class="px-5 py-3 text-stone-700">{{ $log->created_at?->format('d M Y H:i:s') }}</td>
                            <td class="px-5 py-3 text-stone-700">{{ $log->user?->email ?? 'System' }}</td>
                            <td class="px-5 py-3 font-medium text-stone-950">{{ $log->action }}</td>
                            <td class="px-5 py-3 text-stone-700">{{ class_basename($log->subject_type) }} #{{ $log->subject_id }}</td>
                            <td class="px-5 py-3 text-stone-700">{{ $log->description ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-8 text-center text-stone-500">Belum ada audit log.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-stone-200 px-5 py-4">
            {{ $logs->links() }}
        </div>
    </div>
@endsection
