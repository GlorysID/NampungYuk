@props([
    'comment',
    'depth' => 0,
])

<div class="py-3" x-data="{ replying: false, showReplies: false }">
    <div class="flex items-start gap-3">
        <div class="w-8 shrink-0 pt-0.5">
            @if($comment->user)
                <a href="{{ route('profile.show', $comment->user->username) }}" class="block shrink-0 hover:opacity-85 transition">
                    <x-user-avatar :user="$comment->user" size="sm" />
                </a>
            @else
                <x-user-avatar :name="$comment->guest_name" size="sm" />
            @endif
        </div>

        <div class="min-w-0 flex-1 space-y-1">
            <div class="flex items-center gap-2 flex-wrap text-sm leading-tight">
                @if($comment->user)
                    <a href="{{ route('profile.show', $comment->user->username) }}" class="font-bold text-[var(--primary)] hover:underline transition">
                        {{ $comment->user->name }}
                    </a>
                    <span class="text-xs font-mono text-[var(--text-muted)]">&#64;{{ $comment->user->username }}</span>
                @else
                    <span class="font-semibold text-[var(--primary)]">
                        {{ $comment->guest_name ?: 'Developer Anonim' }}
                    </span>
                @endif
                <span class="text-[var(--text-faint)] text-xs">&bull;</span>
                <span class="text-xs text-[var(--text-muted)]">{{ $comment->created_at->diffForHumans() }}</span>
            </div>

            <p class="text-[14px] text-[var(--primary)] leading-relaxed whitespace-pre-line">
                {{ $comment->content }}
            </p>

            <!-- Reply toggle + collapse toggle -->
            <div class="flex items-center gap-3 pt-0.5">
                @auth
                    <button type="button" x-on:click.prevent="replying = !replying"
                            class="text-xs font-semibold text-[var(--text-muted)] hover:text-[var(--primary)] transition inline-flex items-center gap-1.5 cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a5 5 0 015 5v2M3 10l4-4M3 10l4 4"/></svg>
                        <span x-text="replying ? 'Batal' : 'Balas'"></span>
                    </button>
                @else
                    <button type="button" x-on:click.prevent="if (window.openAuthModal) { window.openAuthModal('login'); } else { window.location.href = '{{ route('login') }}'; }"
                            class="text-xs font-semibold text-[var(--text-muted)] hover:text-[var(--primary)] transition inline-flex items-center gap-1.5 cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a5 5 0 015 5v2M3 10l4-4M3 10l4 4"/></svg>
                        <span>Balas</span>
                    </button>
                @endauth

                @if($comment->replies->count() > 0)
                    <button type="button" x-on:click="showReplies = !showReplies"
                            class="text-xs font-semibold text-[var(--text-muted)] hover:text-[var(--primary)] transition inline-flex items-center gap-1.5 cursor-pointer">
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
                <form x-show="replying" x-cloak action="{{ route('projects.comment', $comment->project_id) }}" method="POST" class="pt-2 space-y-2">
                    @csrf
                    <input type="hidden" name="parent_id" value="{{ $comment->id }}">
                    <textarea name="content" rows="2" required maxlength="1000"
                              placeholder="Balas {{ $comment->user->name ?? $comment->guest_name ?? 'komentar ini' }}..."
                              class="w-full px-3.5 py-2 rounded-xl bg-[var(--surface-input)] border border-[var(--border-color)] text-[var(--primary)] placeholder:text-[var(--text-placeholder)] text-xs sm:text-sm outline-none focus:border-[var(--primary)] resize-none transition"></textarea>
                    <div class="flex justify-end gap-2">
                        <button type="button" x-on:click="replying = false" class="h-8 px-3 rounded-lg border border-[var(--border-color)] bg-[var(--surface-muted)] hover:bg-[var(--surface-chip-hover)] text-[var(--primary)] text-xs font-semibold transition cursor-pointer">Batal</button>
                        <button type="submit" class="h-8 px-3 rounded-lg bg-[var(--primary)] text-[var(--primary-text)] hover:bg-[var(--primary-hover)] text-xs font-bold transition shadow-xs cursor-pointer">Kirim Balasan</button>
                    </div>
                </form>
            @endauth
        </div>
    </div>

    <!-- Nested replies (collapsible) -->
    @if($comment->replies->count() > 0)
        <div x-show="showReplies" class="ml-8 sm:ml-10 pl-3 sm:pl-4 mt-2 space-y-1 border-l border-[var(--border-color)]">
            @foreach($comment->replies as $reply)
                <x-comment-card :comment="$reply" :depth="$depth + 1" />
            @endforeach
        </div>
    @endif
</div>
