@extends('admin.layouts.app', ['title' => 'Edit User'])

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-semibold tracking-normal text-stone-950">Edit User</h1>
        <p class="mt-1 text-sm text-stone-600">{{ $user->email }}</p>
    </div>

    <form method="POST" action="{{ route('admin.users.update', $user) }}" class="max-w-3xl space-y-5 rounded-lg border border-stone-200 bg-white p-6 shadow-sm">
        @csrf
        @method('PUT')

        @if ($errors->any())
            <div class="rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                {{ $errors->first() }}
            </div>
        @endif

        <label class="block text-sm font-medium text-stone-700">
            Nama
            <input name="name" value="{{ old('name', $user->name) }}" required class="mt-1 block w-full rounded-md border-stone-300 px-3 py-2 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
        </label>

        <label class="block text-sm font-medium text-stone-700">
            Email
            <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="mt-1 block w-full rounded-md border-stone-300 px-3 py-2 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
        </label>

        <label class="block text-sm font-medium text-stone-700">
            Role
            <select name="role" required class="mt-1 block w-full rounded-md border-stone-300 px-3 py-2 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                <option value="user" @selected(old('role', $user->role) === 'user')>User</option>
                <option value="admin" @selected(old('role', $user->role) === 'admin')>Admin</option>
            </select>
        </label>

        <label class="flex items-center gap-2 text-sm font-medium text-stone-700">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $user->is_active)) class="rounded border-stone-300 text-emerald-700 focus:ring-emerald-600">
            Aktif
        </label>

        <div class="grid gap-4 sm:grid-cols-2">
            <label class="block text-sm font-medium text-stone-700">
                Password Baru
                <input type="password" name="password" class="mt-1 block w-full rounded-md border-stone-300 px-3 py-2 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
            </label>

            <label class="block text-sm font-medium text-stone-700">
                Konfirmasi Password
                <input type="password" name="password_confirmation" class="mt-1 block w-full rounded-md border-stone-300 px-3 py-2 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
            </label>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <button type="submit" class="rounded-md bg-emerald-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-800">
                Simpan
            </button>
            <a href="{{ route('admin.users.show', $user) }}" class="rounded-md border border-stone-300 px-4 py-2.5 text-sm font-semibold text-stone-700 hover:bg-stone-100">
                Batal
            </a>
        </div>
    </form>
@endsection
