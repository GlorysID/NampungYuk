@props([
    'project',
    'initialVote' => null,
    'isBookmarked' => false,
    'isReposted' => false,
])

<div class="flex items-center justify-between gap-2 pt-3 border-t border-[var(--border-color)]/70 text-[13px]">
    <!-- Left: Upvote Pill + Comments + Views -->
    <div class="flex items-center gap-1.5 sm:gap-2">
        <!-- Upvote Button (Primary Appreciation Metric) -->
        @guest
            <button type="button"
                    @click.stop="if (window.openAuthModal) { window.openAuthModal('login'); } else { window.location.href = '{{ route('login') }}'; }"
                    aria-label="Upvote project {{ $project->title }}"
                    class="btn-upvote h-8 inline-flex items-center gap-1.5 px-2.5 rounded-lg bg-[var(--surface-subtle)] border border-[var(--border-color)] font-semibold text-[var(--text-muted)] hover:text-emerald-400 hover:border-emerald-500/35 hover:bg-emerald-500/10 transition active:scale-95 cursor-pointer">
                <svg class="w-3.5 h-3.5 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7"/>
                </svg>
                <span class="font-mono text-xs tabular-nums" x-text="score">{{ $project->score }}</span>
            </button>
        @else
            <button @click.stop="vote('up')"
                    type="button"
                    :disabled="isVoting"
                    :aria-pressed="userVote === 'up'"
                    aria-label="Upvote project {{ $project->title }}"
                    :class="userVote === 'up'
                        ? 'text-emerald-400 bg-emerald-500/12 border-emerald-500/35 font-bold'
                        : 'text-[var(--text-muted)] bg-[var(--surface-subtle)] border-[var(--border-color)] hover:text-emerald-400 hover:border-emerald-500/35 hover:bg-emerald-500/10 font-semibold'"
                    class="btn-upvote h-8 inline-flex items-center gap-1.5 px-2.5 rounded-lg border transition active:scale-95 disabled:opacity-60 cursor-pointer">
                <svg class="w-3.5 h-3.5 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7"/>
                </svg>
                <span class="font-mono text-xs tabular-nums" x-text="score">{{ $project->score }}</span>
            </button>
        @endguest

        <!-- Comments Link -->
        <a href="{{ route('projects.show', $project->slug) }}#komentar"
           @click.stop
           class="h-8 inline-flex items-center gap-1.5 px-2.5 rounded-lg text-[var(--text-muted)] hover:text-[var(--primary)] hover:bg-[var(--surface-hover)] transition active:scale-95"
           aria-label="{{ $project->comments_count }} komentar untuk {{ $project->title }}">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
            </svg>
            <span class="font-mono text-xs tabular-nums">{{ $project->comments_count }}</span>
        </a>

        <!-- Views Count -->
        <span class="hidden sm:inline-flex items-center gap-1.5 px-2 text-[var(--text-muted)] font-mono text-xs select-none" title="Jumlah penayangan">
            <svg class="w-4 h-4 opacity-70 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
            </svg>
            <span class="tabular-nums">{{ $project->views_count }}</span>
        </span>
    </div>

    <!-- Right: Repost + Bookmark + Overflow -->
    <div class="flex items-center gap-1">
        <!-- Repost Button -->
        @auth
            @if($project->user_id !== auth()->id())
                <button @click.stop="repost()"
                        type="button"
                        :disabled="isReposting"
                        :aria-pressed="isReposted"
                        :title="isReposted ? 'Batalkan repost' : 'Bagikan ulang ke profilmu'"
                        :class="isReposted
                            ? 'text-emerald-400 bg-emerald-500/10 font-semibold'
                            : 'text-[var(--text-muted)] hover:text-emerald-400 hover:bg-[var(--surface-hover)]'"
                        class="btn-repost h-8 inline-flex items-center gap-1.5 px-2.5 rounded-lg transition active:scale-95 disabled:opacity-60 cursor-pointer">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                    </svg>
                    <span class="font-mono text-xs tabular-nums" x-show="repostsCount > 0" x-text="repostsCount"></span>
                </button>
            @endif
        @endauth

        <!-- Bookmark Toggle -->
        @guest
            <button type="button"
                    @click.stop="if (window.openAuthModal) { window.openAuthModal('login'); } else { window.location.href = '{{ route('login') }}'; }"
                    aria-label="Simpan project ke koleksi"
                    class="btn-bookmark w-8 h-8 flex items-center justify-center rounded-lg text-[var(--text-muted)] hover:text-amber-400 hover:bg-[var(--surface-hover)] transition active:scale-95 cursor-pointer"
                    title="Simpan ke Koleksi">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                </svg>
            </button>
        @else
            <button @click.stop="toggleBookmark()"
                    type="button"
                    :disabled="isBookmarking"
                    :aria-pressed="isBookmarked"
                    aria-label="Simpan project ke koleksi"
                    :class="isBookmarked
                        ? 'text-amber-400 bg-amber-500/10'
                        : 'text-[var(--text-muted)] hover:text-amber-400 hover:bg-[var(--surface-hover)]'"
                    class="btn-bookmark w-8 h-8 flex items-center justify-center rounded-lg transition active:scale-95 disabled:opacity-60 cursor-pointer"
                    title="Simpan ke Koleksi">
                <svg class="w-4 h-4 shrink-0" :fill="isBookmarked ? 'currentColor' : 'none'" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                </svg>
            </button>
        @endguest

        <!-- Overflow menu (⋯): Share + Report + Moderation -->
        <div class="relative" x-data="{ menuOpen: false }" @click.outside="menuOpen = false">
            <button @click.stop="menuOpen = !menuOpen"
                    type="button"
                    aria-label="Menu lainnya"
                    aria-haspopup="true"
                    :aria-expanded="menuOpen"
                    class="w-8 h-8 flex items-center justify-center rounded-lg text-[var(--text-muted)] hover:text-[var(--primary)] hover:bg-[var(--surface-hover)] transition active:scale-95 cursor-pointer">
                <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 24 24">
                    <circle cx="5" cy="12" r="2"/><circle cx="12" cy="12" r="2"/><circle cx="19" cy="12" r="2"/>
                </svg>
            </button>

            <div x-show="menuOpen" x-cloak
                 x-transition:enter="transition ease-out duration-120"
                 x-transition:enter-start="opacity-0 -translate-y-1"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="absolute right-0 bottom-full mb-1.5 z-40 min-w-[190px] rounded-xl bg-[var(--surface)] border border-[var(--border-color)] shadow-xl overflow-hidden text-xs">
                <!-- Share -->
                <button @click.stop="copyLink(); menuOpen = false"
                        type="button"
                        class="w-full flex items-center gap-2.5 px-3.5 py-2.5 text-left text-[var(--primary)] hover:bg-[var(--surface-hover)] transition cursor-pointer">
                    <svg class="w-4 h-4 text-[var(--text-muted)] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/></svg>
                    <span>Salin Tautan</span>
                </button>

                <!-- Report (hidden from own project) -->
                @auth
                    @if(auth()->id() !== $project->user_id)
                        <div class="border-t border-[var(--border-color)]"></div>
                        <button @click.stop="window.openReportModal ? window.openReportModal({ type: 'project', id: {{ $project->id }}, title: '{{ addslashes($project->title) }}' }) : null; menuOpen = false"
                                type="button"
                                class="w-full flex items-center gap-2.5 px-3.5 py-2.5 text-left text-rose-400 hover:bg-rose-500/10 transition cursor-pointer">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            <span>Laporkan Project</span>
                        </button>
                    @endif
                @else
                    <div class="border-t border-[var(--border-color)]"></div>
                    <button @click.stop="if (window.openAuthModal) { window.openAuthModal('login'); } else { window.location.href = '{{ route('login') }}'; }"
                            type="button"
                            class="w-full flex items-center gap-2.5 px-3.5 py-2.5 text-left text-rose-400 hover:bg-rose-500/10 transition cursor-pointer">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <span>Laporkan Project</span>
                    </button>
                @endauth

                <!-- Admin moderation: warn / un-warn this project -->
                @if(auth()->check() && (auth()->user()->isAdmin() || auth()->user()->is_admin))
                    <div class="border-t border-[var(--border-color)]"></div>
                    @if($project->is_flagged || $project->warning_notes)
                        <button @click.stop="window.openWarnProjectModal({ id: {{ $project->id }}, title: '{{ addslashes($project->title) }}', action: 'remove' }); menuOpen = false"
                                type="button"
                                class="w-full flex items-center gap-2.5 px-3.5 py-2.5 text-left text-emerald-400 hover:bg-emerald-500/10 transition cursor-pointer">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Cabut Peringatan</span>
                        </button>
                    @else
                        <button @click.stop="window.openWarnProjectModal({ id: {{ $project->id }}, title: '{{ addslashes($project->title) }}', action: 'set' }); menuOpen = false"
                                type="button"
                                class="w-full flex items-center gap-2.5 px-3.5 py-2.5 text-left text-amber-400 hover:bg-amber-500/10 transition cursor-pointer">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            <span>Beri Peringatan (Admin)</span>
                        </button>
                    @endif
                @endif
            </div>
        </div>
    </div>
</div>
