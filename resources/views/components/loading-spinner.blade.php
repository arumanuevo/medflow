@props(['text' => 'Guardando...', 'size' => 'md'])

@php
    $sizeClasses = [
        'sm' => 'spinner-border-sm',
        'md' => '',
        'lg' => 'spinner-border-lg'
    ];
    
    $sizeClass = $sizeClasses[$size] ?? '';
@endphp

<div class="d-flex align-items-center gap-2">
    <div class="spinner-border {{ $sizeClass }} text-primary" role="status">
        <span class="visually-hidden">{{ $text }}</span>
    </div>
    <span>{{ $text }}</span>
</div>