@props([
    'post',
    'initialVote' => null,
    'canComment' => false,
])

@php $images = $post->imageUrls(); @endphp

<article class="ny-card p-4 space-y-3"
         x-data="communityPostVote({
             score: {{ $post->score }},
             userVote: '{{ $initialVote }}',
             voteUrl: '{{ route('communities.post.vote', $post) }}'
         })">
    <!-- Author -->
    <div class="flex items-center gap-2.5">
        @if($post->user)
            <a href="{{ route('profile.show', $post->user->username) }}" class="shrink-0">
                <x-user-avatar :user="$post->user" size="sm" />
            </a>
            <div class="min-w-0 flex items-center gap-1.5">
                <a href="{{ route('profile.show', $post->user->username) }}" class="font-bold text-xs text-[#18181b] dark:text-[#fafafa] hover:underline truncate">{{ $post->user->name }}</a>
                <span class="text-[11px] text-[#8f8f8f] dark:text-[#666666] shrink-0">·</span>
                <span class="text-[11px] text-[#8f8f8f] dark:text-[#666666] shrink-0">{{ $post->created_at->diffForHumans() }}</span>
            </div>
        @else
            <span class="font-semibold text-xs text-[#63636b] dark:text-[#a0a0a0]">Anonim</span>
        @endif
    </div>

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
