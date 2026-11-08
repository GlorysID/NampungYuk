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
    @auth
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
                    @mouseenter="following ? menuOpen = true : null; hoverUnfollow = following"
                    @mouseleave="hoverUnfollow = false"
                    type="button"
                    :disabled="loading"
                    :class="following
                        ? (hoverUnfollow
                            ? 'bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 border border-rose-300 dark:border-rose-800'
                            : 'bg-transparent text-[#0B0B0D] dark:text-[#EAE8E1] border border-[#D0CDC3] dark:border-[#333336] hover:border-[#0B0B0D] dark:hover:border-[#EAE8E1]')
                        : 'bg-[#0B0B0D] hover:bg-[#222225] dark:bg-[#EAE8E1] dark:hover:bg-[#DCD9D0] text-[#EAE8E1] dark:text-[#0B0B0D] font-bold border border-transparent shadow-xs'"
                    class="inline-flex items-center justify-center gap-1.5 font-semibold transition active:scale-[0.98] disabled:opacity-60 cursor-pointer {{ $size === 'sm' ? 'h-8 px-3 text-xs rounded-lg' : 'h-10 px-4 text-sm rounded-xl' }}">
                <span x-show="following && hoverUnfollow" x-cloak x-text="'Berhenti mengikuti'"></span>
                <span x-show="!following || !hoverUnfollow" style="display: inline;" x-text="following ? 'Mengikuti' : 'Ikuti'"></span>
            </button>

            {{-- Hover dropdown when following --}}
            <div x-show="menuOpen" x-cloak
                 x-transition:enter="transition ease-out duration-120"
                 x-transition:enter-start="opacity-0 -translate-y-1"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 @mouseleave="menuOpen = false"
                 class="absolute right-0 top-full mt-1.5 z-40 min-w-[220px] rounded-xl bg-white dark:bg-[#121214] border border-[#E2DFD7] dark:border-[#242426] shadow-xl overflow-hidden">
                <button type="button"
                        @click="toggle(); menuOpen = false"
                        class="w-full flex items-start gap-2.5 px-3.5 py-2.5 text-left hover:bg-rose-50 dark:hover:bg-rose-950/40 transition cursor-pointer">
                    <svg class="w-4 h-4 text-rose-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7a4 4 0 11-8 0 4 4 0 018 0zM9 14a6 6 0 00-6 6v1h12v-1a6 6 0 00-6-6zM21 12h-6"/>
                    </svg>
                    <span class="min-w-0">
                        <span class="block text-xs font-semibold text-rose-600 dark:text-rose-400" x-text="'Berhenti mengikuti @' + username"></span>
                        <span class="block text-[10px] text-[#6E6D67] dark:text-[#8E8D86]">Repost mereka tidak akan tampil di timeline-mu.</span>
                    </span>
                </button>
            </div>
        </div>
    @else
        <button type="button"
                @click="if (window.openAuthModal) { window.openAuthModal('login'); } else { window.location.href = '{{ route('login') }}'; }"
                class="inline-flex items-center justify-center gap-1.5 font-bold bg-[#0B0B0D] hover:bg-[#222225] dark:bg-[#EAE8E1] dark:hover:bg-[#DCD9D0] text-[#EAE8E1] dark:text-[#0B0B0D] border border-transparent shadow-xs transition active:scale-[0.98] cursor-pointer {{ $size === 'sm' ? 'h-8 px-3 text-xs rounded-lg' : 'h-10 px-4 text-sm rounded-xl' }}">
            <span>Ikuti</span>
        </button>
    @endauth
@endunless
