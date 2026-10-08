@props([
    'project',
    'initialVote' => null,
    'isBookmarked' => false,
    'isReposted' => false,
    'isOwner' => false,
    'uid' => 'p',
])

<article class="project-nav-card ny-card p-4 sm:p-5 space-y-4 group cursor-pointer"
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
             gallery: {{ Illuminate\Support\Js::from($project->galleryImages()) }},
             openLightbox(i) { this.lightboxIndex = i; this.lightboxOpen = true; },
             async repost() {
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
                     if (res.status === 401) { window.location.href = '{{ route('login') }}'; return; }
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
                         window.location.href = '{{ route('login') }}';
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
                         window.location.href = '{{ route('login') }}';
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

    <!-- 1. Creator & Category Metadata -->
    <x-project-meta :project="$project" />

    <!-- 2. Project Title & Tagline -->
    <div class="space-y-1">
        <div class="flex items-start justify-between gap-2">
            <div class="flex items-center gap-2 flex-wrap min-w-0">
                <h2 class="text-base sm:text-lg font-bold text-[#18181b] dark:text-[#fafafa] leading-snug transition">
                    <a href="{{ route('projects.show', $project->slug) }}" class="card-detail-link focus-visible:rounded">
                        {{ $project->title }}
                    </a>
                </h2>
                @if(! $project->thumbnail && $project->created_at->diffInHours(now()) < 24)
                    <span class="hl-new-badge">New</span>
                @endif
                @if($project->demo_url)
                    <a href="{{ $project->demo_url }}" target="_blank" rel="noopener noreferrer"
                       class="inline-flex items-center gap-1 text-[11px] font-medium text-[#0070f3] dark:text-[#3291ff] hover:underline">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        <span>Live Demo</span>
                    </a>
                @elseif($project->prototype_url)
                    <a href="{{ $project->prototype_url }}" target="_blank" rel="noopener noreferrer"
                       class="inline-flex items-center gap-1 text-[11px] font-medium text-purple-600 dark:text-purple-300 hover:underline">
                        <span class="w-1.5 h-1.5 rounded-full bg-purple-500"></span>
                        <span>Prototipe</span>
                    </a>
                @endif
            </div>

            @if($isOwner)
                <form method="POST" action="{{ route('projects.pin', $project) }}" class="shrink-0">
                    @csrf
                    <button type="submit" title="{{ $project->is_pinned ? 'Lepas sematan' : 'Sematkan ke profil' }}"
                            class="w-7 h-7 rounded-md flex items-center justify-center transition {{ $project->is_pinned ? 'text-[#0070f3] dark:text-[#3291ff] bg-[#3291ff]/10' : 'text-[#63636b] dark:text-[#a0a0a0] hover:text-[#0070f3] dark:hover:text-[#3291ff] hover:bg-[#f5f5f5] dark:hover:bg-[#111111]' }}">
                        <svg class="w-3.5 h-3.5" fill="{{ $project->is_pinned ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 3a1 1 0 00-1 1v1.586l-4.293 4.293a1 1 0 00-.29.546l-.585 3.51-2.125 2.124a1 1 0 00.046 1.418l.001.001-1.045 1.045a1 1 0 001.414 1.414L9.16 20.9l.001.001a1 1 0 001.418.046l2.124-2.125 3.51-.585a1 1 0 00.546-.29L21 13.657H22a1 1 0 000-2h-1V7a1 1 0 00-1-1h-4zM7 17l-3.293 3.293a1 1 0 01-1.414-1.414L5.586 15.586 7 17z"/></svg>
                    </button>
                </form>
            @endif
        </div>

        <p class="text-xs sm:text-sm text-[#63636b] dark:text-[#a0a0a0] leading-relaxed line-clamp-2">
            {{ $project->tagline }}
        </p>
    </div>

    <!-- 3. Gallery media (adapts to image count, X-style) -->
    @php $gallery = $project->galleryImages(); $galleryCount = count($gallery); @endphp
    @if($galleryCount > 0)
        <div class="relative block mx-auto max-w-full w-full overflow-hidden rounded-2xl border border-[#e4e4e7] dark:border-[#1f1f1f] bg-[#eeeeef] dark:bg-[#171717]">
            <button type="button" class="block w-full text-left" data-no-card-nav @click.stop="openLightbox(0)">
                @if($galleryCount === 1)
                    {{-- Single image --}}
                    <div class="aspect-video">
                        <img src="{{ $gallery[0] }}" alt="{{ $project->title }}" loading="lazy" decoding="async"
                             onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1555066931-4365d14bab8c?auto=format&fit=crop&w=1200&q=80';"
                             class="w-full h-full object-cover">
                    </div>
                @elseif($galleryCount === 2)
                    {{-- Two images: split --}}
                    <div class="grid grid-cols-2 gap-0.5 aspect-video">
                        <span class="block overflow-hidden" @click.stop="openLightbox(0)"><img src="{{ $gallery[0] }}" alt="" loading="lazy" decoding="async" class="w-full h-full object-cover"></span>
                        <span class="block overflow-hidden" @click.stop="openLightbox(1)"><img src="{{ $gallery[1] }}" alt="" loading="lazy" decoding="async" class="w-full h-full object-cover"></span>
                    </div>
                @elseif($galleryCount === 3)
                    {{-- Three images: one tall + two stacked --}}
                    <div class="grid grid-cols-2 gap-0.5 aspect-video">
                        <span class="block overflow-hidden row-span-2" @click.stop="openLightbox(0)"><img src="{{ $gallery[0] }}" alt="" loading="lazy" decoding="async" class="w-full h-full object-cover"></span>
                        <span class="block overflow-hidden" @click.stop="openLightbox(1)"><img src="{{ $gallery[1] }}" alt="" loading="lazy" decoding="async" class="w-full h-full object-cover"></span>
                        <span class="block overflow-hidden" @click.stop="openLightbox(2)"><img src="{{ $gallery[2] }}" alt="" loading="lazy" decoding="async" class="w-full h-full object-cover"></span>
                    </div>
                @else
                    {{-- Four or more: 2x2 grid, "+N" overlay on the last --}}
                    <div class="grid grid-cols-2 grid-rows-2 gap-0.5 aspect-video">
                        @foreach(array_slice($gallery, 0, 4) as $i => $img)
                            <span class="relative block overflow-hidden" @click.stop="openLightbox({{ $i }})">
                                <img src="{{ $img }}" alt="" loading="lazy" decoding="async" class="w-full h-full object-cover">
                                @if($i === 3 && $galleryCount > 4)
                                    <span class="absolute inset-0 bg-[#000000]/60 text-white flex items-center justify-center text-lg font-bold backdrop-blur-[1px]">
                                        +{{ $galleryCount - 4 }}
                                    </span>
                                @endif
                            </span>
                        @endforeach
                    </div>
                @endif
            </button>

            {{-- Image count chip (multi only) --}}
            @if($galleryCount > 1)
                <span class="absolute bottom-2 right-2 inline-flex items-center gap-1 px-1.5 py-0.5 rounded-md text-[10px] font-semibold bg-[#000000]/75 text-white backdrop-blur-sm">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    {{ $galleryCount }}
                </span>
            @endif

            @if($project->created_at->diffInHours(now()) < 24)
                <span class="hl-new-badge absolute top-2 left-2">New</span>
            @endif

            @if($project->isPrivate())
                <span class="absolute bottom-2 left-2 inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold uppercase bg-[#000000]/85 text-amber-300 backdrop-blur-sm">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    Private
                </span>
            @endif

            @if($project->isRepost())
                <a href="{{ $project->repostedFrom ? route('projects.show', $project->repostedFrom->slug) : '#' }}"
                   class="absolute top-2 right-2 inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-medium bg-[#000000]/85 text-[#3291ff] backdrop-blur-sm hover:bg-[#000000] transition"
                   title="Repost dari {{ $project->repostedFrom?->title }}">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                    Repost
                </a>
            @endif
        </div>
    @endif

    <!-- 4. Tech Stacks Chips -->
    @if(is_array($project->tech_stacks) && count($project->tech_stacks) > 0)
        <div class="flex flex-wrap gap-1.5">
            @foreach(array_slice($project->tech_stacks, 0, 5) as $tech)
                <x-tech-pill :name="$tech" />
            @endforeach
            @if(count($project->tech_stacks) > 5)
                <span class="text-[11px] text-[#63636b] dark:text-[#a0a0a0] self-center">
                    +{{ count($project->tech_stacks) - 5 }}
                </span>
            @endif
        </div>
    @endif

    <!-- 5. Action Bar (Voting, Comments, View Count, Bookmark, Share) -->
    <x-project-actions :project="$project" :initial-vote="$initialVote" :is-bookmarked="$isBookmarked" :is-reposted="$isReposted" />

    <!-- LIGHTBOX: large image + detail panel on the right (X-style) -->
    <div x-show="lightboxOpen" x-cloak
         x-transition.opacity.duration.150ms
         @keydown.escape.window="lightboxOpen = false"
         @click.self="lightboxOpen = false"
         class="fixed inset-0 z-[100] bg-black/90 backdrop-blur-sm flex items-stretch justify-center"
         data-no-card-nav>
        <!-- Close -->
        <button type="button" @click="lightboxOpen = false"
                class="absolute top-4 left-4 z-10 w-9 h-9 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>

        <div class="flex flex-col lg:flex-row w-full h-full" @click.stop>
            <!-- Left: large image -->
            <div class="relative flex-1 flex items-center justify-center p-4 min-h-0">
                <img :src="gallery[lightboxIndex]" alt="{{ $project->title }}"
                     class="max-w-full max-h-full object-contain rounded-lg" @click.self="lightboxOpen = false">

                <template x-if="gallery.length > 1">
                    <div>
                        <button type="button" @click="lightboxIndex = (lightboxIndex - 1 + gallery.length) % gallery.length"
                                class="absolute left-4 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        </button>
                        <button type="button" @click="lightboxIndex = (lightboxIndex + 1) % gallery.length"
                                class="absolute right-4 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </button>
                    </div>
                </template>

                <span class="absolute bottom-4 left-1/2 -translate-x-1/2 text-white/70 text-xs font-mono"
                      x-text="(lightboxIndex + 1) + ' / ' + gallery.length"></span>
            </div>

            <!-- Right: compact detail panel -->
            <aside class="lg:w-80 shrink-0 bg-white dark:bg-[#0a0a0a] border-t lg:border-t-0 lg:border-l border-[#e4e4e7] dark:border-[#1f1f1f] p-5 overflow-y-auto">
                <div class="flex items-center gap-2.5">
                    <x-user-avatar :user="$project->user" size="sm" />
                    <div class="min-w-0">
                        <p class="font-bold text-sm text-[#18181b] dark:text-[#fafafa] truncate">{{ $project->user?->name ?? 'Anonim' }}</p>
                        <p class="text-[11px] text-[#63636b] dark:text-[#a0a0a0] truncate">{{ $project->created_at->diffForHumans() }}</p>
                    </div>
                </div>

                <h3 class="mt-4 font-bold text-sm text-[#18181b] dark:text-[#fafafa] leading-snug">{{ $project->title }}</h3>
                <p class="mt-1 text-xs text-[#63636b] dark:text-[#a0a0a0] leading-relaxed line-clamp-4">{{ $project->tagline }}</p>

                @if($project->category)
                    <span class="inline-block mt-3 text-[11px] font-medium px-2 py-0.5 rounded-full bg-[#eeeeef] dark:bg-[#171717] text-[#63636b] dark:text-[#a0a0a0]">{{ $project->category->name }}</span>
                @endif

                <div class="flex items-center gap-4 mt-4 text-xs text-[#63636b] dark:text-[#a0a0a0]">
                    <span class="inline-flex items-center gap-1"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>{{ $project->score }}</span>
                    <span class="inline-flex items-center gap-1"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>{{ $project->comments_count }}</span>
                    <span class="inline-flex items-center gap-1"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>{{ $project->views_count }}</span>
                </div>

                <a href="{{ route('projects.show', $project->slug) }}"
                   class="btn-primary w-full mt-5 text-xs py-2.5">
                    <span>Lihat Project Lengkap</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </a>
            </aside>
        </div>
    </div>
</article>
