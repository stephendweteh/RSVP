@extends('layouts.admin')

@section('title', 'RSVP title — Admin')

@section('content')
    <h1 class="h4 mb-3">RSVP title</h1>
    <p class="text-muted small mb-4">These appear at the top of the public RSVP form. All admins can change them.</p>

    <div class="card shadow-sm" style="max-width: 36rem;">
        <div class="card-body">
            <form action="{{ route('admin.rsvp-title.update') }}" method="post">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label for="title" class="form-label">Headline</label>
                    <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title', $title) }}" required maxlength="255">
                    @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-4">
                    <label for="subtitle" class="form-label">Subtitle</label>
                    <textarea class="form-control @error('subtitle') is-invalid @enderror" id="subtitle" name="subtitle" rows="2" maxlength="500">{{ old('subtitle', $subtitle) }}</textarea>
                    @error('subtitle')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <p class="form-text small mb-0">Shown below the headline in smaller text. Leave blank if you prefer no subtitle.</p>
                </div>
                <button type="submit" class="btn btn-primary">Save</button>
            </form>
        </div>
    </div>
@endsection
