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
        // Deterministic real portrait fallback (randomuser.me = real people photos).
        $bucket = crc32($displayName) % 99 + 1;
        $subset = (crc32($displayName) % 2 === 0) ? 'men' : 'women';
        $avatarUrl = "https://randomuser.me/api/portraits/{$subset}/{$bucket}.jpg";
    }

    $sizeClasses = [
        'xs' => 'w-5 h-5 rounded-full text-[10px]',
        'sm' => 'w-7 h-7 rounded-full text-xs',
        'md' => 'w-9 h-9 rounded-full text-sm',
        'lg' => 'w-12 h-12 rounded-full text-base',
        'xl' => 'w-16 h-16 sm:w-20 sm:h-20 rounded-full text-xl',
    ][$size] ?? 'w-9 h-9 rounded-full text-sm';
@endphp

<img src="{{ $avatarUrl }}" 
     alt="{{ $displayName }}" 
     loading="lazy"
     decoding="async"
     {{ $attributes->merge(['class' => "$sizeClasses bg-[#f2f2f2] dark:bg-[#171717] object-cover ring-1 ring-[#eaeaea] dark:ring-[#1f1f1f] shrink-0"]) }}>
