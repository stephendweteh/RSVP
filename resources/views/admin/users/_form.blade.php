@php
    $editing = isset($user);
@endphp
@if ($editing && $user->avatar_path)
    <div class="mb-3">
        <label class="form-label d-block">Current photo</label>
        @include('admin.users.partials.avatar-display', ['user' => $user, 'size' => 96])
    </div>
@endif
<div class="mb-3">
    <label for="avatar" class="form-label">Profile picture <span class="text-muted small">(optional, max 2&nbsp;MB)</span></label>
    <input type="file" class="form-control @error('avatar') is-invalid @enderror" id="avatar" name="avatar" accept="image/jpeg,image/png,image/gif,image/webp">
    @error('avatar')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
@if ($editing && $user->avatar_path)
    <div class="form-check mb-3">
        <input type="checkbox" class="form-check-input" id="remove_avatar" name="remove_avatar" value="1">
        <label class="form-check-label text-danger" for="remove_avatar">Remove profile picture</label>
    </div>
@endif
<div class="mb-3">
    <label for="name" class="form-label">Name</label>
    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $user->name ?? '') }}" required>
    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
<div class="mb-3">
    <label for="email" class="form-label">Email</label>
    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $user->email ?? '') }}" required autocomplete="email">
    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
<div class="mb-3">
    <label for="phone" class="form-label">Phone <span class="text-muted small">(optional — for admin SMS alerts)</span></label>
    <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone', $user->phone ?? '') }}" maxlength="30" autocomplete="tel" placeholder="e.g. 0550123456">
    @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
<div class="mb-3">
    <label for="password" class="form-label">Password @if($editing)<span class="text-muted small">(leave blank to keep current)</span>@endif</label>
    <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" autocomplete="new-password" @if(! $editing) required @endif>
    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
<div class="mb-3">
    <label for="password_confirmation" class="form-label">Confirm password</label>
    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" autocomplete="new-password" @if(! $editing) required @endif>
</div>
@php
    $staffRole = old('staff_role', $editing
        ? ($user->is_admin ? ($user->isManager() ? 'manager' : 'administrator') : 'user')
        : 'user');
    $canEditRoles = auth()->user()->isAdministrator();
@endphp
@if ($canEditRoles)
    <div class="mb-3">
        <label for="staff_role" class="form-label">Dashboard access</label>
        <select class="form-select @error('staff_role') is-invalid @enderror" id="staff_role" name="staff_role">
            <option value="user" @selected($staffRole === 'user')>User (no dashboard)</option>
            <option value="manager" @selected($staffRole === 'manager')>Manager</option>
            <option value="administrator" @selected($staffRole === 'administrator')>Administrator</option>
        </select>
        @error('staff_role')<div class="invalid-feedback">{{ $message }}</div>@enderror
        <div class="form-text">Managers can use RSVPs, slider, and users, but not Settings.</div>
    </div>
@else
    <p class="small text-muted mb-3">
        @if ($editing)
            Dashboard access: <strong>{{ $user->staffRoleLabel() }}</strong>. Only an administrator can change roles.
        @else
            New accounts are created as <strong>User</strong> (no dashboard). Only an administrator can assign Manager or Administrator.
        @endif
    </p>
@endif
