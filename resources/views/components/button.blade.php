@props([
    'variant' => 'primary', // 'primary', 'secondary', 'soft', 'ghost', 'danger'
    'size' => 'md', // 'sm', 'md', 'lg'
    'href' => null,
    'type' => 'button',
])

@php
    $baseClass = 'inline-flex items-center justify-center font-medium transition cursor-pointer select-none active:scale-[0.98] disabled:opacity-50 disabled:pointer-events-none';

    $sizeClasses = [
        'sm' => 'text-xs px-2.5 py-1.5 rounded-lg gap-1.5',
        'md' => 'text-xs sm:text-sm px-3.5 py-2 rounded-lg gap-2 font-semibold',
        'lg' => 'text-sm sm:text-base px-4 py-2.5 rounded-xl gap-2 font-semibold',
    ][$size] ?? 'text-xs px-3.5 py-2 rounded-lg gap-2 font-semibold';

    $variantClasses = [
        'primary' => 'bg-[#0e9c8b] hover:bg-[#0b7d70] text-[#04110f] border border-transparent',
        'secondary' => 'bg-white dark:bg-[#141821] hover:bg-[#f2f4f6] dark:hover:bg-[#1e2530] text-[#10161f] dark:text-[#eaecf0] border border-[#d5dbe2] dark:border-[#262d3a]',
        'soft' => 'bg-[#d9fbf4] dark:bg-[#50d2c1]/12 text-[#0e9c8b] dark:text-[#6ee7d5] hover:bg-[#c8fff4] dark:hover:bg-[#50d2c1]/20 border border-[#50d2c1]/30',
        'ghost' => 'bg-transparent text-[#5c6979] dark:text-[#7e8a9a] hover:bg-[#f2f4f6] dark:hover:bg-[#1e2530] hover:text-[#10161f] dark:hover:text-white',
        'danger' => 'bg-rose-600 hover:bg-rose-700 text-white border border-transparent',
    ][$variant] ?? 'bg-[#0e9c8b] hover:bg-[#0b7d70] text-[#04110f]';
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
