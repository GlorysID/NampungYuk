@props([
    'user' => null,
    'name' => null,
    'avatar' => null,
    'size' => 'md', // 'xs', 'sm', 'md', 'lg', 'xl'
])

@php
    $displayName = $user ? $user->name : ($name ?? 'Developer');
    $avatarUrl = $user ? ($user->avatar ?? null) : $avatar;
    if (! $avatarUrl) {
        $avatarUrl = 'https://api.dicebear.com/7.x/bottts/svg?seed=' . urlencode($displayName);
    }

    $sizeClasses = [
        'xs' => 'w-5 h-5 rounded-md text-[10px]',
        'sm' => 'w-7 h-7 rounded-lg text-xs',
        'md' => 'w-9 h-9 rounded-xl text-sm',
        'lg' => 'w-12 h-12 rounded-xl text-base',
        'xl' => 'w-16 h-16 sm:w-20 sm:h-20 rounded-2xl text-xl',
    ][$size] ?? 'w-9 h-9 rounded-xl text-sm';
@endphp

<img src="{{ $avatarUrl }}" 
     alt="{{ $displayName }}" 
     loading="lazy"
     decoding="async"
     {{ $attributes->merge(['class' => "$sizeClasses bg-[#e6eaee] dark:bg-[#1e2530] object-cover ring-1 ring-[#d5dbe2] dark:ring-[#262d3a] shrink-0"]) }}>
