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
        'primary' => 'bg-[#0F766E] hover:bg-[#115E59] text-white border border-transparent shadow-xs',
        'secondary' => 'bg-white dark:bg-[#151D1B] hover:bg-[#F0F4F2] dark:hover:bg-[#1B2623] text-[#17211F] dark:text-[#F2F5F4] border border-[#DDE5E2] dark:border-[#24322F]',
        'soft' => 'bg-[#CCFBF1] dark:bg-teal-950/40 text-[#0F766E] dark:text-teal-300 hover:bg-[#99F6E4] dark:hover:bg-teal-900/50 border border-teal-200/50 dark:border-teal-800/40',
        'ghost' => 'bg-transparent text-[#66736F] dark:text-[#8E9F9B] hover:bg-[#F0F4F2] dark:hover:bg-[#1B2623] hover:text-[#17211F] dark:hover:text-white',
        'danger' => 'bg-rose-600 hover:bg-rose-700 text-white border border-transparent shadow-xs',
    ][$variant] ?? 'bg-[#0F766E] hover:bg-[#115E59] text-white';
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
