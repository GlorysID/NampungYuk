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
            url: '{{ route('follow.toggle', $user) }}',
            username: '{{ $user->username }}'
         })"
         class="relative inline-flex"
         @mouseleave="menuOpen = false">

        {{-- Follow button (with hover → unfollow confirmation) --}}
        <button @click="following ? (menuOpen = !menuOpen) : toggle()"
                @mouseenter="following ? menuOpen = true : null"
                type="button"
                :disabled="loading"
                :class="following
                    ? (hoverUnfollow
                        ? 'bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 border border-rose-300 dark:border-rose-800'
                        : 'bg-transparent text-[#000000] dark:text-[#fafafa] border border-[#d8d8d8] dark:border-[#2e2e2e]')
                    : 'bg-[#000000] dark:bg-[#f2f2f2] text-[#ffffff] dark:text-[#000000] border border-transparent hover:bg-[#333333] dark:hover:bg-[#ededed]'"
                @mouseenter="hoverUnfollow = following"
                @mouseleave="hoverUnfollow = false"
                class="inline-flex items-center justify-center gap-1.5 rounded-full font-semibold transition active:scale-[0.98] disabled:opacity-60 {{ $size === 'sm' ? 'text-[11px] px-3 py-1' : 'text-xs px-4 py-1.5' }}">
            <span x-show="following && hoverUnfollow" x-cloak x-text="'Berhenti mengikuti'"></span>
            <span x-show="!following || !hoverUnfollow" style="display: inline;" x-text="following ? 'Mengikuti' : 'Ikuti'"></span>
        </button>

        {{-- Hover dropdown (X-style) when following --}}
        <div x-show="menuOpen" x-cloak
             x-transition:enter="transition ease-out duration-120"
             x-transition:enter-start="opacity-0 -translate-y-1"
             x-transition:enter-end="opacity-100 translate-y-0"
             @mouseleave="menuOpen = false"
             class="absolute right-0 top-full mt-1.5 z-40 min-w-[220px] rounded-xl bg-white dark:bg-[#0a0a0a] border border-[#e2e2e2] dark:border-[#1f1f1f] shadow-lg overflow-hidden">
            <button type="button"
                    @click="toggle(); menuOpen = false"
                    class="w-full flex items-start gap-2.5 px-3.5 py-2.5 text-left hover:bg-rose-50 dark:hover:bg-rose-950/40 transition">
                <svg class="w-4 h-4 text-rose-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7a4 4 0 11-8 0 4 4 0 018 0zM9 14a6 6 0 00-6 6v1h12v-1a6 6 0 00-6-6zM21 12h-6"/>
                </svg>
                <span class="min-w-0">
                    <span class="block text-xs font-semibold text-rose-600 dark:text-rose-400" x-text="'Berhenti mengikuti @' + username"></span>
                    <span class="block text-[10px] text-[#8f8f8f] dark:text-[#666666]">Repost mereka tidak akan tampil di timeline-mu.</span>
                </span>
            </button>
        </div>
    </div>
@endunless
