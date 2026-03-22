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
            'password' => ['required', 'confirmed', Password::defaults()],
            'is_admin' => ['sometimes', 'boolean'],
            'avatar' => ['nullable', 'image', 'max:2048'],
        ]);

        $user = User::query()->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
        ]);
        $user->forceFill(['is_admin' => $request->boolean('is_admin')])->save();

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
            'password' => ['nullable', 'confirmed', Password::defaults()],
            'is_admin' => ['sometimes', 'boolean'],
            'avatar' => ['nullable', 'image', 'max:2048'],
            'remove_avatar' => ['sometimes', 'boolean'],
        ]);

        $willBeAdmin = $request->boolean('is_admin');

        if ($user->is_admin && ! $willBeAdmin && $this->adminCountExcluding($user->id) === 0) {
            return back()
                ->withInput()
                ->withErrors(['is_admin' => 'You cannot remove admin access from the only administrator.']);
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
        ]);

        if (! empty($validated['password'])) {
            $user->password = $validated['password'];
        }

        $user->save();
        $user->forceFill(['is_admin' => $willBeAdmin])->save();

        return redirect()
            ->route('admin.users.show', $user)
            ->with('success', 'User updated.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->is_admin && User::query()->where('is_admin', true)->count() <= 1) {
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

    private function adminCountExcluding(int $userId): int
    {
        return User::query()->where('is_admin', true)->where('id', '!=', $userId)->count();
    }

    private function deleteStoredAvatar(User $user): void
    {
        if ($user->avatar_path === null || $user->avatar_path === '') {
            return;
        }

        Storage::disk('public')->delete($user->avatar_path);
    }
}
