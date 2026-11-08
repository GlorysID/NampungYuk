@props([
    'variant' => 'neutral', // 'brand', 'teal', 'neutral', 'success', 'warning', 'danger', 'purple', 'cyan'
    'size' => 'sm',
])

@php
    $sizeClass = [
        'xs' => 'text-[10px] px-2 py-0.5 rounded-full',
        'sm' => 'text-[11px] px-2.5 py-0.5 rounded-full font-semibold',
        'md' => 'text-xs px-3 py-1 rounded-full font-semibold',
    ][$size] ?? 'text-[11px] px-2.5 py-0.5 rounded-full font-semibold';

    $variantClass = [
        'brand' => 'bg-[#0B0B0D] dark:bg-[#EAE8E1] text-[#EAE8E1] dark:text-[#0B0B0D] font-bold border border-transparent',
        'teal' => 'bg-emerald-500/10 dark:bg-emerald-500/15 text-emerald-700 dark:text-emerald-300 border border-emerald-500/30',
        'neutral' => 'bg-[#EFECE6] dark:bg-[#161619] text-[#0B0B0D] dark:text-[#EAE8E1] border border-[#E2DFD7] dark:border-[#242426]',
        'success' => 'bg-emerald-500/10 dark:bg-emerald-500/15 text-emerald-700 dark:text-emerald-300 border border-emerald-500/30',
        'warning' => 'bg-amber-500/10 dark:bg-amber-500/15 text-amber-700 dark:text-amber-300 border border-amber-500/30',
        'danger' => 'bg-rose-500/10 dark:bg-rose-500/15 text-rose-700 dark:text-rose-300 border border-rose-500/30',
        'purple' => 'bg-purple-500/10 dark:bg-purple-500/15 text-purple-700 dark:text-purple-300 border border-purple-500/30',
        'cyan' => 'bg-[#EFECE6] dark:bg-[#161619] text-[#0B0B0D] dark:text-[#EAE8E1] border border-[#E2DFD7] dark:border-[#242426]',
    ][$variant] ?? 'bg-[#EFECE6] dark:bg-[#161619] text-[#0B0B0D] dark:text-[#EAE8E1] border border-[#E2DFD7] dark:border-[#242426]';
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1.5 select-none tracking-tight $sizeClass $variantClass"]) }}>
    {{ $slot }}
</span>
