@extends('layouts.admin')

@section('title', 'Edit email — '.config('app.name'))

@section('content')
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.settings.edit') }}#email-templates">Settings</a></li>
            <li class="breadcrumb-item active" aria-current="page">Email content</li>
        </ol>
    </nav>

    <h1 class="h4 mb-2">{{ $mailTemplate->name }}</h1>
    <p class="text-muted small font-monospace mb-1">Slug: {{ $mailTemplate->slug }}</p>
    @if (filled($mailTemplate->description))
        <p class="text-muted small mb-4">{{ $mailTemplate->description }}</p>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <form action="{{ route('admin.mail-templates.update', $mailTemplate) }}" method="post">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label for="name" class="form-label">Label</label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $mailTemplate->name) }}" required maxlength="255">
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Placeholder notes <span class="text-muted">(optional, for admins)</span></label>
                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="2" maxlength="2000">{{ old('description', $mailTemplate->description) }}</textarea>
                    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label for="subject" class="form-label">Subject</label>
                    <input type="text" class="form-control @error('subject') is-invalid @enderror" id="subject" name="subject" value="{{ old('subject', $mailTemplate->subject) }}" required maxlength="255">
                    @error('subject')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label for="body_html" class="form-label">HTML body</label>
                    <textarea class="form-control font-monospace small @error('body_html') is-invalid @enderror" id="body_html" name="body_html" rows="16" required>{{ old('body_html', $mailTemplate->body_html) }}</textarea>
                    @error('body_html')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-4">
                    <label for="body_text" class="form-label">Plain-text body</label>
                    <textarea class="form-control font-monospace small @error('body_text') is-invalid @enderror" id="body_text" name="body_text" rows="12">{{ old('body_text', $mailTemplate->body_text) }}</textarea>
                    @error('body_text')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <button type="submit" class="btn btn-primary">Save template</button>
                    <a href="{{ route('admin.settings.edit') }}#email-templates" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>

            <hr class="my-4">

            <p class="small text-muted mb-2">Restore the original subject and bodies shipped with the app.</p>
            <form action="{{ route('admin.mail-templates.reset', $mailTemplate) }}" method="post" class="d-inline" onsubmit="return confirm('Reset this template to defaults? Your edits will be lost.');">
                @csrf
                <button type="submit" class="btn btn-outline-danger btn-sm">Reset to default</button>
            </form>
        </div>
    </div>
@endsection
