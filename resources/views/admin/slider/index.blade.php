@extends('layouts.admin')

@section('title', 'Slider — '.config('app.name'))

@section('content')
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <h1 class="h3 mb-0">Guest page slider</h1>
        <a href="{{ route('rsvp.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill" target="_blank" rel="noopener">Preview RSVP page</a>
    </div>
    <p class="text-muted small mb-4">Images appear in the carousel on the left side of the public RSVP form. Upload one or more at a time; use arrows to change order.</p>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <h2 class="h6 text-uppercase text-muted mb-3">Upload images</h2>
            <form action="{{ route('admin.slider.store') }}" method="post" enctype="multipart/form-data" class="row g-3 align-items-end">
                @csrf
                @php
                    $imagesInvalid = $errors->has('images') || collect($errors->keys())->contains(fn ($k) => str_starts_with((string) $k, 'images.'));
                @endphp
                <div class="col-md-8">
                    <label for="images" class="form-label">Choose images</label>
                    <input type="file" class="form-control @if($imagesInvalid) is-invalid @endif" id="images" name="images[]" accept="image/jpeg,image/png,image/gif,image/webp" multiple required>
                    @error('images')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    @foreach ($errors->keys() as $key)
                        @if (str_starts_with((string) $key, 'images.') && $key !== 'images')
                            @foreach ($errors->get($key) as $message)
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @endforeach
                        @endif
                    @endforeach
                    <div class="form-text">JPEG, PNG, GIF, or WebP. Max 5&nbsp;MB each.</div>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary rounded-pill w-100">Upload</button>
                </div>
            </form>
        </div>
    </div>

    <h2 class="h6 text-uppercase text-muted mb-3">Current slides ({{ $slides->count() }})</h2>
    @if ($slides->isEmpty())
        <p class="text-muted small">No slides yet. Upload images above.</p>
    @else
        <div class="row g-3">
            @foreach ($slides as $slide)
                <div class="col-sm-6 col-md-4 col-lg-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="ratio ratio-4x3 bg-light">
                            <img src="{{ $slide->url() }}" alt="" class="object-fit-cover rounded-top" style="object-fit: cover;">
                        </div>
                        <div class="card-body p-2 d-flex flex-wrap gap-1 justify-content-between align-items-center">
                            <span class="small text-muted">#{{ $loop->iteration }}</span>
                            <div class="btn-group btn-group-sm">
                                @if (! $loop->first)
                                    <form action="{{ route('admin.slider.move', $slide) }}" method="post" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="direction" value="up">
                                        <button type="submit" class="btn btn-outline-secondary" title="Move up">↑</button>
                                    </form>
                                @endif
                                @if (! $loop->last)
                                    <form action="{{ route('admin.slider.move', $slide) }}" method="post" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="direction" value="down">
                                        <button type="submit" class="btn btn-outline-secondary" title="Move down">↓</button>
                                    </form>
                                @endif
                                <form action="{{ route('admin.slider.destroy', $slide) }}" method="post" class="d-inline" onsubmit="return confirm('Remove this slide?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger">Remove</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
@endsection
