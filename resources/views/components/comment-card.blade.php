@props([
    'comment',
    'depth' => 0,
])

<div class="ny-card p-3.5 sm:p-4 space-y-2 border border-[#e4e4e7] dark:border-[#1f1f1f]"
     x-data="{ replying: false }">
    <div class="flex items-center justify-between gap-2">
        <div class="flex items-center gap-2.5">
            <x-user-avatar :user="$comment->user" :name="$comment->guest_name" size="sm" />
            <div class="flex items-center gap-2 flex-wrap text-xs">
                @if($comment->user)
                    @guest
                        <button type="button" @click="if (window.openAuthModal) { window.openAuthModal('login'); } else { window.location.href = '{{ route('login') }}'; }" class="font-bold text-[#18181b] dark:text-[#fafafa] hover:text-[#0070f3] dark:hover:text-[#3291ff] transition cursor-pointer">
                            {{ $comment->user->name }}
                        </button>
                    @else
                        <a href="{{ route('profile.show', $comment->user->username) }}" class="font-bold text-[#18181b] dark:text-[#fafafa] hover:text-[#0070f3] dark:hover:text-[#3291ff] transition">
                            {{ $comment->user->name }}
                        </a>
                    @endguest
                    <span class="text-[11px] font-mono text-[#63636b] dark:text-[#a0a0a0]">&#64;{{ $comment->user->username }}</span>
                @else
                    <span class="font-semibold text-[#18181b] dark:text-[#fafafa]">
                        {{ $comment->guest_name ?: 'Developer Anonim' }}
                    </span>
                @endif
                <span class="text-[#63636b] dark:text-[#a0a0a0] text-[10px]">&bull;</span>
                <span class="text-[11px] text-[#63636b] dark:text-[#a0a0a0]">{{ $comment->created_at->diffForHumans() }}</span>
            </div>
        </div>
    </div>

    <p class="text-xs sm:text-sm text-[#18181b] dark:text-[#fafafa] leading-relaxed whitespace-pre-line pl-9">
        {{ $comment->content }}
    </p>

    <!-- Reply toggle -->
    <div class="pl-9 flex items-center gap-3">
        @auth
            <button type="button" @click.stop="replying = !replying"
                    class="text-[11px] font-semibold text-[#63636b] dark:text-[#a0a0a0] hover:text-[#0070f3] dark:hover:text-[#3291ff] transition inline-flex items-center gap-1">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a5 5 0 015 5v2M3 10l4-4M3 10l4 4"/></svg>
                <span x-text="replying ? 'Batal' : 'Balas'"></span>
            </button>
        @else
            <button type="button" @click.stop="if (window.openAuthModal) { window.openAuthModal('login'); } else { window.location.href = '{{ route('login') }}'; }"
                    class="text-[11px] font-semibold text-[#63636b] dark:text-[#a0a0a0] hover:text-[#0070f3] transition inline-flex items-center gap-1 cursor-pointer">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a5 5 0 015 5v2M3 10l4-4M3 10l4 4"/></svg>
                <span>Balas</span>
            </button>
        @endauth
        @if($comment->replies->count() > 0)
            <span class="text-[11px] text-[#63636b] dark:text-[#a0a0a0]">{{ $comment->replies->count() }} balasan</span>
        @endif
    </div>

    <!-- Inline reply form -->
    @auth
        <form x-show="replying" x-cloak action="{{ route('projects.comment', $comment->project_id) }}" method="POST" class="pl-9 space-y-2">
            @csrf
            <input type="hidden" name="parent_id" value="{{ $comment->id }}">
            <textarea name="content" rows="2" required maxlength="1000"
                      placeholder="Balas {{ $comment->user->name ?? $comment->guest_name ?? 'komentar ini' }}..."
                      class="ny-input text-xs sm:text-sm w-full resize-none"></textarea>
            <div class="flex justify-end gap-2">
                <button type="button" @click="replying = false" class="btn-secondary text-[11px] py-1.5 px-3">Batal</button>
                <button type="submit" class="btn-primary text-[11px] py-1.5 px-3">Kirim Balasan</button>
            </div>
        </form>
    @endauth

    <!-- Nested replies -->
    @if($comment->replies->count() > 0)
        <div class="pl-5 sm:pl-9 mt-1 space-y-2 border-l-2 border-[#e4e4e7] dark:border-[#1f1f1f]">
            @foreach($comment->replies as $reply)
                <x-comment-card :comment="$reply" :depth="$depth + 1" />
            @endforeach
        </div>
    @endif
</div>
