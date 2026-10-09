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
        'xs' => 'w-5 h-5 rounded-full text-[9px]',
        'sm' => 'w-7 h-7 rounded-full text-[11px]',
        'md' => 'w-9 h-9 rounded-full text-[13px]',
        'lg' => 'w-12 h-12 rounded-full text-base',
        'xl' => 'w-16 h-16 sm:w-20 sm:h-20 rounded-full text-2xl',
    ][$size] ?? 'w-9 h-9 rounded-full text-[13px]';
@endphp

@if($avatarUrl)
    <img src="{{ $avatarUrl }}"
         alt="{{ $displayName }}"
         loading="lazy"
         decoding="async"
         {{ $attributes->merge(['class' => "$sizeClasses bg-[#eeeeef] dark:bg-[#171717] object-cover ring-1 ring-[#eaeaea] dark:ring-[#1f1f1f] shrink-0"]) }}>
@else
    <span aria-label="{{ $displayName }}"
          {{ $attributes->merge(['class' => "$sizeClasses flex items-center justify-center font-bold text-white ring-1 ring-black/5 dark:ring-white/10 select-none shrink-0"]) }}
          style="background-color: {{ $bg }};">
        {{ $initial }}
    </span>
@endif
