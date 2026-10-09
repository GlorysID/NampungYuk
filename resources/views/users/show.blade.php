@extends('layouts.app')

@section('title', $user->name . ' (@' . $user->username . ') — NampungYuk')
@section('meta_description', $user->bio ?: 'Lihat karya project codingan dan kontribusi ' . $user->name . ' di NampungYuk.')

@section('content')
@php
    $initialTab = request('tab') === 'likes' && $isOwner ? 'likes'
                : (request('tab') === 'comments' ? 'comments' : 'projects');
@endphp

<div class="space-y-5" x-data="{ activeTab: '{{ $initialTab }}', editOpen: {{ $errors->any() && $isOwner ? 'true' : 'false' }} }">

    <!-- ===================== PROFILE HERO ===================== -->
    <div class="hl-panel overflow-hidden">

        <!-- Cover banner (uploaded image, or Hyperliquid gradient mesh fallback) -->
        <div class="relative h-28 sm:h-36 bg-[#000000] dark:bg-[#000000]">
            @if($user->banner)
                <img src="{{ $user->banner }}" alt="Banner {{ $user->name }}" class="absolute inset-0 w-full h-full object-cover">
                <div class="absolute inset-0 bg-gradient-to-t from-[#000000]/70 to-transparent"></div>
            @else
                <div class="absolute inset-0 opacity-90"
                     style="background:
                        radial-gradient(120% 140% at 0% 0%, rgba(80,210,193,0.35) 0%, transparent 55%),
                        radial-gradient(120% 140% at 100% 0%, rgba(14,156,139,0.28) 0%, transparent 55%),
                        linear-gradient(180deg, rgba(20,24,33,0) 0%, rgba(11,14,17,0.6) 100%);"></div>
                <!-- faint grid overlay for terminal feel -->
                <div class="absolute inset-0 opacity-[0.07]"
                     style="background-image: linear-gradient(rgba(255,255,255,0.6) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.6) 1px, transparent 1px); background-size: 26px 26px;"></div>
            @endif
        </div>

        <div class="px-5 sm:px-6 pb-5 -mt-10 sm:-mt-12">
            <div class="flex flex-col sm:flex-row sm:items-end gap-4">
                <!-- Avatar -->
                <div class="relative shrink-0">
                    <x-user-avatar :user="$user" size="xl" class="ring-4 ring-white dark:ring-[#0a0a0a]!" />
                    @if($isOwner)
                        <span class="absolute -bottom-1 -right-1 w-6 h-6 rounded-full bg-[#0070f3] dark:bg-[#3291ff] text-[#ffffff] flex items-center justify-center ring-2 ring-white dark:ring-[#0a0a0a] z-10">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        </span>
                    @endif
                </div>

                <!-- Name + bio -->
                <div class="flex-1 min-w-0 pb-1">
                    <div class="flex items-center gap-2 min-w-0">
                        <h1 class="text-xl sm:text-2xl font-bold text-[#18181b] dark:text-[#fafafa] tracking-tight truncate min-w-0">
                            {{ $user->name }}
                        </h1>
                        @if($user->is_admin ?? false)
                            <x-badge variant="teal" size="xs" class="shrink-0">Admin</x-badge>
                        @endif
                    </div>
                    <p class="text-xs sm:text-sm text-[#63636b] dark:text-[#a0a0a0] font-mono truncate">&#64;{{ $user->username }}</p>
                </div>

                <!-- Actions -->
                <div class="flex items-center gap-2 shrink-0 pb-1">
                    @if($isOwner)
                        <button type="button" @click="editOpen = true" class="btn-secondary text-xs py-2 px-3">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            <span>Edit Profil</span>
                        </button>
                    @else
                        <x-follow-button :user="$user" />
                    @endif
                </div>
            </div>

            @if($user->bio)
                <p class="text-xs sm:text-sm text-[#18181b] dark:text-[#fafafa] leading-relaxed mt-4 max-w-2xl">{{ $user->bio }}</p>
            @endif

            <!-- ===== PROFILE LINKS (link-in-bio list) ===== -->
            @if($user->links->count() > 0)
                <div class="mt-4 flex flex-wrap gap-2 max-w-2xl">
                    @foreach($user->links as $link)
                        <a href="{{ $link->url }}" target="_blank" rel="noopener noreferrer"
                           class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full border border-[#e4e4e7] dark:border-[#1f1f1f] bg-white dark:bg-[#0a0a0a] hover:border-[#0070f3] dark:hover:border-[#3291ff] hover:bg-[#f7f7f8] dark:hover:bg-[#111111] transition group"
                           title="{{ $link->url }}">
                            <svg class="w-3.5 h-3.5 text-[#0070f3] dark:text-[#3291ff] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                            <span class="text-xs font-semibold text-[#18181b] dark:text-[#fafafa] group-hover:text-[#0070f3] dark:group-hover:text-[#3291ff] transition">{{ $link->label }}</span>
                            <span class="text-[11px] text-[#63636b] dark:text-[#a0a0a0] hidden sm:inline">{{ $link->domain() }}</span>
                        </a>
                    @endforeach
                </div>
            @endif

            <!-- ===== STAT TERMINAL BAR (mono, dense, hoverable) ===== -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-px mt-5 rounded-lg overflow-hidden border border-[#e4e4e7] dark:border-[#1f1f1f] bg-[#eaeaea] dark:bg-[#1f1f1f]">
                <div class="bg-white dark:bg-[#0a0a0a] px-4 py-3">
                    <p class="hl-label">Reputasi</p>
                    <p class="hl-stat text-lg font-bold text-[#0070f3] dark:text-[#3291ff] mt-0.5 flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7"/></svg>
                        {{ number_format($user->reputation_points) }}
                    </p>
                </div>
                <div class="bg-white dark:bg-[#0a0a0a] px-4 py-3">
                    <p class="hl-label">Karya</p>
                    <p class="hl-stat text-lg font-bold text-[#18181b] dark:text-[#fafafa] mt-0.5">{{ $totalProjects }}</p>
                </div>
                <div class="bg-white dark:bg-[#0a0a0a] px-4 py-3">
                    <p class="hl-label">Pengikut</p>
                    <p class="hl-stat text-lg font-bold text-[#18181b] dark:text-[#fafafa] mt-0.5">{{ number_format($followersCount) }}</p>
                </div>
                <div class="bg-white dark:bg-[#0a0a0a] px-4 py-3">
                    <p class="hl-label">Mengikuti</p>
                    <p class="hl-stat text-lg font-bold text-[#18181b] dark:text-[#fafafa] mt-0.5">{{ number_format($followingCount) }}</p>
                </div>
            </div>

            <p class="text-[11px] font-mono text-[#63636b] dark:text-[#a0a0a0] mt-3">
                Bergabung {{ $user->created_at->translatedFormat('F Y') }} · {{ number_format($totalUpvotesReceived) }} total upvote diterima
            </p>
        </div>
    </div>

    <!-- ===================== PINNED PROJECTS ===================== -->
    @if($pinnedProjects->count() > 0)
        <div class="space-y-3">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-[#0070f3] dark:text-[#3291ff]" fill="currentColor" viewBox="0 0 24 24"><path d="M16 3a1 1 0 00-1 1v1.586l-4.293 4.293a1 1 0 00-.29.546l-.585 3.51-2.125 2.124a1 1 0 00.046 1.418l.001.001-1.045 1.045a1 1 0 001.414 1.414L9.16 20.9l.001.001a1 1 0 001.418.046l2.124-2.125 3.51-.585a1 1 0 00.546-.29L21 13.657H22a1 1 0 000-2h-1V7a1 1 0 00-1-1h-4zM7 17l-3.293 3.293a1 1 0 01-1.414-1.414L5.586 15.586 7 17z"/></svg>
                <h2 class="hl-label !text-[#18181b] dark:!text-[#fafafa]">Disematkan</h2>
                <span class="text-[10px] font-mono text-[#63636b] dark:text-[#a0a0a0]">{{ $pinnedProjects->count() }}/3</span>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-3">
                @foreach($pinnedProjects as $project)
                    <x-pinned-card :project="$project" :pinned="true" :is-owner="$isOwner" />
                @endforeach
            </div>
        </div>
    @endif

    <!-- ===================== TABS ===================== -->
    <div class="hl-panel p-1.5 flex items-center gap-1.5">
        <button @click="activeTab = 'projects'"
                :class="activeTab === 'projects' ? 'bg-[#0070f3] text-[#ffffff] font-semibold' : 'text-[#63636b] dark:text-[#a0a0a0] hover:bg-[#f5f5f5] dark:hover:bg-[#111111]'"
                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-md text-xs font-medium transition">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
            <span>Karya</span>
            <span class="hl-stat text-[10px] px-1.5 py-0.5 rounded" :class="activeTab === 'projects' ? 'bg-black/15' : 'bg-[#eeeeef] dark:bg-[#171717]'">{{ $totalProjects }}</span>
        </button>

        <button @click="activeTab = 'comments'"
                :class="activeTab === 'comments' ? 'bg-[#0070f3] text-[#ffffff] font-semibold' : 'text-[#63636b] dark:text-[#a0a0a0] hover:bg-[#f5f5f5] dark:hover:bg-[#111111]'"
                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-md text-xs font-medium transition">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
            <span>Diskusi</span>
            <span class="hl-stat text-[10px] px-1.5 py-0.5 rounded" :class="activeTab === 'comments' ? 'bg-black/15' : 'bg-[#eeeeef] dark:bg-[#171717]'">{{ $comments->count() }}</span>
        </button>

        <button @click="activeTab = 'reposts'"
                :class="activeTab === 'reposts' ? 'bg-[#0070f3] text-[#ffffff] font-semibold' : 'text-[#63636b] dark:text-[#a0a0a0] hover:bg-[#f5f5f5] dark:hover:bg-[#111111]'"
                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-md text-xs font-medium transition">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
            <span>Repost</span>
            <span class="hl-stat text-[10px] px-1.5 py-0.5 rounded" :class="activeTab === 'reposts' ? 'bg-black/15' : 'bg-[#eeeeef] dark:bg-[#171717]'">{{ $totalReposts }}</span>
        </button>

        @if($isOwner)
            <button @click="activeTab = 'likes'"
                    :class="activeTab === 'likes' ? 'bg-[#0070f3] text-[#ffffff] font-semibold' : 'text-[#63636b] dark:text-[#a0a0a0] hover:bg-[#f5f5f5] dark:hover:bg-[#111111]'"
                    class="inline-flex items-center gap-2 px-3.5 py-2 rounded-md text-xs font-medium transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                <span>Disukai</span>
                <span class="hl-stat text-[10px] px-1.5 py-0.5 rounded" :class="activeTab === 'likes' ? 'bg-black/15' : 'bg-[#eeeeef] dark:bg-[#171717]'">{{ $totalLikes }}</span>
            </button>
        @endif
    </div>

    <!-- ===================== TAB: PROJECTS ===================== -->
    <div x-show="activeTab === 'projects'" class="space-y-4">
        @forelse($projects as $project)
            @php
                $isBookmarked = in_array($project->id, $userBookmarkedIds ?? []);
                $initialVote = $userVotes[$project->id] ?? null;
            @endphp
            <x-project-card :project="$project" :initial-vote="$initialVote" :is-bookmarked="$isBookmarked" :is-owner="$isOwner" :is-reposted="in_array($project->id, $userRepostedIds ?? [])" />
        @empty
            <x-empty-state title="Belum ada karya codingan" description="Developer ini belum mempublikasikan project ke NampungYuk." />
        @endforelse

        @if($projects->hasPages())
            <div class="pt-2">{{ $projects->links() }}</div>
        @endif
    </div>

    <!-- ===================== TAB: REPOSTS ===================== -->
    <div x-show="activeTab === 'reposts'" x-cloak class="space-y-4">
        <p class="text-[11px] text-[#63636b] dark:text-[#a0a0a0] px-1">
            Project yang dibagikan ulang oleh {{ $isOwner ? 'kamu' : $user->name }}.
        </p>
        @forelse($repostedProjects as $project)
            @php
                $isBookmarked = in_array($project->id, $userBookmarkedIds ?? []);
                $initialVote = $userVotes[$project->id] ?? null;
            @endphp
            <x-project-card :project="$project" :initial-vote="$initialVote" :is-bookmarked="$isBookmarked" :is-reposted="true" uid="repost" />
        @empty
            <x-empty-state title="Belum ada repost" description="Project yang dibagikan ulang akan muncul di sini." />
        @endforelse

        @if($repostedProjects->hasPages())
            <div class="pt-2">{{ $repostedProjects->links() }}</div>
        @endif
    </div>

    <!-- ===================== TAB: LIKES (owner only) ===================== -->
    @if($isOwner)
        <div x-show="activeTab === 'likes'" x-cloak class="space-y-4">
            @forelse($likedProjects as $project)
                <x-project-card :project="$project" :initial-vote="'up'" uid="like" />
            @empty
                <x-empty-state title="Belum ada yang disukai" description="Project yang kamu upvote akan muncul di sini." />
            @endforelse

            @if(method_exists($likedProjects, 'hasPages') && $likedProjects->hasPages())
                <div class="pt-2">{{ $likedProjects->links() }}</div>
            @endif
        </div>
    @endif

    <!-- ===================== TAB: COMMENTS ===================== -->
    <div x-show="activeTab === 'comments'" x-cloak class="space-y-3">
        @forelse($comments as $comment)
            <div class="hl-panel p-4 space-y-2">
                <div class="flex items-center justify-between text-xs">
                    @if($comment->project)
                        <span class="text-[#63636b] dark:text-[#a0a0a0]">
                            Memberikan masukan di
                            <a href="{{ route('projects.show', $comment->project->slug) }}#komentar" class="font-bold text-[#0070f3] dark:text-[#3291ff] hover:underline">
                                {{ $comment->project->title }}
                            </a>
                        </span>
                    @endif
                    <span class="text-[#63636b] dark:text-[#a0a0a0] text-[11px] font-mono">{{ $comment->created_at->diffForHumans() }}</span>
                </div>
                <p class="text-xs sm:text-sm text-[#18181b] dark:text-[#fafafa] leading-relaxed">"{{ $comment->content }}"</p>
            </div>
        @empty
            <x-empty-state title="Belum ada ulasan" description="Developer ini belum memberikan komentar pada project lain." />
        @endforelse
    </div>

    <!-- ===================== EDIT PROFILE MODAL ===================== -->
    @if($isOwner)
        <div x-show="editOpen" x-cloak
             class="fixed inset-0 z-50 flex items-start sm:items-center justify-center p-4 overflow-y-auto bg-black/55 backdrop-blur-xs"
             @click.self="editOpen = false"
             @keydown.escape.window="editOpen = false"
             role="dialog" aria-modal="true"
             x-data="{
                preview: null, bannerPreview: null,
                crop: { open: false, tag: 'avatar', file: null, url: null, scale: 1, x: 0, y: 0, drag: false, sx: 0, sy: 0, ox: 0, oy: 0, natW: 0, natH: 0, fw: 0, fh: 0 },
                startCrop(file, tag) {
                    if (!file) return;
                    this.crop = Object.assign(this.crop, { open: true, tag, file, url: URL.createObjectURL(file), scale: 1, x: 0, y: 0, natW: 0, natH: 0, coverScale: 1 });
                    this.$nextTick(() => {
                        const f = this.$refs.cropFrame; this.crop.fw = f.clientWidth; this.crop.fh = f.clientHeight;
                        const img = this.$refs.cropImg;
                        const onload = () => {
                            this.crop.natW = img.naturalWidth; this.crop.natH = img.naturalHeight;
                            this.crop.coverScale = Math.max(this.crop.fw / this.crop.natW, this.crop.fh / this.crop.natH);
                            this.crop.scale = this.crop.coverScale;
                        };
                        if (img.complete) onload(); else img.onload = onload;
                    });
                },
                cropDown(e) { const p = e.touches ? e.touches[0] : e; this.crop.drag = true; this.crop.sx = p.clientX; this.crop.sy = p.clientY; this.crop.ox = this.crop.x; this.crop.oy = this.crop.y; },
                cropMove(e) {
                    if (!this.crop.drag) return;
                    const p = e.touches ? e.touches[0] : e;
                    this.crop.x = this.crop.ox + (p.clientX - this.crop.sx);
                    this.crop.y = this.crop.oy + (p.clientY - this.crop.sy);
                    this.clampCrop();
                },
                clampCrop() {
                    const dw = this.crop.natW * this.crop.scale, dh = this.crop.natH * this.crop.scale;
                    const mx = Math.max(0, (dw - this.crop.fw) / 2), my = Math.max(0, (dh - this.crop.fh) / 2);
                    this.crop.x = Math.max(-mx, Math.min(mx, this.crop.x));
                    this.crop.y = Math.max(-my, Math.min(my, this.crop.y));
                },
                cropUp() { this.crop.drag = false; },
                cropZoom(d) { this.crop.scale = Math.max(0.1, this.crop.scale + d); this.clampCrop(); },
                async applyCrop() {
                    const c = this.crop;
                    if (!c.natW) return;
                    const ratio = c.tag === 'banner' ? 3 : 1; // banner 3:1, avatar 1:1
                    const W = 900, H = Math.round(900 / ratio);
                    const cv = document.createElement('canvas'); cv.width = W; cv.height = H;
                    const ctx = cv.getContext('2d'); ctx.fillStyle = '#000'; ctx.fillRect(0, 0, W, H);
                    const sr = W / c.fw;
                    const dw = c.natW * c.scale * sr, dh = c.natH * c.scale * sr;
                    const dx = (W - dw) / 2 + c.x * sr, dy = (H - dh) / 2 + c.y * sr;
                    ctx.drawImage(this.$refs.cropImg, dx, dy, dw, dh);
                    const blob = await new Promise(r => cv.toBlob(r, 'image/jpeg', 0.92));
                    const file = new File([blob], c.tag + '.jpg', { type: 'image/jpeg' });
                    const input = c.tag === 'avatar' ? this.$refs.avatarInput : this.$refs.bannerInput;
                    const dt = new DataTransfer(); dt.items.add(file); input.files = dt.files;
                    const previewUrl = URL.createObjectURL(blob);
                    if (c.tag === 'avatar') this.preview = previewUrl; else this.bannerPreview = previewUrl;
                    this.crop.open = false;
                },
                cancelCrop() { this.crop.open = false; }
             }">
            <div class="hl-panel w-full max-w-lg my-6 bg-white dark:bg-[#0a0a0a] overflow-hidden"
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100">

                <!-- Modal header -->
                <div class="flex items-center justify-between px-5 py-3.5 border-b border-[#e4e4e7] dark:border-[#1f1f1f]">
                    <div class="flex items-center gap-2">
                        <h3 class="font-bold text-sm text-[#18181b] dark:text-[#fafafa]">Edit Profil</h3>
                        <span class="hl-label">Profil</span>
                    </div>
                    <button type="button" @click="editOpen = false" class="text-[#63636b] hover:text-[#18181b] dark:hover:text-white transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="px-5 py-4 space-y-4 max-h-[75vh] overflow-y-auto">
                    @csrf
                    @method('PUT')

                    <!-- Banner upload -->
                    <div class="space-y-2">
                        <p class="hl-label">Banner Profil</p>
                        <label class="relative block h-24 rounded-lg overflow-hidden border border-[#e4e4e7] dark:border-[#1f1f1f] cursor-pointer group">
                            <img x-show="bannerPreview" :src="bannerPreview" class="absolute inset-0 w-full h-full object-cover" alt="">
                            <div x-show="!bannerPreview" class="absolute inset-0 bg-[#eeeeef] dark:bg-[#171717] flex items-center justify-center"
                                 style="background-image: radial-gradient(120% 140% at 0% 0%, rgba(80,210,193,0.28) 0%, transparent 55%);">
                                <span class="text-[11px] font-mono text-[#63636b] dark:text-[#a0a0a0]">Klik untuk unggah banner</span>
                            </div>
                            <div class="absolute inset-0 bg-black/0 group-hover:bg-black/30 transition flex items-center justify-center">
                                <span class="opacity-0 group-hover:opacity-100 transition text-white text-[11px] font-semibold flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    Ganti banner
                                </span>
                            </div>
                            <input type="file" accept="image/*" class="hidden"
                                   @change="startCrop($event.target.files[0], 'banner')">
                            <input type="file" x-ref="bannerInput" name="banner" class="hidden">
                        </label>
                        <p class="text-[10px] text-[#63636b] dark:text-[#a0a0a0]">JPG, PNG, WEBP · maks 4MB · kamu bisa pilih bagian foto setelah upload.</p>
                        @error('banner') <p class="text-rose-500 text-[11px]">{{ $message }}</p> @enderror
                    </div>

                    <!-- Avatar + name/username -->
                    <div class="flex items-center gap-4">
                        <img x-show="preview || {{ $user->avatar ? 'true' : 'false' }}"
                             :src="preview || '{{ $user->avatar }}'"
                             alt="{{ $user->name }}"
                             class="w-16 h-16 rounded-full object-cover ring-1 ring-[#eaeaea] dark:ring-[#1f1f1f] shrink-0">
                        <span x-show="!(preview || {{ $user->avatar ? 'true' : 'false' }})"
                              class="w-16 h-16 rounded-full flex items-center justify-center text-xl font-bold text-white ring-1 ring-[#eaeaea] dark:ring-[#1f1f1f] shrink-0"
                              style="background-color: #4F46E5;">{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}</span>
                        <div class="flex-1 space-y-1.5">
                            <label for="avatar" class="btn-secondary text-xs py-1.5 px-3 cursor-pointer inline-flex">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                <span>Ganti avatar</span>
                                <input type="file" id="avatar" accept="image/*" class="hidden"
                                       @change="startCrop($event.target.files[0], 'avatar')">
                                <input type="file" x-ref="avatarInput" name="avatar" class="hidden">
                            </label>
                            <p class="text-[10px] text-[#63636b] dark:text-[#a0a0a0]">Maks 2MB · kamu bisa pilih bagian foto setelah upload.</p>
                            @error('avatar') <p class="text-rose-500 text-[11px]">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="space-y-1">
                            <label for="name" class="text-xs font-semibold text-[#18181b] dark:text-[#fafafa]">Nama Lengkap</label>
                            <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" class="ny-input text-sm">
                            @error('name') <p class="text-rose-500 text-[11px]">{{ $message }}</p> @enderror
                        </div>
                        <div class="space-y-1">
                            <label for="username" class="text-xs font-semibold text-[#18181b] dark:text-[#fafafa]">Username</label>
                            <div class="relative">
                                <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-[#63636b] dark:text-[#a0a0a0] font-mono text-sm pointer-events-none select-none">@</span>
                                <input type="text" id="username" name="username" value="{{ old('username', $user->username) }}" placeholder="username" class="ny-input text-sm font-mono !pl-9">
                            </div>
                            @error('username') <p class="text-rose-500 text-[11px]">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="space-y-1">
                        <div class="flex items-center justify-between">
                            <label for="bio" class="text-xs font-semibold text-[#18181b] dark:text-[#fafafa]">Bio</label>
                            <span class="text-[10px] font-mono text-[#63636b] dark:text-[#a0a0a0]">maks 280</span>
                        </div>
                        <textarea id="bio" name="bio" rows="3" maxlength="280" class="ny-input text-sm resize-none" placeholder="Ceritakan spesialisasimu...">{{ old('bio', $user->bio) }}</textarea>
                        @error('bio') <p class="text-rose-500 text-[11px]">{{ $message }}</p> @enderror
                    </div>

                    <!-- Dynamic profile links (link-in-bio), max 6 -->
                    <div class="space-y-2 pt-1 border-t border-[#e4e4e7] dark:border-[#1f1f1f]"
                         x-data="{ links: {{ Illuminate\Support\Js::from($user->links->map(fn($l) => ['label' => $l->label, 'url' => $l->url])->values()) }} }">
                        <div class="flex items-center justify-between">
                            <label class="text-xs font-semibold text-[#18181b] dark:text-[#fafafa]">Link Profil</label>
                            <span class="text-[10px] text-[#63636b] dark:text-[#a0a0a0]" x-text="links.length + '/6'"></span>
                        </div>

                        <template x-for="(link, i) in links" :key="i">
                            <div class="flex items-center gap-2">
                                <input type="text" :name="'links[' + i + '][label]'" x-model="link.label"
                                       placeholder="Keterangan (mis. Portfolio)" maxlength="40"
                                       class="ny-input text-xs !w-32 shrink-0">
                                <input type="url" :name="'links[' + i + '][url]'" x-model="link.url"
                                       placeholder="https://..." class="ny-input text-xs flex-1 min-w-0">
                                <button type="button" @click="links.splice(i, 1)"
                                        class="shrink-0 w-8 h-8 rounded-md flex items-center justify-center text-[#63636b] dark:text-[#a0a0a0] hover:text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/30 transition"
                                        title="Hapus link">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                        </template>

                        <button type="button" @click="if (links.length < 6) links.push({ label: '', url: '' })"
                                :disabled="links.length >= 6"
                                class="btn-secondary text-xs py-1.5 px-3 disabled:opacity-40">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            <span>Tambah Link</span>
                        </button>
                        @error('links') <p class="text-rose-500 text-[11px]">{{ $message }}</p> @enderror
                        @error('links.*.url') <p class="text-rose-500 text-[11px]">Pastikan semua URL valid.</p> @enderror
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-1 border-t border-[#e4e4e7] dark:border-[#1f1f1f] mt-2">
                        <button type="button" @click="editOpen = false" class="btn-secondary text-xs py-2 px-4">Batal</button>
                        <button type="submit" class="btn-primary text-xs py-2 px-4">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Simpan</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- CROP OVERLAY (choose which part of the photo is visible) -->
            <div x-show="crop.open" x-cloak
                 class="fixed inset-0 z-[60] bg-black/85 backdrop-blur-sm flex flex-col items-center justify-center p-4"
                 @keydown.escape.window="cancelCrop()">
                <div class="w-full max-w-md space-y-4">
                    <p class="text-center text-xs text-white/70">Seret gambar untuk mengatur posisi · gunakan tombol zoom untuk memperbesar</p>

                    <!-- Crop frame -->
                    <div class="mx-auto rounded-xl overflow-hidden bg-black relative touch-none select-none cursor-move"
                         :style="crop.tag === 'banner' ? 'width:100%;aspect-ratio:3/1' : 'width:260px;height:260px'"
                         x-ref="cropFrame"
                         @mousedown="cropDown($event)" @mousemove="cropMove($event)" @mouseup="cropUp()" @mouseleave="cropUp()"
                         @touchstart.prevent="cropDown($event)" @touchmove.prevent="cropMove($event)" @touchend="cropUp()">
                        <img x-ref="cropImg" :src="crop.url" alt=""
                             class="absolute pointer-events-none select-none"
                             :style="`
                                width: ${(crop.natW * crop.scale) || 0}px;
                                height: ${(crop.natH * crop.scale) || 0}px;
                                left: ${crop.fw / 2 - (crop.natW * crop.scale) / 2 + crop.x}px;
                                top: ${crop.fh / 2 - (crop.natH * crop.scale) / 2 + crop.y}px;
                             `">
                        <!-- guide overlay -->
                        <div class="absolute inset-0 pointer-events-none" :class="crop.tag === 'avatar' ? 'rounded-full' : ''"
                             style="box-shadow: 0 0 0 9999px rgba(0,0,0,0.35);"></div>
                    </div>

                    <!-- Zoom controls -->
                    <div class="flex items-center justify-center gap-3">
                        <button type="button" @click="cropZoom(-0.1)" class="w-9 h-9 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                        </button>
                        <input type="range" min="0.1" max="4" step="0.01" :value="crop.scale" @input="crop.scale = parseFloat($event.target.value); clampCrop()" class="w-40 accent-[#0070f3]">
                        <button type="button" @click="cropZoom(0.1)" class="w-9 h-9 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        </button>
                    </div>

                    <div class="flex items-center justify-center gap-2 pt-1">
                        <button type="button" @click="cancelCrop()" class="btn-secondary text-xs py-2 px-4">Batal</button>
                        <button type="button" @click="applyCrop()" class="btn-primary text-xs py-2 px-4">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Terapkan</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

</div>
@endsection
