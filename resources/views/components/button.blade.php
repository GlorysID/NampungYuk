@props([
    'variant' => 'primary', // 'primary', 'secondary', 'soft', 'ghost', 'danger'
    'size' => 'md', // 'sm', 'md', 'lg'
    'href' => null,
    'type' => 'button',
])

@php
    $baseClass = 'inline-flex items-center justify-center font-semibold transition cursor-pointer select-none active:scale-[0.98] disabled:opacity-50 disabled:pointer-events-none rounded-xl';

    $sizeClasses = [
        'sm' => 'text-xs px-3.5 py-2 gap-1.5',
        'md' => 'text-sm px-4.5 py-2.5 gap-2',
        'lg' => 'text-base px-6 py-3 gap-2.5',
    ][$size] ?? 'text-sm px-4.5 py-2.5 gap-2';

    $variantClasses = [
        'primary' => 'bg-[var(--primary)] hover:bg-[var(--primary-hover)] text-[var(--primary-text)] font-bold border border-transparent shadow-xs',
        'secondary' => 'bg-[var(--surface-muted)] hover:bg-[var(--surface-chip-hover)] text-[var(--primary)] border border-[var(--border-color)]',
        'soft' => 'bg-[var(--surface-hover)] text-[var(--primary)] border border-[var(--border-strong)]',
        'ghost' => 'bg-transparent text-[var(--text-muted)] hover:bg-[var(--surface-muted)] hover:text-[var(--primary)]',
        'danger' => 'bg-rose-600 hover:bg-rose-700 text-white border border-transparent shadow-xs',
    ][$variant] ?? 'bg-[var(--primary)] hover:bg-[var(--primary-hover)] text-[var(--primary-text)] font-bold border border-transparent shadow-xs';
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => "$baseClass $sizeClasses $variantClasses"]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => "$baseClass $sizeClasses $variantClasses"]) }}>
        {{ $slot }}
    </button>
@endif
