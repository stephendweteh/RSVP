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
    <label for="password" class="form-label">Password @if($editing)<span class="text-muted small">(leave blank to keep current)</span>@endif</label>
    <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" autocomplete="new-password" @if(! $editing) required @endif>
    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
<div class="mb-3">
    <label for="password_confirmation" class="form-label">Confirm password</label>
    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" autocomplete="new-password" @if(! $editing) required @endif>
</div>
<div class="form-check mb-3">
    <input type="checkbox" class="form-check-input @error('is_admin') is-invalid @enderror" id="is_admin" name="is_admin" value="1" @checked(old('is_admin', $editing && $user->is_admin))>
    <label class="form-check-label" for="is_admin">Administrator (can access this dashboard)</label>
    @error('is_admin')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
</div>
