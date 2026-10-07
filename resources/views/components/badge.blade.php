@props([
    'variant' => 'neutral', // 'teal', 'neutral', 'success', 'warning', 'danger', 'purple'
    'size' => 'sm',
])

@php
    $sizeClass = [
        'xs' => 'text-[10px] px-2 py-0.5 rounded',
        'sm' => 'text-[11px] px-2.5 py-0.5 rounded-md font-medium',
        'md' => 'text-xs px-3 py-1 rounded-lg font-medium',
    ][$size] ?? 'text-[11px] px-2.5 py-0.5 rounded-md font-medium';

    $variantClass = [
        'teal' => 'bg-[#d9fbf4] dark:bg-[#50d2c1]/12 text-[#0e9c8b] dark:text-[#6ee7d5] border border-[#50d2c1]/30',
        'neutral' => 'bg-[#e6eaee] dark:bg-[#1e2530] text-[#10161f] dark:text-[#eaecf0] border border-[#d5dbe2] dark:border-[#262d3a]',
        'success' => 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border border-emerald-200/60 dark:border-emerald-800/40',
        'warning' => 'bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 border border-amber-200/60 dark:border-amber-800/40',
        'danger' => 'bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 border border-rose-200/60 dark:border-rose-800/40',
        'purple' => 'bg-purple-50 dark:bg-purple-950/40 text-purple-700 dark:text-purple-300 border border-purple-200/60 dark:border-purple-800/40',
    ][$variant] ?? 'bg-[#e6eaee] text-[#10161f]';
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1.5 select-none font-mono tracking-tight $sizeClass $variantClass"]) }}>
    {{ $slot }}
</span>
