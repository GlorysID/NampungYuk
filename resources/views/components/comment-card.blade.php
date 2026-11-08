@props([
    'comment',
    'depth' => 0,
])

<div class="rounded-xl bg-white dark:bg-[#121214] p-4 sm:p-5 space-y-2.5 border border-[#E2DFD7] dark:border-[#242426] shadow-xs"
     x-data="{ replying: false, showReplies: false }">
    <div class="flex items-center justify-between gap-2">
        <div class="flex items-center gap-2.5">
            @if($comment->user)
                <a href="{{ route('profile.show', $comment->user->username) }}" class="shrink-0 hover:opacity-85 transition">
                    <x-user-avatar :user="$comment->user" size="sm" />
                </a>
            @else
                <x-user-avatar :name="$comment->guest_name" size="sm" />
            @endif
            <div class="flex items-center gap-2 flex-wrap text-sm">
                @if($comment->user)
                    <a href="{{ route('profile.show', $comment->user->username) }}" class="font-bold text-[#0B0B0D] dark:text-[#EAE8E1] hover:underline transition">
                        {{ $comment->user->name }}
                    </a>
                    <span class="text-xs font-mono text-[#6E6D67] dark:text-[#8E8D86]">&#64;{{ $comment->user->username }}</span>
                @else
                    <span class="font-semibold text-[#0B0B0D] dark:text-[#EAE8E1]">
                        {{ $comment->guest_name ?: 'Developer Anonim' }}
                    </span>
                @endif
                <span class="text-[#908F88] dark:text-[#605F5A] text-xs">&bull;</span>
                <span class="text-xs text-[#6E6D67] dark:text-[#8E8D86]">{{ $comment->created_at->diffForHumans() }}</span>
            </div>
        </div>
    </div>

    <p class="text-[14.5px] text-[#0B0B0D] dark:text-[#EAE8E1] leading-relaxed whitespace-pre-line pl-10">
        {{ $comment->content }}
    </p>

    <!-- Reply toggle + collapse toggle -->
    <div class="pl-10 flex items-center gap-3.5">
        @auth
            <button type="button" x-on:click.prevent="replying = !replying"
                    class="text-xs font-semibold text-[#6E6D67] dark:text-[#8E8D86] hover:text-[#0B0B0D] dark:hover:text-[#EAE8E1] transition inline-flex items-center gap-1.5 cursor-pointer">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a5 5 0 015 5v2M3 10l4-4M3 10l4 4"/></svg>
                <span x-text="replying ? 'Batal' : 'Balas'"></span>
            </button>
        @else
            <button type="button" x-on:click.prevent="if (window.openAuthModal) { window.openAuthModal('login'); } else { window.location.href = '{{ route('login') }}'; }"
                    class="text-xs font-semibold text-[#6E6D67] dark:text-[#8E8D86] hover:text-[#0B0B0D] dark:hover:text-[#EAE8E1] transition inline-flex items-center gap-1.5 cursor-pointer">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a5 5 0 015 5v2M3 10l4-4M3 10l4 4"/></svg>
                <span>Balas</span>
            </button>
        @endauth

        @if($comment->replies->count() > 0)
            <button type="button" x-on:click="showReplies = !showReplies"
                    class="text-xs font-semibold text-[#6E6D67] dark:text-[#8E8D86] hover:text-[#0B0B0D] dark:hover:text-[#EAE8E1] transition inline-flex items-center gap-1.5 cursor-pointer">
                <svg class="w-3.5 h-3.5 transition-transform" :class="showReplies ? '' : '-rotate-90'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                <span x-text="showReplies ? 'Sembunyikan {{ $comment->replies->count() }} balasan' : 'Lihat {{ $comment->replies->count() }} balasan'"></span>
            </button>
        @endif

        @auth
            @if(auth()->id() === $comment->user_id || (isset($comment->project) && auth()->id() === $comment->project->user_id) || auth()->user()->is_admin)
                <form action="{{ route('projects.comment.destroy', $comment->id) }}" method="POST" onsubmit="return confirm('Hapus komentar ini?')" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-xs font-semibold text-rose-500/80 hover:text-rose-600 transition inline-flex items-center gap-1.5 cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        <span>Hapus</span>
                    </button>
                </form>
            @endif
        @endauth
    </div>

    <!-- Inline reply form -->
    @auth
        <form x-show="replying" x-cloak action="{{ route('projects.comment', $comment->project_id) }}" method="POST" class="pl-10 space-y-2.5">
            @csrf
            <input type="hidden" name="parent_id" value="{{ $comment->id }}">
            <textarea name="content" rows="2" required maxlength="1000"
                      placeholder="Balas {{ $comment->user->name ?? $comment->guest_name ?? 'komentar ini' }}..."
                      class="w-full px-3.5 py-2 rounded-xl bg-white dark:bg-[#141416] border border-[#E2DFD7] dark:border-[#242426] text-[#0B0B0D] dark:text-[#EAE8E1] placeholder:text-[#908F88] dark:placeholder:text-[#6E6D67] text-xs sm:text-sm outline-none focus:border-[#0B0B0D] dark:focus:border-[#EAE8E1] resize-none transition"></textarea>
            <div class="flex justify-end gap-2">
                <button type="button" x-on:click="replying = false" class="px-3.5 py-2 rounded-xl border border-[#E2DFD7] dark:border-[#242426] bg-[#EFECE6] dark:bg-[#161619] hover:bg-[#E2DFD7] dark:hover:bg-[#19191D] text-[#0B0B0D] dark:text-[#EAE8E1] text-xs font-semibold transition cursor-pointer">Batal</button>
                <button type="submit" class="px-3.5 py-2 rounded-xl bg-[#0B0B0D] text-[#EAE8E1] dark:bg-[#EAE8E1] dark:text-[#0B0B0D] hover:bg-[#222225] dark:hover:bg-[#DCD9D0] text-xs font-bold transition shadow-xs cursor-pointer">Kirim Balasan</button>
            </div>
        </form>
    @endauth

    <!-- Nested replies (collapsible) -->
    @if($comment->replies->count() > 0)
        <div x-show="showReplies" class="pl-6 sm:pl-10 mt-2 space-y-2.5 border-l-2 border-[#E2DFD7] dark:border-[#242426]">
            @foreach($comment->replies as $reply)
                <x-comment-card :comment="$reply" :depth="$depth + 1" />
            @endforeach
        </div>
    @endif
</div>
