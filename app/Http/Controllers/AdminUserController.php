<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    public function index(): View
    {
        $users = User::query()->orderBy('name')->paginate(15);

        return view('admin.users.index', compact('users'));
    }

    public function create(): View
    {
        return view('admin.users.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'staff_role' => ['nullable', 'in:user,manager,administrator'],
            'avatar' => ['nullable', 'image', 'max:2048'],
        ]);

        $staff = $this->resolveStaffAttributes($request, null);

        $user = User::query()->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => self::nullablePhone($validated['phone'] ?? null),
            'password' => $validated['password'],
            'is_admin' => $staff['is_admin'],
            'admin_role' => $staff['admin_role'],
        ]);

        if ($request->hasFile('avatar')) {
            $user->forceFill([
                'avatar_path' => $request->file('avatar')->store('avatars', 'public'),
            ])->save();
        }

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User created.');
    }

    public function show(User $user): View
    {
        return view('admin.users.show', compact('user'));
    }

    public function edit(User $user): View
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['nullable', 'confirmed', Password::defaults()],
            'staff_role' => ['nullable', 'in:user,manager,administrator'],
            'avatar' => ['nullable', 'image', 'max:2048'],
            'remove_avatar' => ['sometimes', 'boolean'],
        ]);

        $staff = $this->resolveStaffAttributes($request, $user);

        if ($user->isAdministrator() && ! $this->wouldBeAdministrator($staff)) {
            if (User::countAdministratorsExcluding($user->id) === 0) {
                return back()
                    ->withInput()
                    ->withErrors(['staff_role' => 'You cannot remove or demote the only administrator.']);
            }
        }

        if ($request->boolean('remove_avatar')) {
            $this->deleteStoredAvatar($user);
            $user->avatar_path = null;
        }

        if ($request->hasFile('avatar')) {
            $this->deleteStoredAvatar($user);
            $user->avatar_path = $request->file('avatar')->store('avatars', 'public');
        }

        $user->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => self::nullablePhone($validated['phone'] ?? null),
            'is_admin' => $staff['is_admin'],
            'admin_role' => $staff['admin_role'],
        ]);

        if (! empty($validated['password'])) {
            $user->password = $validated['password'];
        }

        $user->save();

        return redirect()
            ->route('admin.users.show', $user)
            ->with('success', 'User updated.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->isAdministrator() && User::countAdministratorsExcluding($user->id) === 0) {
            return redirect()
                ->route('admin.users.index')
                ->withErrors(['delete' => 'You cannot delete the only administrator.']);
        }

        if ($user->id === auth()->id()) {
            return redirect()
                ->route('admin.users.index')
                ->withErrors(['delete' => 'You cannot delete your own account while signed in.']);
        }

        $this->deleteStoredAvatar($user);
        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User deleted.');
    }

    /**
     * @return array{is_admin: bool, admin_role: string|null}
     */
    private function resolveStaffAttributes(Request $request, ?User $existing): array
    {
        $actor = auth()->user();
        if (! $actor instanceof User || ! $actor->isAdministrator()) {
            if ($existing === null) {
                return ['is_admin' => false, 'admin_role' => null];
            }

            return [
                'is_admin' => (bool) $existing->is_admin,
                'admin_role' => $existing->admin_role,
            ];
        }

        $role = $request->input('staff_role');
        if ($role === null && $existing !== null) {
            $role = $existing->is_admin
                ? ($existing->isManager() ? 'manager' : 'administrator')
                : 'user';
        }
        $role ??= 'user';

        return match ($role) {
            'manager' => ['is_admin' => true, 'admin_role' => User::ADMIN_ROLE_MANAGER],
            'administrator' => ['is_admin' => true, 'admin_role' => User::ADMIN_ROLE_ADMIN],
            default => ['is_admin' => false, 'admin_role' => null],
        };
    }

    /**
     * @param  array{is_admin: bool, admin_role: string|null}  $staff
     */
    private function wouldBeAdministrator(array $staff): bool
    {
        if (! $staff['is_admin']) {
            return false;
        }

        $role = $staff['admin_role'];

        return $role === null || $role === User::ADMIN_ROLE_ADMIN;
    }

    private static function nullablePhone(mixed $value): ?string
    {
        $p = trim((string) ($value ?? ''));

        return $p !== '' ? $p : null;
    }

    private function deleteStoredAvatar(User $user): void
    {
        if ($user->avatar_path === null || $user->avatar_path === '') {
            return;
        }

        Storage::disk('public')->delete($user->avatar_path);
    }
}
