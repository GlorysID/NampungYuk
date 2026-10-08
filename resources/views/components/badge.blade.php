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
        'brand' => 'bg-[#e8f2ff] dark:bg-[#3291ff]/16 text-[#0761d1] dark:text-[#47a8ff] border border-[#3291ff]/25',
        'teal' => 'bg-[#e8f2ff] dark:bg-[#3291ff]/16 text-[#0761d1] dark:text-[#47a8ff] border border-[#3291ff]/25',
        'neutral' => 'bg-[#f2f2f2] dark:bg-[#171717] text-[#000000] dark:text-[#fafafa] border border-[#e2e2e2] dark:border-[#1f1f1f]',
        'success' => 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border border-emerald-200/60 dark:border-emerald-800/40',
        'warning' => 'bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 border border-amber-200/60 dark:border-amber-800/40',
        'danger' => 'bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 border border-rose-200/60 dark:border-rose-800/40',
        'purple' => 'bg-violet-50 dark:bg-violet-950/40 text-violet-700 dark:text-violet-300 border border-violet-200/60 dark:border-violet-800/40',
        'cyan' => 'bg-cyan-50 dark:bg-cyan-950/40 text-cyan-700 dark:text-cyan-300 border border-cyan-200/60 dark:border-cyan-800/40',
    ][$variant] ?? 'bg-[#f2f2f2] dark:bg-[#171717] text-[#000000] dark:text-[#fafafa] border border-[#e2e2e2] dark:border-[#1f1f1f]';
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1.5 select-none tracking-tight $sizeClass $variantClass"]) }}>
    {{ $slot }}
</span>
