@props([
    'variant' => 'primary', // 'primary', 'secondary', 'soft', 'ghost', 'danger'
    'size' => 'md', // 'sm', 'md', 'lg'
    'href' => null,
    'type' => 'button',
])

@php
    $baseClass = 'inline-flex items-center justify-center font-semibold transition cursor-pointer select-none active:scale-[0.97] disabled:opacity-50 disabled:pointer-events-none';

    $sizeClasses = [
        'sm' => 'text-xs px-3.5 py-1.5 rounded-full gap-1.5',
        'md' => 'text-sm px-4 py-2.5 rounded-full gap-2',
        'lg' => 'text-base px-5 py-3 rounded-full gap-2',
    ][$size] ?? 'text-sm px-4 py-2.5 rounded-full gap-2';

    $variantClasses = [
        'primary' => 'btn-primary',
        'secondary' => 'bg-[#eeeeef] dark:bg-[#171717] hover:bg-[#f5f5f5] dark:hover:bg-[#111111] text-[#18181b] dark:text-[#fafafa] border border-[#e4e4e7] dark:border-[#1f1f1f] hover:border-[#d8d8d8] dark:hover:border-[#3a4270] hover:text-[#0070f3] dark:hover:text-[#47a8ff]',
        'soft' => 'bg-[#e8f2ff] dark:bg-[#3291ff]/16 text-[#0761d1] dark:text-[#47a8ff] hover:bg-[#d0e7ff] dark:hover:bg-[#3291ff]/24 border border-[#3291ff]/25',
        'ghost' => 'bg-transparent text-[#63636b] dark:text-[#a0a0a0] hover:bg-[#f5f5f5] dark:hover:bg-[#111111] hover:text-[#18181b] dark:hover:text-white',
        'danger' => 'bg-rose-600 hover:bg-rose-700 text-white border border-transparent',
    ][$variant] ?? 'btn-primary';
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
