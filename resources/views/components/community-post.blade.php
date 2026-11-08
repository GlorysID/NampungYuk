@props([
    'post',
    'initialVote' => null,
    'canComment' => false,
    'canModerate' => false,
    'showCommunity' => false,
])

@php $images = $post->imageUrls(); @endphp

<article id="post-{{ $post->id }}" class="bg-white dark:bg-[#121214] border border-[#E2DFD7] dark:border-[#242426] hover:border-[#D0CDC3] dark:hover:border-[#333336] rounded-2xl p-4 sm:p-5 space-y-3.5 shadow-xs transition-colors {{ $post->is_pinned ? 'ring-1 ring-[#D0CDC3] dark:ring-[#333336]' : '' }}"
         x-data="communityPostVote({
             score: {{ $post->score }},
             userVote: '{{ $initialVote }}',
             voteUrl: '{{ route('communities.post.vote', $post) }}'
         })">
    @if($showCommunity && $post->community)
        <!-- Community label -->
        <div class="flex items-center gap-1.5 text-[11px]">
            <a href="{{ route('communities.show', $post->community->slug) }}" class="inline-flex items-center gap-1.5 font-semibold text-[#0B0B0D] dark:text-[#EAE8E1] hover:underline">
                @if($post->community->icon)
                    <img src="{{ $post->community->icon }}" alt="" class="w-4 h-4 rounded object-cover">
                @else
                    <span class="w-4 h-4 rounded bg-[#0B0B0D] text-[#EAE8E1] dark:bg-[#EAE8E1] dark:text-[#0B0B0D] flex items-center justify-center text-[9px] font-bold">{{ mb_substr($post->community->name, 0, 1) }}</span>
                @endif
                <span>{{ $post->community->name }}</span>
            </a>
            <span class="text-[#908F88] dark:text-[#605F5A]">·</span>
            <span class="text-[#6E6D67] dark:text-[#8E8D86]">diposting di komunitas</span>
            @if($post->is_pinned)
                <span class="ml-auto inline-flex items-center gap-1 text-[10px] font-semibold uppercase tracking-wide text-[#6E6D67] dark:text-[#8E8D86]">
                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24"><path d="M16 3a1 1 0 00-1 1v1.586l-4.293 4.293a1 1 0 00-.29.546l-.585 3.51-2.125 2.124a1 1 0 00.046 1.418l.001.001-1.045 1.045a1 1 0 001.414 1.414L9.16 20.9l.001.001a1 1 0 001.418.046l2.124-2.125 3.51-.585a1 1 0 00.546-.29L21 13.657H22a1 1 0 000-2h-1V7a1 1 0 00-1-1h-4zM7 17l-3.293 3.293a1 1 0 01-1.414-1.414L5.586 15.586 7 17z"/></svg>
                    Sematan
                </span>
            @endif
        </div>
    @elseif($post->is_pinned)
        <div class="flex items-center gap-1.5 text-[10px] font-semibold uppercase tracking-wide text-[#6E6D67] dark:text-[#8E8D86]">
            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24"><path d="M16 3a1 1 0 00-1 1v1.586l-4.293 4.293a1 1 0 00-.29.546l-.585 3.51-2.125 2.124a1 1 0 00.046 1.418l.001.001-1.045 1.045a1 1 0 001.414 1.414L9.16 20.9l.001.001a1 1 0 001.418.046l2.124-2.125 3.51-.585a1 1 0 00.546-.29L21 13.657H22a1 1 0 000-2h-1V7a1 1 0 00-1-1h-4zM7 17l-3.293 3.293a1 1 0 01-1.414-1.414L5.586 15.586 7 17z"/></svg>
            <span>Disematkan</span>
        </div>
    @endif

    <!-- Author + type -->
    <div class="flex items-center gap-3">
        @if($post->user)
            <a href="{{ route('profile.show', $post->user->username) }}" class="shrink-0">
                <x-user-avatar :user="$post->user" size="sm" />
            </a>
            <div class="min-w-0 flex items-center gap-1.5 flex-wrap">
                <a href="{{ route('profile.show', $post->user->username) }}" class="font-bold text-sm sm:text-[15px] text-[#0B0B0D] dark:text-[#EAE8E1] hover:underline truncate">{{ $post->user->name }}</a>
                <span class="text-xs text-[#908F88] dark:text-[#605F5A] shrink-0">·</span>
                <span class="text-xs text-[#6E6D67] dark:text-[#8E8D86] shrink-0">{{ $post->created_at->diffForHumans() }}</span>
            </div>
        @else
            <span class="font-semibold text-sm text-[#6E6D67] dark:text-[#8E8D86]">Anonim</span>
        @endif

        <div class="ml-auto flex items-center gap-1.5 shrink-0">
            <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full border {{ $post->typeClasses() }}">{{ $post->typeLabel() }}</span>
            @if($post->isQuestion() && $post->is_answered)
                <span class="inline-flex items-center gap-1 text-xs font-semibold px-2.5 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/50">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    Terjawab
                </span>
            @endif
        </div>
    </div>

    <!-- Moderation actions for community moderators -->
    @if($canModerate)
        <div class="flex items-center gap-2 -mt-1" data-no-card-nav>
            <form method="POST" action="{{ route('communities.post.pin', $post) }}">
                @csrf
                <button type="submit" class="text-xs font-medium text-[#6E6D67] dark:text-[#8E8D86] hover:text-[#0B0B0D] dark:hover:text-[#EAE8E1] transition inline-flex items-center gap-1 cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/></svg>
                    {{ $post->is_pinned ? 'Lepas sematan' : 'Sematkan' }}
                </button>
            </form>
            @if($post->isQuestion())
                <form method="POST" action="{{ route('communities.post.answered', $post) }}">
                    @csrf
                    <button type="submit" class="text-xs font-medium text-[#6E6D67] dark:text-[#8E8D86] hover:text-emerald-600 dark:hover:text-emerald-400 transition inline-flex items-center gap-1 cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4"/></svg>
                        {{ $post->is_answered ? 'Batal terjawab' : 'Tandai terjawab' }}
                    </button>
                </form>
            @endif
            <form method="POST" action="{{ route('communities.post.destroy', $post) }}" onsubmit="return confirm('Hapus postingan ini?')">
                @csrf @method('DELETE')
                <button type="submit" class="text-xs font-medium text-rose-500 hover:text-rose-600 transition inline-flex items-center gap-1 cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3M4 7h16"/></svg>
                    Hapus
                </button>
            </form>
        </div>
    @endif

    <!-- Content -->
    <p class="text-[15px] text-[#0B0B0D] dark:text-[#EAE8E1] leading-relaxed whitespace-pre-line">{{ $post->content }}</p>

    <!-- Images -->
    @if(count($images))
        <div class="grid {{ count($images) > 1 ? 'grid-cols-2' : 'grid-cols-1' }} gap-2 rounded-xl overflow-hidden">
            @foreach($images as $img)
                <img src="{{ $img }}" alt="" loading="lazy" class="w-full aspect-video object-cover rounded-lg border border-[#E2DFD7] dark:border-[#242426]">
            @endforeach
        </div>
    @endif

    <!-- Actions -->
    <div class="flex items-center gap-2 pt-2.5 border-t border-[#E2DFD7] dark:border-[#242426]">
        <!-- Upvote Button -->
        @guest
            <button type="button" @click="if (window.openAuthModal) { window.openAuthModal('login'); } else { window.location.href = '{{ route('login') }}'; }"
                    aria-label="Upvote postingan"
                    class="btn-upvote min-h-[40px] inline-flex items-center gap-2 px-3 py-2 rounded-xl font-medium text-[#6E6D67] dark:text-[#8E8D86] hover:text-[#0B0B0D] dark:hover:text-[#EAE8E1] hover:bg-[#EFECE6] dark:hover:bg-[#161619] transition active:scale-95 cursor-pointer border border-[#E2DFD7] dark:border-[#242426]">
                <svg class="w-[18px] h-[18px] text-emerald-600 dark:text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7"/>
                </svg>
                <span class="font-mono text-xs font-semibold">{{ $post->score }}</span>
            </button>
        @else
            <button @click="vote('up')" type="button" :disabled="voting"
                    :aria-pressed="userVote === 'up'"
                    aria-label="Upvote postingan"
                    :class="{
                        'bg-emerald-500/10 text-emerald-700 dark:text-emerald-300 border-emerald-500/30 dark:border-emerald-500/40': userVote === 'up',
                        'text-[#6E6D67] dark:text-[#8E8D86] hover:text-[#0B0B0D] dark:hover:text-[#EAE8E1] hover:bg-[#EFECE6] dark:hover:bg-[#161619] border-[#E2DFD7] dark:border-[#242426]': userVote !== 'up'
                    }"
                    class="btn-upvote min-h-[40px] inline-flex items-center gap-2 px-3 py-2 rounded-xl font-medium transition active:scale-95 disabled:opacity-60 border cursor-pointer">
                <svg class="w-[18px] h-[18px] text-emerald-600 dark:text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7"/>
                </svg>
                <span class="font-mono text-xs font-semibold" x-text="score">{{ $post->score }}</span>
            </button>
        @endguest

        <span class="min-h-[40px] inline-flex items-center gap-2 px-3 py-2 rounded-xl border border-[#E2DFD7] dark:border-[#242426] text-[#6E6D67] dark:text-[#8E8D86] text-xs font-semibold">
            <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
            </svg>
            <span class="font-mono text-xs">{{ $post->comments_count }}</span>
        </span>

        <!-- Overflow menu (⋯): Share + Report + Author Delete -->
        <div class="relative ml-auto" x-data="{ menuOpen: false }" @click.outside="menuOpen = false">
            <button @click.stop="menuOpen = !menuOpen" type="button" aria-label="Menu lainnya"
                    :aria-expanded="menuOpen" aria-haspopup="true"
                    class="p-2 rounded-lg text-[#6E6D67] dark:text-[#8E8D86] hover:text-[#0B0B0D] dark:hover:text-[#EAE8E1] hover:bg-[#EFECE6] dark:hover:bg-[#161619] transition active:scale-95 cursor-pointer">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><circle cx="5" cy="12" r="2"/><circle cx="12" cy="12" r="2"/><circle cx="19" cy="12" r="2"/></svg>
            </button>
            <div x-show="menuOpen" x-cloak
                 x-transition:enter="transition ease-out duration-120"
                 x-transition:enter-start="opacity-0 -translate-y-1"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="absolute right-0 bottom-full mb-1.5 z-40 min-w-[190px] rounded-xl bg-white dark:bg-[#121214] border border-[#E2DFD7] dark:border-[#242426] shadow-lg overflow-hidden text-xs">
                <button type="button"
                        @click.stop="navigator.clipboard.writeText('{{ route('communities.show', $post->community?->slug) }}#post-{{ $post->id }}'); if (window.notify) notify('Tautan postingan berhasil disalin!'); menuOpen = false"
                        class="w-full flex items-center gap-2.5 px-3.5 py-2.5 text-left text-[#0B0B0D] dark:text-[#EAE8E1] hover:bg-[#EFECE6] dark:hover:bg-[#161619] transition cursor-pointer">
                    <svg class="w-4 h-4 text-[#908F88] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/></svg>
                    <span>Bagikan / Salin Tautan</span>
                </button>
                @auth
                    @if((int) auth()->id() === (int) $post->user_id)
                        <div class="border-t border-[#E2DFD7] dark:border-[#242426]"></div>
                        <form method="POST" action="{{ route('communities.post.destroy', $post) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus postingan ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full flex items-center gap-2.5 px-3.5 py-2.5 text-left text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/30 transition text-xs font-medium cursor-pointer">
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3M4 7h16"/></svg>
                                <span>Hapus Postingan</span>
                            </button>
                        </form>
                    @elseif(auth()->user()->isAdmin())
                        <div class="border-t border-[#E2DFD7] dark:border-[#242426]"></div>
                        <form method="POST" action="{{ route('admin.community-posts.destroy', $post) }}" onsubmit="return confirm('Hapus postingan ini sebagai admin?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full flex items-center gap-2.5 px-3.5 py-2.5 text-left text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/30 transition text-xs font-medium cursor-pointer">
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3M4 7h16"/></svg>
                                <span>Hapus (Admin)</span>
                            </button>
                        </form>
                    @endif
                    @if(auth()->id() !== $post->user_id)
                        <div class="border-t border-[#E2DFD7] dark:border-[#242426]"></div>
                        <button type="button"
                                @click.stop="window.openReportModal ? window.openReportModal({ type: 'community_post', id: {{ $post->id }}, title: 'Postingan komunitas' }) : null; menuOpen = false"
                                class="w-full flex items-center gap-2.5 px-3.5 py-2.5 text-left text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/30 transition cursor-pointer">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            <span>Laporkan Postingan</span>
                        </button>
                    @endif
                @else
                    <div class="border-t border-[#E2DFD7] dark:border-[#242426]"></div>
                    <button type="button"
                            @click.stop="if (window.openAuthModal) { window.openAuthModal('login'); } else { window.location.href = '{{ route('login') }}'; }"
                            class="w-full flex items-center gap-2.5 px-3.5 py-2.5 text-left text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/30 transition cursor-pointer">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <span>Laporkan Postingan</span>
                    </button>
                @endauth
            </div>
        </div>
    </div>

    <!-- Comments -->
    @if($post->comments->count())
        <div class="space-y-2.5 pl-4 border-l-2 border-[#E2DFD7] dark:border-[#242426]">
            @foreach($post->comments as $comment)
                <div class="flex items-start gap-2">
                    <x-user-avatar :user="$comment->user" size="xs" />
                    <div class="min-w-0">
                        <p class="text-[11px] text-[#0B0B0D] dark:text-[#EAE8E1]">
                            <span class="font-semibold">{{ $comment->user?->name ?? 'Anonim' }}</span>
                            <span class="text-[#908F88] dark:text-[#605F5A]"> · {{ $comment->created_at->diffForHumans() }}</span>
                        </p>
                        <p class="text-xs text-[#6E6D67] dark:text-[#8E8D86] leading-relaxed">{{ $comment->content }}</p>
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
                <input type="text" name="content" required maxlength="1000" placeholder="Tulis komentar..."
                       class="w-full px-3.5 py-2 rounded-xl bg-white dark:bg-[#141416] border border-[#E2DFD7] dark:border-[#242426] text-[#0B0B0D] dark:text-[#EAE8E1] placeholder:text-[#908F88] dark:placeholder:text-[#6E6D67] text-xs outline-none focus:border-[#0B0B0D] dark:focus:border-[#EAE8E1] transition flex-1">
                <button type="submit" class="px-3.5 py-2 rounded-xl bg-[#0B0B0D] text-[#EAE8E1] dark:bg-[#EAE8E1] dark:text-[#0B0B0D] hover:bg-[#222225] dark:hover:bg-[#DCD9D0] text-xs font-bold transition shadow-xs shrink-0 cursor-pointer select-none active:scale-[0.98]">Kirim</button>
            </form>
        @endif
    @endauth
</article>
