@props([
    'project',
    'initialVote' => null,
    'isBookmarked' => false,
    'isReposted' => false,
    'isOwner' => false,
    'uid' => 'p',
])

@php
    $gallery = $project->galleryImages();
    $galleryCount = count($gallery);
    $lightboxMeta = [
        'title' => $project->title,
        'tagline' => $project->tagline,
        'showUrl' => route('projects.show', $project->slug),
        'authorName' => $project->user?->name ?? 'Anonim',
        'authorUsername' => $project->user?->username,
        'authorAvatar' => $project->user?->avatar,
        'authorUrl' => $project->user?->username ? route('profile.show', $project->user->username) : null,
        'category' => $project->category?->name,
        'techStacks' => is_array($project->tech_stacks) ? array_slice($project->tech_stacks, 0, 4) : [],
        'demoUrl' => $project->demo_url,
        'githubUrl' => $project->github_url,
        'viewsFormatted' => number_format($project->views_count, 0, ',', '.'),
        'createdAtLabel' => $project->created_at?->translatedFormat('H:i Â· d M Y') ?? '',
    ];
@endphp

<article class="project-nav-card bg-[var(--surface)] border border-[var(--border-color)] hover:border-[var(--border-strong)] rounded-2xl p-5 transition relative mb-4 last:mb-0 space-y-3.5 group cursor-pointer"
         @click="if (! $event.target.closest('a, button, input, textarea, select, [data-no-card-nav]')) { $el.querySelector('.card-detail-link')?.click(); }"
         x-data="projectCard({
             score: {{ $project->score }},
             userVote: @js($initialVote ?? ''),
             isBookmarked: {{ $isBookmarked ? 'true' : 'false' }},
             isReposted: {{ $isReposted ? 'true' : 'false' }},
             repostsCount: {{ $project->reposts_count }},
             commentsCount: {{ $project->comments_count }},
             gallery: @js($gallery),
             meta: @js($lightboxMeta),
             isGuest: {{ auth()->guest() ? 'true' : 'false' }},
             loginUrl: @js(route('login')),
             showUrl: @js(route('projects.show', $project->slug)),
             commentsUrl: @js(route('projects.comments.index', $project)),
             commentUrl: @js(route('projects.comment', $project)),
             repostUrl: @js(route('projects.repost', $project)),
             voteUrl: @js(route('projects.vote', $project)),
             bookmarkUrl: @js(route('projects.bookmark', $project)),
         })">

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
            <a href="{{ route('projects.show', $project->slug) }}" class="card-detail-link focus-visible:rounded" @guest data-auth-reason="detail" @click.prevent="window.openAuthModal('login')" @endguest>
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
                        {{ $isOwner ? 'â€” Hanya Tampil Untuk Kamu & Admin' : '(hanya terlihat oleh pemilik & admin)' }}
                    </p>
                    <p class="text-xs">{{ $project->warning_notes ?: 'Proyek ini sedang dalam peninjauan moderasi dan disembunyikan dari publik.' }}</p>
                </div>
            </div>
        @endif
    </div>

    <!-- 3. Full-Width Gallery Media Showcase -->
    @if($galleryCount > 0)
        <div class="group/media relative block w-full overflow-hidden rounded-lg border border-[var(--border-strong)]/80 bg-[var(--bg-app)] after:pointer-events-none after:absolute after:inset-0 after:rounded-lg after:ring-1 after:ring-inset after:ring-white/[0.08]">
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
                           class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11.5px] font-medium bg-emerald-500/10 border border-emerald-500/25 text-emerald-400 hover:bg-emerald-500/15 transition">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                            <span>Live Demo</span>
                            <svg class="w-3 h-3 opacity-75" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 17L17 7M7 7h10v10"/></svg>
                        </a>
                    @endif
                    @if($project->prototype_url)
                        <a href="{{ $project->prototype_url }}" target="_blank" rel="noopener noreferrer" @click.stop
                           class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11.5px] font-medium bg-[var(--surface-subtle)] border border-[var(--border-color)] hover:border-[var(--border-strong)] text-[var(--text-muted)] hover:text-[var(--primary)] transition">
                            <svg class="w-3 h-3 opacity-75" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/></svg>
                            <span>Prototipe</span>
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

</article>
