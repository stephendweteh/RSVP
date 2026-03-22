@extends('layouts.admin')

@section('title', 'Add user — '.config('app.name'))

@section('content')
    <h1 class="h3 mb-4">Add user</h1>
    <div class="card border-0 shadow-sm" style="max-width: 32rem;">
        <div class="card-body p-4">
            <form action="{{ route('admin.users.store') }}" method="post" enctype="multipart/form-data">
                @csrf
                @include('admin.users._form', ['user' => null])
                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Create user</button>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary rounded-pill px-4">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection
