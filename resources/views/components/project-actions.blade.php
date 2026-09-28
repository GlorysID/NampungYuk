@props([
    'project',
    'initialVote' => null,
    'isBookmarked' => false,
])

<div class="flex items-center justify-between gap-3 pt-3 border-t border-[#DDE5E2]/80 dark:border-[#24322F] text-xs">
    <!-- Left: Voting Controls + Comments Count -->
    <div class="flex items-center gap-1.5 sm:gap-2">
        <!-- Upvote / Downvote Pill Container -->
        <div class="inline-flex items-center rounded-lg bg-[#EBF0EE] dark:bg-[#1F2C29] p-0.5 border border-[#DDE5E2] dark:border-[#24322F]">
            <!-- Upvote Button -->
            <button @click="vote('up')"
                    type="button"
                    :disabled="isVoting"
                    :aria-pressed="userVote === 'up'"
                    aria-label="Upvote project {{ $project->title }}"
                    :class="{
                        'bg-[#0F766E] text-white shadow-2xs': userVote === 'up',
                        'text-[#66736F] dark:text-[#8E9F9B] hover:text-[#0F766E] dark:hover:text-teal-300': userVote !== 'up'
                    }"
                    class="btn-upvote inline-flex items-center gap-1 px-2.5 py-1 rounded-md font-semibold transition active:scale-95 disabled:opacity-60">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7"/>
                </svg>
                <span class="font-mono text-xs" x-text="score">{{ $project->score }}</span>
            </button>

            <span class="h-3 w-px bg-[#DDE5E2] dark:bg-[#24322F] mx-0.5" aria-hidden="true"></span>

            <!-- Downvote Button -->
            <button @click="vote('down')"
                    type="button"
                    :disabled="isVoting"
                    :aria-pressed="userVote === 'down'"
                    aria-label="Downvote project {{ $project->title }}"
                    :class="{
                        'bg-rose-600 text-white shadow-2xs': userVote === 'down',
                        'text-[#66736F] dark:text-[#8E9F9B] hover:text-rose-600': userVote !== 'down'
                    }"
                    class="btn-downvote p-1 rounded-md transition active:scale-95 disabled:opacity-60">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
        </div>

        <!-- Comments Count Link -->
        <a href="{{ route('projects.show', $project->slug) }}#komentar" 
           class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[#66736F] dark:text-[#8E9F9B] hover:text-[#17211F] dark:hover:text-white hover:bg-[#F0F4F2] dark:hover:bg-[#1B2623] transition"
           aria-label="{{ $project->comments_count }} komentar untuk {{ $project->title }}">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
            </svg>
            <span class="font-mono text-xs">{{ $project->comments_count }}</span>
        </a>

        <!-- Views Count (Truthful Metric) -->
        <span class="hidden sm:inline-flex items-center gap-1 px-2 text-[#66736F] dark:text-[#8E9F9B] font-mono text-[11px]" title="Jumlah penayangan">
            <svg class="w-3.5 h-3.5 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
            </svg>
            <span>{{ $project->views_count }}</span>
        </span>
    </div>

    <!-- Right: Bookmark & Share Actions -->
    <div class="flex items-center gap-1.5">
        <!-- Bookmark Toggle -->
        <button @click="toggleBookmark()"
                type="button"
                :disabled="isBookmarking"
                :aria-pressed="isBookmarked"
                aria-label="Simpan project ke koleksi"
                :class="{
                    'text-[#0F766E] dark:text-teal-400 bg-teal-50 dark:bg-teal-950/40 border-teal-200 dark:border-teal-800/40': isBookmarked,
                    'text-[#66736F] dark:text-[#8E9F9B] hover:text-[#17211F] dark:hover:text-white border-transparent hover:bg-[#F0F4F2] dark:hover:bg-[#1B2623]': !isBookmarked
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
                class="p-1.5 rounded-lg text-[#66736F] dark:text-[#8E9F9B] hover:text-[#17211F] dark:hover:text-white hover:bg-[#F0F4F2] dark:hover:bg-[#1B2623] transition active:scale-95"
                title="Salin Tautan">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
            </svg>
        </button>
    </div>
</div>
