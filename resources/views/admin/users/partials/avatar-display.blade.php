@php
    $size = $size ?? 48;
    $class = $class ?? '';
@endphp
@if (filled($user->avatar_path))
    <img src="{{ $user->avatarUrl() }}" alt="{{ $user->name }}" width="{{ $size }}" height="{{ $size }}" class="rounded-circle flex-shrink-0 {{ $class }}" style="width: {{ $size }}px; height: {{ $size }}px; object-fit: cover;" loading="lazy" decoding="async">
@else
    <span class="rounded-circle bg-secondary text-white d-inline-flex align-items-center justify-content-center fw-semibold {{ $class }}" style="width: {{ $size }}px; height: {{ $size }}px; font-size: {{ max(12, round($size / 3)) }}px;">{{ $user->avatarInitials() }}</span>
@endif
