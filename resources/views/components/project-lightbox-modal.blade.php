{{-- Single global project media lightbox (replaces per-card duplicate DOM) --}}
<div x-data="projectLightbox()"
     x-show="open"
     x-cloak
     x-transition.opacity.duration.150ms
     @keydown.escape.window="if (open) close()"
     @keydown.left.window="if (open && gallery.length > 1 && !['INPUT','TEXTAREA'].includes(document.activeElement?.tagName)) prev()"
     @keydown.right.window="if (open && gallery.length > 1 && !['INPUT','TEXTAREA'].includes(document.activeElement?.tagName)) next()"
     @click.self="close()"
     class="fixed inset-0 z-[100] bg-black/95 flex items-stretch justify-center"
     role="dialog"
     aria-modal="true"
     aria-label="Pratinjau media proyek">
    <template x-if="card">
        <div class="flex flex-col lg:flex-row w-full h-full" @click.stop>
            <!-- Left: sharp media stage + bottom engagement bar -->
            <div class="relative flex-1 flex flex-col min-w-0 min-h-0 bg-black">
                <!-- Top controls -->
                <div class="flex items-center justify-between px-4 pt-3 pb-1 shrink-0 z-10">
                    <button type="button" @click="close()" title="Tutup (Esc)" aria-label="Tutup lightbox"
                            class="w-9 h-9 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>

                    <div class="flex items-center gap-2">
                        <template x-if="gallery.length > 1">
                            <span class="text-white/70 text-xs font-mono px-2 py-1 rounded-md bg-white/5"
                                  x-text="(index + 1) + ' / ' + gallery.length"></span>
                        </template>
                        <button type="button" @click="sidebar = !sidebar"
                                :title="sidebar ? 'Sembunyikan panel info' : 'Tampilkan panel info'"
                                aria-label="Toggle panel info lightbox"
                                class="hidden lg:flex w-9 h-9 rounded-full bg-white/10 hover:bg-white/20 text-white items-center justify-center transition cursor-pointer">
                            <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': !sidebar }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Sharp image viewport -->
                <div class="relative flex-1 flex items-center justify-center px-4 min-h-0" @click.self="close()">
                    <img :src="gallery[index]" :alt="card.meta.title || ''"
                         class="max-w-full max-h-full object-contain rounded-none select-none">

                    <template x-if="gallery.length > 1">
                        <div>
                            <button type="button" @click="prev()"
                                    aria-label="Gambar sebelumnya"
                                    class="absolute left-3 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-black/60 hover:bg-black/80 border border-white/15 text-white flex items-center justify-center transition cursor-pointer">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                            </button>
                            <button type="button" @click="next()"
                                    aria-label="Gambar berikutnya"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-black/60 hover:bg-black/80 border border-white/15 text-white flex items-center justify-center transition cursor-pointer">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </button>
                        </div>
                    </template>
                </div>

                <!-- Bottom engagement bar -->
                <div class="shrink-0 flex items-center justify-center gap-8 sm:gap-12 py-3 px-4 border-t border-white/10 text-xs text-white/75">
                    <button type="button" @click="card.vote('up')"
                            class="inline-flex items-center gap-1.5 hover:text-emerald-400 transition cursor-pointer"
                            :class="{ 'text-emerald-400 font-semibold': card.userVote === 'up' }">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7"/></svg>
                        <span class="font-mono" x-text="card.score"></span>
                    </button>

                    <button type="button" @click="sidebar = true; $nextTick(() => $refs.lightboxCommentInput?.focus())"
                            class="inline-flex items-center gap-1.5 hover:text-white transition cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                        <span class="font-mono" x-text="card.commentsCount"></span>
                    </button>

                    <button type="button" @click="card.repost()"
                            class="inline-flex items-center gap-1.5 hover:text-emerald-400 transition cursor-pointer"
                            :class="{ 'text-emerald-400 font-semibold': card.isReposted }">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                        <span class="font-mono" x-text="card.repostsCount"></span>
                    </button>

                    <span class="inline-flex items-center gap-1.5 text-white/60">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        <span class="font-mono" x-text="card.meta.viewsFormatted"></span>
                    </span>

                    <button type="button" @click="card.toggleBookmark()"
                            class="inline-flex items-center gap-1.5 hover:text-emerald-400 transition cursor-pointer"
                            :class="{ 'text-emerald-400': card.isBookmarked }"
                            title="Simpan ke Koleksi">
                        <svg class="w-4 h-4" :fill="card.isBookmarked ? 'currentColor' : 'none'" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/></svg>
                    </button>
                </div>
            </div>

            <!-- Right: project info + inline reply & comment thread -->
            <aside x-show="sidebar"
                   class="w-full lg:w-[370px] xl:w-[400px] max-h-[45vh] lg:max-h-full shrink-0 bg-[var(--surface)] border-t lg:border-t-0 lg:border-l border-[var(--border-color)] flex flex-col min-h-0">
                <div class="flex-1 overflow-y-auto divide-y divide-[var(--border-color)]">
                    <!-- Author & Project Details -->
                    <div class="p-4 sm:p-5 space-y-3">
                        <div class="flex items-center justify-between gap-2.5">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <template x-if="card.meta.authorAvatar">
                                    <img :src="card.meta.authorAvatar" :alt="card.meta.authorName" referrerpolicy="no-referrer"
                                         class="w-8 h-8 rounded-full object-cover shrink-0 border border-[var(--border-color)]">
                                </template>
                                <template x-if="!card.meta.authorAvatar">
                                    <span class="w-8 h-8 rounded-full bg-[var(--surface-muted)] border border-[var(--border-color)] flex items-center justify-center text-xs font-bold text-[var(--primary)] shrink-0"
                                          x-text="(card.meta.authorName || 'A').charAt(0).toUpperCase()"></span>
                                </template>
                                <div class="min-w-0">
                                    <template x-if="card.meta.authorUrl">
                                        <div>
                                            <a :href="card.meta.authorUrl" class="block font-bold text-sm text-[var(--primary)] hover:underline truncate" x-text="card.meta.authorName"></a>
                                            <p class="text-[11px] font-mono text-[var(--text-muted)] truncate" x-text="'@' + card.meta.authorUsername"></p>
                                        </div>
                                    </template>
                                    <template x-if="!card.meta.authorUrl">
                                        <p class="font-bold text-sm text-[var(--primary)] truncate" x-text="card.meta.authorName || 'Anonim'"></p>
                                    </template>
                                </div>
                            </div>
                            <a :href="card.meta.showUrl"
                               @guest @click.prevent="close(); window.openAuthModal('login', 'detail')" @endguest
                               class="shrink-0 inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-[var(--primary)] text-[var(--primary-text)] hover:opacity-90 transition">
                                <span>Detail</span>
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </a>
                        </div>

                        <div>
                            <h3 class="font-bold text-[15px] text-[var(--primary)] leading-snug">
                                <a :href="card.meta.showUrl"
                                   @guest @click.prevent="close(); window.openAuthModal('login', 'detail')" @endguest
                                   class="hover:text-emerald-300 transition" x-text="card.meta.title"></a>
                            </h3>
                            <p x-show="card.meta.tagline" class="mt-1 text-[13px] text-[var(--text-soft)] leading-relaxed whitespace-pre-line" x-text="card.meta.tagline"></p>
                        </div>

                        <div class="flex flex-wrap items-center gap-1.5 pt-0.5"
                             x-show="card.meta.category || (card.meta.techStacks && card.meta.techStacks.length) || card.meta.demoUrl || card.meta.githubUrl">
                            <span x-show="card.meta.category"
                                  class="text-[11px] font-medium px-2 py-0.5 rounded-md bg-[var(--surface-muted)] text-[var(--text-soft)] border border-[var(--border-color)]"
                                  x-text="card.meta.category"></span>
                            <template x-for="tech in (card.meta.techStacks || [])" :key="tech">
                                <span class="text-[11px] font-mono px-2 py-0.5 rounded-md bg-[var(--surface-subtle)] text-[var(--text-muted)] border border-[var(--border-color)]" x-text="tech"></span>
                            </template>
                            <a x-show="card.meta.demoUrl" :href="card.meta.demoUrl" target="_blank" rel="noopener noreferrer"
                               class="text-[11px] font-medium px-2 py-0.5 rounded-md bg-emerald-500/10 text-emerald-400 border border-emerald-500/25 hover:bg-emerald-500/20 transition">Live Demo</a>
                            <a x-show="card.meta.githubUrl" :href="card.meta.githubUrl" target="_blank" rel="noopener noreferrer"
                               class="text-[11px] font-mono px-2 py-0.5 rounded-md bg-[var(--surface-subtle)] text-[var(--text-muted)] border border-[var(--border-color)] hover:text-[var(--primary)] transition">GitHub</a>
                        </div>

                        <p class="text-[11px] text-[var(--text-faint)] font-mono">
                            <span x-text="card.meta.createdAtLabel"></span> &middot; <span class="text-[var(--text-muted)]" x-text="card.meta.viewsFormatted + ' tayangan'"></span>
                        </p>
                    </div>

                    <!-- Inline Reply Input -->
                    <form @submit.prevent="card.submitComment()" class="p-4 flex items-start gap-2.5">
                        @auth
                            <x-user-avatar :user="auth()->user()" size="sm" />
                        @endauth
                        <div class="flex-1 min-w-0 space-y-2">
                            <textarea x-ref="lightboxCommentInput"
                                      x-model="card.newComment"
                                      rows="2"
                                      maxlength="1000"
                                      @focus="@guest if (window.openAuthModal) { $el.blur(); close(); window.openAuthModal('login', 'interact'); } @endguest"
                                      placeholder="Tulis komentar atau masukan..."
                                      class="w-full bg-transparent border border-[var(--border-color)] focus:border-[var(--border-strong)] rounded-lg px-3 py-2 text-xs text-[var(--primary)] placeholder-[var(--text-faint)] resize-none focus:outline-none"></textarea>
                            <div class="flex items-center justify-between">
                                <span class="text-[10px] font-mono text-[var(--text-faint)]" x-text="(card.newComment || '').length + '/1000'"></span>
                                <button type="submit"
                                        :disabled="card.isSubmittingComment || !(card.newComment || '').trim()"
                                        class="px-3.5 py-1.5 rounded-full text-xs font-bold bg-[var(--primary)] text-[var(--primary-text)] disabled:opacity-40 hover:opacity-90 transition cursor-pointer">
                                    <span x-text="card.isSubmittingComment ? 'Mengirim...' : 'Balas'"></span>
                                </button>
                            </div>
                        </div>
                    </form>

                    <!-- Comments Thread -->
                    <div class="p-4 space-y-3.5">
                        <div class="flex items-center justify-between">
                            <span class="text-[11px] font-mono uppercase tracking-wider text-[var(--text-muted)]">Komentar (<span x-text="card.commentsCount"></span>)</span>
                            <a :href="card.meta.showUrl + '#comments'"
                               @guest @click.prevent="close(); window.openAuthModal('login', 'detail')" @endguest
                               class="text-[11px] text-emerald-400 hover:underline">Buka semua</a>
                        </div>

                        <template x-if="card.commentsLoading">
                            <div class="space-y-3 py-2">
                                <div class="h-10 rounded-lg bg-[var(--surface-muted)] animate-pulse"></div>
                                <div class="h-10 rounded-lg bg-[var(--surface-muted)] animate-pulse"></div>
                            </div>
                        </template>

                        <template x-if="!card.commentsLoading && card.comments.length === 0">
                            <p class="text-xs text-[var(--text-muted)] py-3 text-center">Belum ada komentar. Jadilah yang pertama memberi masukan.</p>
                        </template>

                        <template x-for="c in card.comments" :key="c.id">
                            <div class="flex items-start gap-2.5 text-xs">
                                <template x-if="c.avatar">
                                    <img :src="c.avatar" :alt="c.author" referrerpolicy="no-referrer" class="w-7 h-7 rounded-full object-cover shrink-0 border border-[var(--border-color)]">
                                </template>
                                <template x-if="!c.avatar">
                                    <span class="w-7 h-7 rounded-full bg-[var(--surface-muted)] border border-[var(--border-color)] flex items-center justify-center text-[11px] font-bold text-[var(--primary)] shrink-0"
                                          x-text="(c.author || 'A').charAt(0).toUpperCase()"></span>
                                </template>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        <span class="font-semibold text-[var(--primary)] truncate" x-text="c.author"></span>
                                        <span class="text-[10.5px] font-mono text-[var(--text-faint)] truncate" x-show="c.username" x-text="'@' + c.username"></span>
                                        <span class="text-[10.5px] text-[var(--text-faint)]" x-text="'· ' + c.created_at"></span>
                                    </div>
                                    <p class="mt-0.5 text-[var(--text-soft)] leading-relaxed break-words whitespace-pre-line" x-text="c.content"></p>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </aside>
        </div>
    </template>
</div>
