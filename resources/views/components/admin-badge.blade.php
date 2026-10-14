@props([
    'size' => 'sm', // 'xs', 'sm'
    'label' => 'Admin',
])

@php
    $sizeClass = $size === 'xs'
        ? 'text-[9px] px-1.5 py-0.5 gap-0.5'
        : 'text-[10px] px-2 py-0.5 gap-1';
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center font-bold uppercase tracking-wider rounded-full select-none
    bg-gradient-to-r from-violet-600 to-fuchsia-600 text-white shadow-[0_2px_8px_-2px_rgba(139,92,246,0.7)] ring-1 ring-white/20 $sizeClass"]) }}
      title="Administrator NampungYuk">
    <svg class="{{ $size === 'xs' ? 'w-2.5 h-2.5' : 'w-3 h-3' }} shrink-0" fill="currentColor" viewBox="0 0 24 24">
        <path d="M12 2l7 3v6c0 4.418-2.985 8.5-7 11-4.015-2.5-7-6.582-7-11V5l7-3z"/>
        <path d="M9.5 12l1.8 1.8 3.6-3.6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
    </svg>
    <span>{{ $label }}</span>
</span>
