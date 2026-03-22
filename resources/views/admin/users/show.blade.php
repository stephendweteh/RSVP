@extends('layouts.admin')

@section('title', $user->name.' — '.config('app.name'))

@section('content')
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
        <div class="d-flex align-items-center gap-3">
            @include('admin.users.partials.avatar-display', ['user' => $user, 'size' => 88])
            <div>
                <h1 class="h3 mb-1">{{ $user->name }}</h1>
                <p class="text-muted small mb-0">{{ $user->email }}</p>
            </div>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-primary rounded-pill px-4">Edit</a>
            @if (auth()->user()->isAdministrator())
                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary rounded-pill px-4">All users</a>
            @endif
            @if (auth()->user()->isAdministrator() && $user->id !== auth()->id())
                <form action="{{ route('admin.users.destroy', $user) }}" method="post" onsubmit="return confirm('Delete this user permanently?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger rounded-pill px-4">Delete</button>
                </form>
            @endif
        </div>
    </div>
    <div class="card border-0 shadow-sm" style="max-width: 28rem;">
        <div class="card-body">
            <dl class="row small mb-0">
                @if (filled($user->phone))
                    <dt class="col-sm-4 text-muted">Phone</dt>
                    <dd class="col-sm-8">{{ $user->phone }}</dd>
                @endif
                <dt class="col-sm-4 text-muted">Role</dt>
                <dd class="col-sm-8">
                    @if ($user->is_admin)
                        @if ($user->isManager())
                            <span class="badge text-bg-info">Manager</span>
                        @else
                            <span class="badge text-bg-primary">Administrator</span>
                        @endif
                    @else
                        <span class="badge text-bg-secondary">User</span>
                    @endif
                </dd>
                <dt class="col-sm-4 text-muted">Joined</dt>
                <dd class="col-sm-8">{{ $user->created_at?->format('M j, Y g:i A') ?? '—' }}</dd>
                <dt class="col-sm-4 text-muted">Updated</dt>
                <dd class="col-sm-8">{{ $user->updated_at?->format('M j, Y g:i A') ?? '—' }}</dd>
            </dl>
        </div>
    </div>
@endsection
