@props([
    'project',
    'initialVote' => null,
    'isBookmarked' => false,
    'isReposted' => false,
])

<div class="flex items-center justify-between gap-3 pt-3 border-t border-[#e4e4e7]/80 dark:border-[#1f1f1f] text-xs">
    <!-- Left: Like + Comments Count -->
    <div class="flex items-center gap-1.5 sm:gap-2">
        <!-- Like Button -->
        <button @click="vote('up')"
                type="button"
                :disabled="isVoting"
                :aria-pressed="userVote === 'up'"
                aria-label="Sukai project {{ $project->title }}"
                :class="{
                    'bg-[#0070f3] text-white dark:bg-[#3291ff] dark:text-[#000000]': userVote === 'up',
                    'text-[#63636b] dark:text-[#a0a0a0] hover:text-[#0070f3] dark:hover:text-[#3291ff]': userVote !== 'up'
                }"
                class="btn-upvote inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg font-semibold transition active:scale-95 disabled:opacity-60">
            <svg class="w-3.5 h-3.5" :fill="userVote === 'up' ? 'currentColor' : 'none'" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
            </svg>
            <span class="font-mono text-xs" x-text="score">{{ $project->score }}</span>
        </button>

        <!-- Comments Count Link -->
        <a href="{{ route('projects.show', $project->slug) }}#komentar" 
           class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[#63636b] dark:text-[#a0a0a0] hover:text-[#18181b] dark:hover:text-white hover:bg-[#f5f5f5] dark:hover:bg-[#111111] transition"
           aria-label="{{ $project->comments_count }} komentar untuk {{ $project->title }}">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
            </svg>
            <span class="font-mono text-xs">{{ $project->comments_count }}</span>
        </a>

        <!-- Views Count (Truthful Metric) -->
        <span class="hidden sm:inline-flex items-center gap-1 px-2 text-[#63636b] dark:text-[#a0a0a0] font-mono text-[11px]" title="Jumlah penayangan">
            <svg class="w-3.5 h-3.5 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
            </svg>
            <span>{{ $project->views_count }}</span>
        </span>
    </div>

    <!-- Right: Repost, Bookmark & Share Actions -->
    <div class="flex items-center gap-1.5">
        <!-- Repost Button (disabled for own project) -->
        @auth
            @if($project->user_id !== auth()->id())
                <button @click="repost()"
                        type="button"
                        :disabled="isReposting"
                        :aria-pressed="isReposted"
                        :title="isReposted ? 'Batalkan repost' : 'Bagikan ulang ke profilmu'"
                        :class="{
                            'text-[#0070f3] dark:text-[#3291ff] bg-[#e8f2ff] dark:bg-[#3291ff]/12 border-[#3291ff]/30 dark:border-[#3291ff]/30': isReposted,
                            'text-[#63636b] dark:text-[#a0a0a0] hover:text-[#0070f3] dark:hover:text-[#3291ff] border-transparent hover:bg-[#f5f5f5] dark:hover:bg-[#111111]': !isReposted
                        }"
                        class="btn-repost inline-flex items-center gap-1 px-2 py-1.5 rounded-lg border transition active:scale-95 disabled:opacity-60">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                    </svg>
                    <span class="font-mono text-[11px] tabular-nums" x-text="repostsCount > 0 ? repostsCount : ''"></span>
                </button>
            @endif
        @endauth

        <!-- Bookmark Toggle -->
        <button @click="toggleBookmark()"
                type="button"
                :disabled="isBookmarking"
                :aria-pressed="isBookmarked"
                aria-label="Simpan project ke koleksi"
                :class="{
                    'text-[#0070f3] dark:text-[#3291ff] bg-[#e8f2ff] dark:bg-[#3291ff]/12 border-[#3291ff]/30 dark:border-[#3291ff]/30': isBookmarked,
                    'text-[#63636b] dark:text-[#a0a0a0] hover:text-[#18181b] dark:hover:text-white border-transparent hover:bg-[#f5f5f5] dark:hover:bg-[#111111]': !isBookmarked
                }"
                class="btn-bookmark p-1.5 rounded-lg border transition active:scale-95 disabled:opacity-60"
                title="Simpan ke Koleksi">
            <svg class="w-4 h-4" :fill="isBookmarked ? 'currentColor' : 'none'" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
            </svg>
        </button>

        <!-- Share / Copy Link Button -->
        <button @click="copyLink()"
                type="button"
                aria-label="Salin tautan project"
                class="p-1.5 rounded-lg text-[#63636b] dark:text-[#a0a0a0] hover:text-[#18181b] dark:hover:text-white hover:bg-[#f5f5f5] dark:hover:bg-[#111111] transition active:scale-95"
                title="Salin Tautan">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
            </svg>
        </button>
    </div>
</div>
