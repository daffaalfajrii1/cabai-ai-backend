@extends('admin.layouts.app', ['title' => 'Users'])

@section('content')
    <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold tracking-normal text-stone-950">Users</h1>
            <p class="mt-1 text-sm text-stone-600">Kelola akun admin dan user mobile.</p>
        </div>
    </div>

    <form method="GET" class="mb-5 grid gap-3 rounded-lg border border-stone-200 bg-white p-4 shadow-sm md:grid-cols-[1fr_160px_160px_auto]">
        <input name="search" value="{{ $filters['search'] }}" placeholder="Cari nama atau email" class="rounded-md border-stone-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
        <select name="role" class="rounded-md border-stone-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
            <option value="all" @selected($filters['role'] === 'all')>Semua role</option>
            <option value="admin" @selected($filters['role'] === 'admin')>Admin</option>
            <option value="user" @selected($filters['role'] === 'user')>User</option>
        </select>
        <select name="status" class="rounded-md border-stone-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
            <option value="all" @selected($filters['status'] === 'all')>Semua status</option>
            <option value="active" @selected($filters['status'] === 'active')>Aktif</option>
            <option value="inactive" @selected($filters['status'] === 'inactive')>Nonaktif</option>
        </select>
        <button type="submit" class="rounded-md bg-emerald-700 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-800">Filter</button>
    </form>

    <div class="rounded-lg border border-stone-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-stone-200 text-sm">
                <thead class="bg-stone-50 text-left text-xs font-semibold uppercase tracking-normal text-stone-500">
                    <tr>
                        <th class="px-5 py-3">Nama</th>
                        <th class="px-5 py-3">Email</th>
                        <th class="px-5 py-3">Role</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3">Registrasi</th>
                        <th class="px-5 py-3">Deteksi</th>
                        <th class="px-5 py-3">Terakhir Deteksi</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse ($users as $user)
                        <tr>
                            <td class="px-5 py-3 font-medium text-stone-950">{{ $user->name }}</td>
                            <td class="px-5 py-3 text-stone-700">{{ $user->email }}</td>
                            <td class="px-5 py-3 text-stone-700">{{ ucfirst($user->role) }}</td>
                            <td class="px-5 py-3">
                                <span class="rounded-md px-2 py-1 text-xs font-semibold {{ $user->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-stone-700">{{ $user->created_at?->format('d M Y') }}</td>
                            <td class="px-5 py-3 text-stone-700">{{ $user->detections_count }}</td>
                            <td class="px-5 py-3 text-stone-700">{{ $user->detections_max_created_at ? \Illuminate\Support\Carbon::parse($user->detections_max_created_at)->format('d M Y H:i') : '-' }}</td>
                            <td class="px-5 py-3 text-right">
                                <a href="{{ route('admin.users.show', $user) }}" class="font-medium text-emerald-800 hover:text-emerald-950">Detail</a>
                                <span class="mx-1 text-stone-300">/</span>
                                <a href="{{ route('admin.users.edit', $user) }}" class="font-medium text-emerald-800 hover:text-emerald-950">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-8 text-center text-stone-500">User tidak ditemukan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-stone-200 px-5 py-4">
            {{ $users->links() }}
        </div>
    </div>
@endsection
