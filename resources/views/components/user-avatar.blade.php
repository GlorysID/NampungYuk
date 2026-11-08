@props([
    'user' => null,
    'name' => null,
    'avatar' => null,
    'size' => 'md', // 'xs', 'sm', 'md', 'lg', 'xl'
])

@php
    $displayName = $user ? $user->name : ($name ?? 'Developer');
    $avatarUrl = $user ? ($user->avatar ?? null) : $avatar;

    $initial = mb_strtoupper(mb_substr(trim($displayName) !== '' ? trim($displayName) : 'D', 0, 1));

    // Deterministic soft background for the initial fallback.
    $palette = ['#0F766E', '#4F46E5', '#0891B2', '#7C3AED', '#DB2777', '#EA580C', '#059669', '#2563EB'];
    $bg = $palette[crc32($displayName) % count($palette)];

    $sizeClasses = [
        'xs' => 'w-6 h-6 rounded-full text-[10px]',
        'sm' => 'w-8 h-8 rounded-full text-xs',
        'md' => 'w-10 h-10 rounded-full text-sm font-semibold',
        'lg' => 'w-12 h-12 rounded-full text-base',
        'xl' => 'w-16 h-16 sm:w-20 sm:h-20 rounded-full text-2xl',
    ][$size] ?? 'w-10 h-10 rounded-full text-sm font-semibold';
@endphp

@if($avatarUrl)
    <img src="{{ $avatarUrl }}"
         alt="{{ $displayName }}"
         loading="lazy"
         decoding="async"
         {{ $attributes->merge(['class' => "$sizeClasses bg-zinc-100 dark:bg-zinc-900 object-cover ring-1 ring-zinc-200/80 dark:ring-zinc-800 shrink-0"]) }}>
@else
    <span aria-label="{{ $displayName }}"
          {{ $attributes->merge(['class' => "$sizeClasses flex items-center justify-center font-bold text-white ring-1 ring-black/5 dark:ring-white/10 select-none shrink-0"]) }}
          style="background-color: {{ $bg }};">
        {{ $initial }}
    </span>
@endif
