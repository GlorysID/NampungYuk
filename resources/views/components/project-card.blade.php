@props([
    'project',
    'initialVote' => null,
    'isBookmarked' => false,
    'isReposted' => false,
    'isOwner' => false,
    'uid' => 'p',
])

<article class="project-nav-card bg-[var(--surface)] border border-[var(--border-color)] hover:border-[var(--border-strong)] rounded-2xl p-5 transition relative mb-4 last:mb-0 space-y-3.5 group cursor-pointer"
         @click="if (! $event.target.closest('a, button, input, textarea, select, [data-no-card-nav]')) { window.location.href = '{{ route('projects.show', $project->slug) }}'; }"
         x-data="{
             score: {{ $project->score }},
             userVote: '{{ $initialVote }}',
             isBookmarked: {{ $isBookmarked ? 'true' : 'false' }},
             isReposted: {{ $isReposted ? 'true' : 'false' }},
             repostsCount: {{ $project->reposts_count }},
             isVoting: false,
             isBookmarking: false,
             isReposting: false,
             lightboxOpen: false,
             lightboxIndex: 0,
             lightboxSidebar: true,
             commentsCount: {{ $project->comments_count }},
             comments: [],
             commentsLoaded: false,
             commentsLoading: false,
             newComment: '',
             isSubmittingComment: false,
             gallery: {{ Illuminate\Support\Js::from($project->galleryImages()) }},
             openLightbox(i) {
                 this.lightboxIndex = i;
                 this.lightboxOpen = true;
                 this.loadComments();
             },
             async loadComments() {
                 if (this.commentsLoaded || this.commentsLoading) return;
                 this.commentsLoading = true;
                 try {
                     const res = await fetch('{{ route('projects.comments.index', $project) }}', {
                         headers: { 'Accept': 'application/json' }
                     });
                     if (res.ok) {
                         const data = await res.json();
                         this.comments = data.comments || [];
                         if (typeof data.comments_count === 'number') {
                             this.commentsCount = data.comments_count;
                         }
                         this.commentsLoaded = true;
                     }
                 } catch (e) {
                     // Silent fallback: user can still open full project page
                 } finally {
                     this.commentsLoading = false;
                 }
             },
             async submitComment() {
                 @guest
                     if (window.openAuthModal) { window.openAuthModal('login'); return; }
                 @endguest
                 const content = this.newComment.trim();
                 if (!content || this.isSubmittingComment) return;
                 this.isSubmittingComment = true;
                 try {
                     const res = await fetch('{{ route('projects.comment', $project) }}', {
                         method: 'POST',
                         headers: {
                             'Content-Type': 'application/json',
                             'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                             'Accept': 'application/json'
                         },
                         body: JSON.stringify({ content })
                     });
                     if (res.status === 401) {
                         if (window.openAuthModal) { window.openAuthModal('login'); } else { window.location.href = '{{ route('login') }}'; }
                         return;
                     }
                     const data = await res.json();
                     if (data.success && data.comment) {
                         this.comments.unshift(data.comment);
                         this.commentsCount = data.comments_count ?? (this.commentsCount + 1);
                         this.newComment = '';
                         this.commentsLoaded = true;
                         if (window.notify) notify('Komentar terkirim!');
                     } else if (window.notify) {
                         notify(data.message || 'Gagal mengirim komentar.');
                     }
                 } catch (e) {
                     if (window.notify) notify('Gagal mengirim komentar.');
                 } finally {
                     this.isSubmittingComment = false;
                 }
             },
             async repost() {
                 @guest
                     if (window.openAuthModal) { window.openAuthModal('login'); return; }
                 @endguest
                 if (this.isReposting) return;
                 this.isReposting = true;
                 try {
                     const res = await fetch('{{ route('projects.repost', $project) }}', {
                         method: 'POST',
                         headers: {
                             'Content-Type': 'application/json',
                             'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                             'Accept': 'application/json'
                         }
                     });
                     if (res.status === 401) {
                         if (window.openAuthModal) { window.openAuthModal('login'); } else { window.location.href = '{{ route('login') }}'; }
                         return;
                     }
                     const data = await res.json();
                     if (data.success) {
                         this.isReposted = data.reposted;
                         this.repostsCount = data.reposts_count;
                         if (window.notify) notify(data.message);
                     } else if (window.notify) {
                         notify(data.message || 'Gagal repost.');
                     }
                 } catch(e) {
                     if (window.notify) notify('Gagal repost.');
                 } finally {
                     this.isReposting = false;
                 }
             },
             async vote(type) {
                 @guest
                     if (window.openAuthModal) { window.openAuthModal('login'); return; }
                 @endguest
                 if (this.isVoting) return;
                 this.isVoting = true;
                 try {
                     const res = await fetch('{{ route('projects.vote', $project) }}', {
                         method: 'POST',
                         headers: {
                             'Content-Type': 'application/json',
                             'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                             'Accept': 'application/json'
                         },
                         body: JSON.stringify({ type })
                     });
                     if (res.status === 401) {
                         if (window.openAuthModal) { window.openAuthModal('login'); } else { window.location.href = '{{ route('login') }}'; }
                         return;
                     }
                     const data = await res.json();
                     if (data.success) {
                         this.score = data.score;
                         this.userVote = data.user_vote;
                         if (window.notify) {
                             notify(type === 'up' ? 'Upvoted! Terima kasih atas apresiasimu.' : 'Feedback tercatat.');
                         }
                     }
                 } catch(e) {
                     if (window.notify) notify('Gagal memberikan vote');
                 } finally {
                     this.isVoting = false;
                 }
             },
             async toggleBookmark() {
                 @guest
                     if (window.openAuthModal) { window.openAuthModal('login'); return; }
                 @endguest
                 if (this.isBookmarking) return;
                 this.isBookmarking = true;
                 try {
                     const res = await fetch('{{ route('projects.bookmark', $project) }}', {
                         method: 'POST',
                         headers: {
                             'Content-Type': 'application/json',
                             'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                             'Accept': 'application/json'
                         }
                     });
                     if (res.status === 401) {
                         if (window.openAuthModal) { window.openAuthModal('login'); } else { window.location.href = '{{ route('login') }}'; }
                         return;
                     }
                     const data = await res.json();
                     if (data.success) {
                         this.isBookmarked = data.bookmarked;
                         if (window.notify) {
                             notify(this.isBookmarked ? 'Proyek disimpan ke Koleksi!' : 'Dihapus dari Koleksi.');
                         }
                     }
                 } catch(e) {
                     if (window.notify) notify('Gagal menyimpan bookmark');
                 } finally {
                     this.isBookmarking = false;
                 }
             },
             copyLink() {
                 navigator.clipboard.writeText('{{ route('projects.show', $project->slug) }}');
                 if (window.notify) notify('Tautan project berhasil disalin!');
             }
         }">

    @if($project->isRepost())
        <div class="flex items-center gap-1.5 text-[11px] font-medium text-[var(--text-muted)] pb-2 border-b border-[var(--border-color)]/60">
            <svg class="w-3.5 h-3.5 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
            <a href="{{ $project->repostedFrom ? route('projects.show', $project->repostedFrom->slug) : '#' }}"
               class="hover:text-[var(--primary)] transition truncate"
               @click.stop>
                Repost{{ $project->repostedFrom ? ' dari '.$project->repostedFrom->title : '' }}
            </a>
        </div>
    @endif

    <!-- 1. Author Header -->
    <x-project-meta :project="$project" :is-owner="$isOwner" />

    <!-- 2. Title & Tagline -->
    <div class="space-y-1.5">
        <h2 class="text-[17.5px] sm:text-[19px] font-bold text-[var(--primary)] leading-snug tracking-[-0.015em] group-hover:text-emerald-300 transition-colors">
            <a href="{{ route('projects.show', $project->slug) }}" class="card-detail-link focus-visible:rounded">
                {{ $project->title }}
            </a>
        </h2>

        @if($project->tagline)
            <p class="text-[13.5px] sm:text-[14px] text-[var(--text-muted)] leading-relaxed line-clamp-2">
                {{ $project->tagline }}
            </p>
        @endif

        @if($project->hasWarning() && ($isOwner || auth()->user()?->is_admin))
            <div class="mt-2 p-3 rounded-xl border border-amber-500/30 bg-amber-950/30 text-amber-200 text-xs flex items-start gap-2.5" data-no-card-nav>
                <svg class="w-4 h-4 text-amber-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <div class="min-w-0 space-y-0.5">
                    <p class="font-bold text-xs text-amber-100 uppercase tracking-wider">
                        Peringatan Moderasi
                        {{ $isOwner ? '— Hanya Tampil Untuk Kamu & Admin' : '(hanya terlihat oleh pemilik & admin)' }}
                    </p>
                    <p class="text-xs">{{ $project->warning_notes ?: 'Proyek ini sedang dalam peninjauan moderasi dan disembunyikan dari publik.' }}</p>
                </div>
            </div>
        @endif
    </div>

    <!-- 3. Full-Width Gallery Media Showcase -->
    @php $gallery = $project->galleryImages(); $galleryCount = count($gallery); @endphp
    @if($galleryCount > 0)
        <div class="group/media relative block w-full overflow-hidden rounded-lg border border-[var(--border-color)] bg-[var(--bg-app)] ring-1 ring-inset ring-white/[0.05]">
            @if($galleryCount === 1)
                <button type="button" class="block w-full aspect-video overflow-hidden cursor-pointer focus:outline-none" data-no-card-nav @click.stop="openLightbox(0)">
                    <img src="{{ $gallery[0] }}" alt="{{ $project->title }}" loading="lazy" decoding="async"
                         onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1555066931-4365d14bab8c?auto=format&fit=crop&w=1200&q=80';"
                         class="w-full h-full object-cover transition-transform duration-500 ease-[cubic-bezier(0.22,1,0.36,1)] group-hover/media:scale-[1.015]">
                </button>
            @elseif($galleryCount === 2)
                <div class="grid grid-cols-2 gap-1 aspect-video">
                    @foreach(array_slice($gallery, 0, 2) as $i => $img)
                        <button type="button" class="group/tile relative block w-full h-full overflow-hidden cursor-pointer focus:outline-none" data-no-card-nav @click.stop="openLightbox({{ $i }})">
                            <img src="{{ $img }}" alt="" loading="lazy" decoding="async"
                                 class="w-full h-full object-cover transition-transform duration-300 group-hover/tile:scale-[1.03]">
                        </button>
                    @endforeach
                </div>
            @elseif($galleryCount === 3)
                <div class="grid grid-cols-2 grid-rows-2 gap-1 aspect-video">
                    <button type="button" class="group/tile relative block w-full h-full overflow-hidden row-span-2 cursor-pointer focus:outline-none" data-no-card-nav @click.stop="openLightbox(0)">
                        <img src="{{ $gallery[0] }}" alt="" loading="lazy" decoding="async"
                             class="w-full h-full object-cover transition-transform duration-300 group-hover/tile:scale-[1.03]">
                    </button>
                    <button type="button" class="group/tile relative block w-full h-full overflow-hidden cursor-pointer focus:outline-none" data-no-card-nav @click.stop="openLightbox(1)">
                        <img src="{{ $gallery[1] }}" alt="" loading="lazy" decoding="async"
                             class="w-full h-full object-cover transition-transform duration-300 group-hover/tile:scale-[1.03]">
                    </button>
                    <button type="button" class="group/tile relative block w-full h-full overflow-hidden cursor-pointer focus:outline-none" data-no-card-nav @click.stop="openLightbox(2)">
                        <img src="{{ $gallery[2] }}" alt="" loading="lazy" decoding="async"
                             class="w-full h-full object-cover transition-transform duration-300 group-hover/tile:scale-[1.03]">
                    </button>
                </div>
            @else
                <div class="grid grid-cols-2 grid-rows-2 gap-1 aspect-video">
                    @foreach(array_slice($gallery, 0, 4) as $i => $img)
                        <button type="button" class="group/tile relative block w-full h-full overflow-hidden cursor-pointer focus:outline-none" data-no-card-nav @click.stop="openLightbox({{ $i }})">
                            <img src="{{ $img }}" alt="" loading="lazy" decoding="async"
                                 class="w-full h-full object-cover transition-transform duration-300 group-hover/tile:scale-[1.03]">
                            @if($i === 3 && $galleryCount > 4)
                                <span class="absolute inset-0 bg-black/60 text-white flex items-center justify-center text-lg font-bold backdrop-blur-xs">
                                    +{{ $galleryCount - 4 }}
                                </span>
                            @endif
                        </button>
                    @endforeach
                </div>
            @endif

            @if($galleryCount > 1)
                <span class="absolute bottom-2.5 right-2.5 inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-mono font-semibold bg-black/75 text-white border border-white/10">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    {{ $galleryCount }}
                </span>
            @endif
        </div>
    @endif

    <!-- 4. Structured Metadata Strip: Tech Stack (Left) + Live Links (Right) -->
    @php
        $hasLinks = $project->demo_url || $project->prototype_url || $project->github_url;
        $hasTech = is_array($project->tech_stacks) && count($project->tech_stacks) > 0;
    @endphp
    @if($hasLinks || $hasTech)
        <div class="flex flex-wrap items-center justify-between gap-2.5 pt-0.5">
            <!-- Tech Stack -->
            <div class="flex flex-wrap items-center gap-1.5 min-w-0">
                @if($hasTech)
                    @foreach(array_slice($project->tech_stacks, 0, 4) as $tech)
                        <x-tech-pill :name="$tech" variant="compact" />
                    @endforeach
                    @if(count($project->tech_stacks) > 4)
                        <span class="text-[11px] font-mono text-[var(--text-faint)] px-1">
                            +{{ count($project->tech_stacks) - 4 }}
                        </span>
                    @endif
                @endif
            </div>

            <!-- External Links -->
            @if($hasLinks)
                <div class="flex items-center gap-1.5 shrink-0">
                    @if($project->demo_url)
                        <a href="{{ $project->demo_url }}" target="_blank" rel="noopener noreferrer" @click.stop
                           class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11.5px] font-medium bg-[var(--surface-subtle)] border border-[var(--border-color)] hover:border-emerald-500/40 text-[var(--primary)] hover:text-emerald-400 transition">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                            <span>Demo</span>
                            <svg class="w-3 h-3 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 17L17 7M7 7h10v10"/></svg>
                        </a>
                    @endif
                    @if($project->prototype_url)
                        <a href="{{ $project->prototype_url }}" target="_blank" rel="noopener noreferrer" @click.stop
                           class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11.5px] font-medium bg-emerald-500/10 border border-emerald-500/25 text-emerald-400 hover:bg-emerald-500/15 transition">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                            <span>Interaktif</span>
                        </a>
                    @endif
                    @if($project->github_url)
                        <a href="{{ $project->github_url }}" target="_blank" rel="noopener noreferrer" @click.stop
                           class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11.5px] font-mono bg-[var(--surface-subtle)] border border-[var(--border-color)] hover:border-[var(--border-strong)] text-[var(--text-muted)] hover:text-[var(--primary)] transition">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.53 1.032 1.53 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z"/></svg>
                            <span>GitHub</span>
                        </a>
                    @endif
                </div>
            @endif
        </div>
    @endif

    <!-- 5. Action Bar -->
    <x-project-actions :project="$project" :initial-vote="$initialVote" :is-bookmarked="$isBookmarked" :is-reposted="$isReposted" />

    <!-- LIGHTBOX: X-style sharp media viewer + info & live comments panel -->
    <div x-show="lightboxOpen" x-cloak
         x-transition.opacity.duration.150ms
         @keydown.escape.window="lightboxOpen = false"
         @keydown.left.window="if (lightboxOpen && gallery.length > 1 && !['INPUT','TEXTAREA'].includes(document.activeElement?.tagName)) lightboxIndex = (lightboxIndex - 1 + gallery.length) % gallery.length"
         @keydown.right.window="if (lightboxOpen && gallery.length > 1 && !['INPUT','TEXTAREA'].includes(document.activeElement?.tagName)) lightboxIndex = (lightboxIndex + 1) % gallery.length"
         @click.self="lightboxOpen = false"
         class="fixed inset-0 z-[100] bg-black/95 flex items-stretch justify-center"
         data-no-card-nav>
        <div class="flex flex-col lg:flex-row w-full h-full" @click.stop>
            <!-- Left: sharp media stage + bottom engagement bar -->
            <div class="relative flex-1 flex flex-col min-w-0 min-h-0 bg-black">
                <!-- Top controls -->
                <div class="flex items-center justify-between px-4 pt-3 pb-1 shrink-0 z-10">
                    <button type="button" @click="lightboxOpen = false" title="Tutup (Esc)"
                            class="w-9 h-9 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>

                    <div class="flex items-center gap-2">
                        <template x-if="gallery.length > 1">
                            <span class="text-white/70 text-xs font-mono px-2 py-1 rounded-md bg-white/5"
                                  x-text="(lightboxIndex + 1) + ' / ' + gallery.length"></span>
                        </template>
                        <button type="button" @click="lightboxSidebar = !lightboxSidebar"
                                :title="lightboxSidebar ? 'Sembunyikan panel info' : 'Tampilkan panel info'"
                                class="hidden lg:flex w-9 h-9 rounded-full bg-white/10 hover:bg-white/20 text-white items-center justify-center transition cursor-pointer">
                            <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': !lightboxSidebar }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Sharp image viewport -->
                <div class="relative flex-1 flex items-center justify-center px-4 min-h-0" @click.self="lightboxOpen = false">
                    <img :src="gallery[lightboxIndex]" alt="{{ $project->title }}"
                         class="max-w-full max-h-full object-contain rounded-none select-none">

                    <template x-if="gallery.length > 1">
                        <div>
                            <button type="button" @click="lightboxIndex = (lightboxIndex - 1 + gallery.length) % gallery.length"
                                    class="absolute left-3 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-black/60 hover:bg-black/80 border border-white/15 text-white flex items-center justify-center transition cursor-pointer">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                            </button>
                            <button type="button" @click="lightboxIndex = (lightboxIndex + 1) % gallery.length"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-black/60 hover:bg-black/80 border border-white/15 text-white flex items-center justify-center transition cursor-pointer">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </button>
                        </div>
                    </template>
                </div>

                <!-- Bottom engagement bar (X-style) -->
                <div class="shrink-0 flex items-center justify-center gap-8 sm:gap-12 py-3 px-4 border-t border-white/10 text-xs text-white/75">
                    <button type="button" @click="vote('up')"
                            class="inline-flex items-center gap-1.5 hover:text-emerald-400 transition cursor-pointer"
                            :class="{ 'text-emerald-400 font-semibold': userVote === 'up' }">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7"/></svg>
                        <span class="font-mono" x-text="score"></span>
                    </button>

                    <button type="button" @click="lightboxSidebar = true; $nextTick(() => $refs.lightboxCommentInput?.focus())"
                            class="inline-flex items-center gap-1.5 hover:text-white transition cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                        <span class="font-mono" x-text="commentsCount"></span>
                    </button>

                    <button type="button" @click="repost()"
                            class="inline-flex items-center gap-1.5 hover:text-emerald-400 transition cursor-pointer"
                            :class="{ 'text-emerald-400 font-semibold': isReposted }">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                        <span class="font-mono" x-text="repostsCount"></span>
                    </button>

                    <span class="inline-flex items-center gap-1.5 text-white/60">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        <span class="font-mono">{{ $project->views_count }}</span>
                    </span>

                    <button type="button" @click="toggleBookmark()"
                            class="inline-flex items-center gap-1.5 hover:text-emerald-400 transition cursor-pointer"
                            :class="{ 'text-emerald-400': isBookmarked }"
                            title="Simpan ke Koleksi">
                        <svg class="w-4 h-4" :fill="isBookmarked ? 'currentColor' : 'none'" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/></svg>
                    </button>
                </div>
            </div>

            <!-- Right: X-style project info + inline reply & comment thread -->
            <aside x-show="lightboxSidebar"
                   class="w-full lg:w-[370px] xl:w-[400px] max-h-[45vh] lg:max-h-full shrink-0 bg-[var(--surface)] border-t lg:border-t-0 lg:border-l border-[var(--border-color)] flex flex-col min-h-0">
                <div class="flex-1 overflow-y-auto divide-y divide-[var(--border-color)]">
                    <!-- Author & Project Details -->
                    <div class="p-4 sm:p-5 space-y-3">
                        <div class="flex items-center justify-between gap-2.5">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <x-user-avatar :user="$project->user" size="sm" />
                                <div class="min-w-0">
                                    @if($project->user)
                                        <a href="{{ route('profile.show', $project->user->username) }}" class="block font-bold text-sm text-[var(--primary)] hover:underline truncate">{{ $project->user->name }}</a>
                                        <p class="text-[11px] font-mono text-[var(--text-muted)] truncate">{{ '@' . $project->user->username }}</p>
                                    @else
                                        <p class="font-bold text-sm text-[var(--primary)] truncate">Anonim</p>
                                    @endif
                                </div>
                            </div>
                            <a href="{{ route('projects.show', $project->slug) }}"
                               class="shrink-0 inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-[var(--primary)] text-[var(--primary-text)] hover:opacity-90 transition">
                                <span>Detail</span>
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </a>
                        </div>

                        <div>
                            <h3 class="font-bold text-[15px] text-[var(--primary)] leading-snug">
                                <a href="{{ route('projects.show', $project->slug) }}" class="hover:text-emerald-300 transition">{{ $project->title }}</a>
                            </h3>
                            @if($project->tagline)
                                <p class="mt-1 text-[13px] text-[var(--text-soft)] leading-relaxed whitespace-pre-line">{{ $project->tagline }}</p>
                            @endif
                        </div>

                        @if($project->category || $hasTech || $hasLinks)
                            <div class="flex flex-wrap items-center gap-1.5 pt-0.5">
                                @if($project->category)
                                    <span class="text-[11px] font-medium px-2 py-0.5 rounded-md bg-[var(--surface-muted)] text-[var(--text-soft)] border border-[var(--border-color)]">{{ $project->category->name }}</span>
                                @endif
                                @if($hasTech)
                                    @foreach(array_slice($project->tech_stacks, 0, 4) as $tech)
                                        <span class="text-[11px] font-mono px-2 py-0.5 rounded-md bg-[var(--surface-subtle)] text-[var(--text-muted)] border border-[var(--border-color)]">{{ $tech }}</span>
                                    @endforeach
                                @endif
                                @if($project->demo_url)
                                    <a href="{{ $project->demo_url }}" target="_blank" rel="noopener noreferrer"
                                       class="text-[11px] font-medium px-2 py-0.5 rounded-md bg-emerald-500/10 text-emerald-400 border border-emerald-500/25 hover:bg-emerald-500/20 transition">Demo</a>
                                @endif
                                @if($project->github_url)
                                    <a href="{{ $project->github_url }}" target="_blank" rel="noopener noreferrer"
                                       class="text-[11px] font-mono px-2 py-0.5 rounded-md bg-[var(--surface-subtle)] text-[var(--text-muted)] border border-[var(--border-color)] hover:text-[var(--primary)] transition">GitHub</a>
                                @endif
                            </div>
                        @endif

                        <p class="text-[11px] text-[var(--text-faint)] font-mono">
                            {{ $project->created_at->translatedFormat('H:i · d M Y') }} · <span class="text-[var(--text-muted)]">{{ $project->views_count }} tayangan</span>
                        </p>
                    </div>

                    <!-- Inline Reply Input -->
                    <form @submit.prevent="submitComment()" class="p-4 flex items-start gap-2.5">
                        @auth
                            <x-user-avatar :user="auth()->user()" size="sm" />
                        @endauth
                        <div class="flex-1 min-w-0 space-y-2">
                            <textarea x-ref="lightboxCommentInput"
                                      x-model="newComment"
                                      rows="2"
                                      maxlength="1000"
                                      @focus="@guest if (window.openAuthModal) { $el.blur(); window.openAuthModal('login'); } @endguest"
                                      placeholder="Tulis komentar atau masukan..."
                                      class="w-full bg-transparent border border-[var(--border-color)] focus:border-[var(--border-strong)] rounded-lg px-3 py-2 text-xs text-[var(--primary)] placeholder-[var(--text-faint)] resize-none focus:outline-none"></textarea>
                            <div class="flex items-center justify-between">
                                <span class="text-[10px] font-mono text-[var(--text-faint)]" x-text="newComment.length + '/1000'"></span>
                                <button type="submit"
                                        :disabled="isSubmittingComment || !newComment.trim()"
                                        class="px-3.5 py-1.5 rounded-full text-xs font-bold bg-[var(--primary)] text-[var(--primary-text)] disabled:opacity-40 hover:opacity-90 transition cursor-pointer">
                                    <span x-text="isSubmittingComment ? 'Mengirim...' : 'Balas'"></span>
                                </button>
                            </div>
                        </div>
                    </form>

                    <!-- Comments Thread -->
                    <div class="p-4 space-y-3.5">
                        <div class="flex items-center justify-between">
                            <span class="text-[11px] font-mono uppercase tracking-wider text-[var(--text-muted)]">Komentar (<span x-text="commentsCount"></span>)</span>
                            <a href="{{ route('projects.show', $project->slug) }}#comments" class="text-[11px] text-emerald-400 hover:underline">Buka semua</a>
                        </div>

                        <template x-if="commentsLoading">
                            <div class="space-y-3 py-2">
                                <div class="h-10 rounded-lg bg-[var(--surface-muted)] animate-pulse"></div>
                                <div class="h-10 rounded-lg bg-[var(--surface-muted)] animate-pulse"></div>
                            </div>
                        </template>

                        <template x-if="!commentsLoading && comments.length === 0">
                            <p class="text-xs text-[var(--text-muted)] py-3 text-center">Belum ada komentar. Jadilah yang pertama memberi masukan.</p>
                        </template>

                        <template x-for="c in comments" :key="c.id">
                            <div class="flex items-start gap-2.5 text-xs">
                                <template x-if="c.avatar">
                                    <img :src="c.avatar" :alt="c.author" class="w-7 h-7 rounded-full object-cover shrink-0 border border-[var(--border-color)]">
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
    </div>
</article>
