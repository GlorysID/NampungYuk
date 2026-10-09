@props([
    'post',
    'initialVote' => null,
    'canComment' => false,
    'canModerate' => false,
    'showCommunity' => false,
])

@php $images = $post->imageUrls(); @endphp

<article class="ny-card p-4 space-y-3 {{ $post->is_pinned ? 'border-[#0070f3]/40 dark:border-[#3291ff]/30' : '' }}"
         x-data="communityPostVote({
             score: {{ $post->score }},
             userVote: '{{ $initialVote }}',
             voteUrl: '{{ route('communities.post.vote', $post) }}'
         })">
    @if($showCommunity && $post->community)
        <!-- Community label (X-style "posted in") -->
        <div class="flex items-center gap-1.5 text-[11px]">
            <a href="{{ route('communities.show', $post->community->slug) }}" class="inline-flex items-center gap-1.5 font-semibold text-[#0070f3] dark:text-[#3291ff] hover:underline">
                @if($post->community->icon)
                    <img src="{{ $post->community->icon }}" alt="" class="w-4 h-4 rounded object-cover">
                @else
                    <span class="w-4 h-4 rounded ny-gradient-bg text-white flex items-center justify-center text-[9px] font-bold">{{ mb_substr($post->community->name, 0, 1) }}</span>
                @endif
                <span>{{ $post->community->name }}</span>
            </a>
            <span class="text-[#8f8f8f] dark:text-[#666666]">·</span>
            <span class="text-[#8f8f8f] dark:text-[#666666]">diposting di komunitas</span>
            @if($post->is_pinned)
                <span class="ml-auto inline-flex items-center gap-1 text-[10px] font-semibold uppercase tracking-wide text-[#0070f3] dark:text-[#3291ff]">
                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24"><path d="M16 3a1 1 0 00-1 1v1.586l-4.293 4.293a1 1 0 00-.29.546l-.585 3.51-2.125 2.124a1 1 0 00.046 1.418l.001.001-1.045 1.045a1 1 0 001.414 1.414L9.16 20.9l.001.001a1 1 0 001.418.046l2.124-2.125 3.51-.585a1 1 0 00.546-.29L21 13.657H22a1 1 0 000-2h-1V7a1 1 0 00-1-1h-4zM7 17l-3.293 3.293a1 1 0 01-1.414-1.414L5.586 15.586 7 17z"/></svg>
                    Sematan
                </span>
            @endif
        </div>
    @elseif($post->is_pinned)
        <div class="flex items-center gap-1.5 text-[10px] font-semibold uppercase tracking-wide text-[#0070f3] dark:text-[#3291ff]">
            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24"><path d="M16 3a1 1 0 00-1 1v1.586l-4.293 4.293a1 1 0 00-.29.546l-.585 3.51-2.125 2.124a1 1 0 00.046 1.418l.001.001-1.045 1.045a1 1 0 001.414 1.414L9.16 20.9l.001.001a1 1 0 001.418.046l2.124-2.125 3.51-.585a1 1 0 00.546-.29L21 13.657H22a1 1 0 000-2h-1V7a1 1 0 00-1-1h-4zM7 17l-3.293 3.293a1 1 0 01-1.414-1.414L5.586 15.586 7 17z"/></svg>
            <span>Disematkan</span>
        </div>
    @endif

    <!-- Author + type -->
    <div class="flex items-center gap-2.5">
        @if($post->user)
            <a href="{{ route('profile.show', $post->user->username) }}" class="shrink-0">
                <x-user-avatar :user="$post->user" size="sm" />
            </a>
            <div class="min-w-0 flex items-center gap-1.5 flex-wrap">
                <a href="{{ route('profile.show', $post->user->username) }}" class="font-bold text-xs text-[#18181b] dark:text-[#fafafa] hover:underline truncate">{{ $post->user->name }}</a>
                <span class="text-[11px] text-[#8f8f8f] dark:text-[#666666] shrink-0">·</span>
                <span class="text-[11px] text-[#8f8f8f] dark:text-[#666666] shrink-0">{{ $post->created_at->diffForHumans() }}</span>
            </div>
        @else
            <span class="font-semibold text-xs text-[#63636b] dark:text-[#a0a0a0]">Anonim</span>
        @endif

        <div class="ml-auto flex items-center gap-1.5 shrink-0">
            <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full border {{ $post->typeClasses() }}">{{ $post->typeLabel() }}</span>
            @if($post->isQuestion() && $post->is_answered)
                <span class="inline-flex items-center gap-1 text-[10px] font-semibold px-2 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/50">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    Terjawab
                </span>
            @endif
        </div>
    </div>

    <!-- Moderation actions -->
    @if($canModerate)
        <div class="flex items-center gap-1.5 -mt-1" data-no-card-nav>
            <form method="POST" action="{{ route('communities.post.pin', $post) }}">
                @csrf
                <button type="submit" class="text-[11px] font-medium text-[#63636b] dark:text-[#a0a0a0] hover:text-[#0070f3] transition inline-flex items-center gap-1">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/></svg>
                    {{ $post->is_pinned ? 'Lepas sematan' : 'Sematkan' }}
                </button>
            </form>
            @if($post->isQuestion())
                <form method="POST" action="{{ route('communities.post.answered', $post) }}">
                    @csrf
                    <button type="submit" class="text-[11px] font-medium text-[#63636b] dark:text-[#a0a0a0] hover:text-emerald-600 transition inline-flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4"/></svg>
                        {{ $post->is_answered ? 'Batal terjawab' : 'Tandai terjawab' }}
                    </button>
                </form>
            @endif
            <form method="POST" action="{{ route('communities.post.destroy', $post) }}" onsubmit="return confirm('Hapus postingan ini?')">
                @csrf @method('DELETE')
                <button type="submit" class="text-[11px] font-medium text-rose-500 hover:text-rose-600 transition inline-flex items-center gap-1">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3M4 7h16"/></svg>
                    Hapus
                </button>
            </form>
        </div>
    @endif

    <!-- Content -->
    <p class="text-xs sm:text-sm text-[#18181b] dark:text-[#fafafa] leading-relaxed whitespace-pre-line">{{ $post->content }}</p>

    <!-- Images -->
    @if(count($images))
        <div class="grid {{ count($images) > 1 ? 'grid-cols-2' : 'grid-cols-1' }} gap-1.5 rounded-xl overflow-hidden">
            @foreach($images as $img)
                <img src="{{ $img }}" alt="" loading="lazy" class="w-full aspect-video object-cover rounded-lg border border-[#e4e4e7] dark:border-[#1f1f1f]">
            @endforeach
        </div>
    @endif

    <!-- Actions -->
    <div class="flex items-center gap-2 pt-2 border-t border-[#e4e4e7] dark:border-[#1f1f1f]">
        <div class="inline-flex items-center rounded-full bg-[#eeeeef] dark:bg-[#171717] p-0.5">
            <button @click="vote('up')" :disabled="voting" :class="userVote === 'up' ? 'bg-[#0070f3] text-white' : 'text-[#63636b] dark:text-[#a0a0a0] hover:text-[#0070f3]'"
                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold transition active:scale-95 disabled:opacity-60">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7"/></svg>
                <span class="hl-stat" x-text="score">{{ $post->score }}</span>
            </button>
            <button @click="vote('down')" :disabled="voting" :class="userVote === 'down' ? 'bg-rose-600 text-white' : 'text-[#63636b] dark:text-[#a0a0a0] hover:text-rose-600'"
                    class="p-1.5 rounded-full transition active:scale-95 disabled:opacity-60">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
            </button>
        </div>

        <span class="inline-flex items-center gap-1.5 px-2 text-[#63636b] dark:text-[#a0a0a0] text-xs">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
            <span class="hl-stat">{{ $post->comments_count }}</span>
        </span>
    </div>

    <!-- Comments -->
    @if($post->comments->count())
        <div class="space-y-2.5 pl-4 border-l-2 border-[#e4e4e7] dark:border-[#1f1f1f]">
            @foreach($post->comments as $comment)
                <div class="flex items-start gap-2">
                    <x-user-avatar :user="$comment->user" size="xs" />
                    <div class="min-w-0">
                        <p class="text-[11px] text-[#18181b] dark:text-[#fafafa]">
                            <span class="font-semibold">{{ $comment->user?->name ?? 'Anonim' }}</span>
                            <span class="text-[#8f8f8f] dark:text-[#666666]"> · {{ $comment->created_at->diffForHumans() }}</span>
                        </p>
                        <p class="text-xs text-[#63636b] dark:text-[#a0a0a0] leading-relaxed">{{ $comment->content }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <!-- Comment form -->
    @auth
        @if($canComment)
            <form method="POST" action="{{ route('communities.post.comment', $post) }}" class="flex items-center gap-2 pt-1">
                @csrf
                <input type="text" name="content" required maxlength="1000" placeholder="Tulis komentar..." class="ny-input text-xs py-2 flex-1">
                <button type="submit" class="btn-primary text-xs py-2 px-3 shrink-0">Kirim</button>
            </form>
        @endif
    @endauth
</article>
