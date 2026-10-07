@props([
    'user',
    'size' => 'md', // 'sm', 'md'
])

@php
    $authUser = auth()->user();
    $isSelf = $authUser && $authUser->id === $user->id;
    $isFollowing = $authUser ? $authUser->isFollowing($user) : false;
@endphp

@unless($isSelf)
    <div x-data="followButton({
            following: {{ $isFollowing ? 'true' : 'false' }},
            count: {{ $user->followers()->count() }},
            url: '{{ route('follow.toggle', $user) }}'
         })"
         class="inline-flex items-center">
        <button @click="toggle()"
                type="button"
                :disabled="loading"
                :class="following
                    ? 'bg-transparent text-[#5c6979] dark:text-[#7e8a9a] border border-[#d5dbe2] dark:border-[#262d3a] hover:border-rose-400 hover:text-rose-500'
                    : 'bg-[#0e9c8b] text-[#04110f] border border-transparent hover:bg-[#0b7d70]'"
                class="inline-flex items-center justify-center gap-1.5 rounded-md font-semibold transition active:scale-[0.98] disabled:opacity-60 {{ $size === 'sm' ? 'text-[11px] px-2.5 py-1' : 'text-xs px-3.5 py-1.5' }}">
            <svg x-show="following" x-cloak class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
            </svg>
            <svg x-show="!following" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
            </svg>
            <span x-text="following ? 'Mengikuti' : 'Ikuti'"></span>
        </button>
    </div>
@endunless
