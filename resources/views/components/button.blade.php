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
        'primary' => 'bg-[#0B0B0D] hover:bg-[#222225] text-[#EAE8E1] dark:bg-[#EAE8E1] dark:hover:bg-[#DCD9D0] dark:text-[#0B0B0D] font-bold border border-transparent shadow-xs',
        'secondary' => 'bg-[#EFECE6] dark:bg-[#161619] hover:bg-[#E2DFD7] dark:hover:bg-[#19191D] text-[#0B0B0D] dark:text-[#EAE8E1] border border-[#E2DFD7] dark:border-[#242426]',
        'soft' => 'bg-[#EFECE6] text-[#0B0B0D] dark:bg-[#19191D] dark:text-[#EAE8E1] border border-[#D0CDC3] dark:border-[#333336]',
        'ghost' => 'bg-transparent text-[#6E6D67] dark:text-[#8E8D86] hover:bg-[#EFECE6] dark:hover:bg-[#161619] hover:text-[#0B0B0D] dark:hover:text-[#EAE8E1]',
        'danger' => 'bg-rose-600 hover:bg-rose-700 text-white border border-transparent shadow-xs',
    ][$variant] ?? 'bg-[#0B0B0D] hover:bg-[#222225] text-[#EAE8E1] dark:bg-[#EAE8E1] dark:hover:bg-[#DCD9D0] dark:text-[#0B0B0D] font-bold border border-transparent shadow-xs';
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
