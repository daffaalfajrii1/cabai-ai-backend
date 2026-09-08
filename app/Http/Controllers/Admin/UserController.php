<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\User;
use App\Support\AdminAudit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::query()
            ->withCount('detections')
            ->withMax('detections', 'created_at')
            ->latest();

        $search = trim((string) $request->query('search'));
        if ($search !== '') {
            $query->where(function ($query) use ($search): void {
                $query->where('name', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%');
            });
        }

        if (in_array($request->query('role'), ['admin', 'user'], true)) {
            $query->where('role', $request->query('role'));
        }

        if ($request->query('status') === 'active') {
            $query->where('is_active', true);
        }

        if ($request->query('status') === 'inactive') {
            $query->where('is_active', false);
        }

        return view('admin.users.index', [
            'users' => $query->paginate(15)->withQueryString(),
            'filters' => [
                'search' => $search,
                'role' => $request->query('role', 'all'),
                'status' => $request->query('status', 'all'),
            ],
        ]);
    }

    public function show(User $user): View
    {
        return view('admin.users.show', [
            'user' => $user->loadCount('detections')->load([
                'detections' => fn ($query) => $query->latest()->limit(8),
            ]),
        ]);
    }

    public function edit(User $user): View
    {
        return view('admin.users.edit', [
            'user' => $user,
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $validated = $request->validated();
        $validated['is_active'] = $request->boolean('is_active');

        $this->guardActiveAdminAccess($request->user(), $user, $validated);

        $user->fill([
            'name' => trim((string) $validated['name']),
            'email' => trim((string) $validated['email']),
            'role' => $validated['role'],
            'is_active' => $validated['is_active'],
        ]);

        if (! empty($validated['password'])) {
            $user->password = $validated['password'];
        }

        $dirtyFields = array_keys($user->getDirty());
        $user->save();

        if (! $user->isActive()) {
            $user->tokens()->delete();
        }

        AdminAudit::record(
            $request->user(),
            'admin.user.updated',
            $user,
            'User diperbarui: '.implode(', ', $dirtyFields ?: ['tanpa perubahan data'])
        );

        return redirect()
            ->route('admin.users.show', $user)
            ->with('status', 'User berhasil diperbarui.');
    }

    private function guardActiveAdminAccess(User $actor, User $user, array $data): void
    {
        $willBeActiveAdmin = $data['role'] === 'admin' && $data['is_active'] === true;

        if ($actor->is($user) && ! $willBeActiveAdmin) {
            throw ValidationException::withMessages([
                'is_active' => 'Admin tidak dapat menurunkan akses atau menonaktifkan akun sendiri.',
            ]);
        }

        if ($user->isAdmin() && ! $willBeActiveAdmin) {
            $hasOtherActiveAdmin = User::activeAdmins()
                ->where('id', '!=', $user->id)
                ->exists();

            if (! $hasOtherActiveAdmin) {
                throw ValidationException::withMessages([
                    'is_active' => 'Minimal harus ada satu admin aktif.',
                ]);
            }
        }
    }
}
