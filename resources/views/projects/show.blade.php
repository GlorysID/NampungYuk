@extends('layouts.app')

@section('title', $project->title . ' — NampungYuk')
@section('meta_description', $project->tagline)

@section('content')
<div class="space-y-6"
     x-data="{
         score: {{ $project->score }},
         userVote: '{{ $userVote }}',
         isBookmarked: {{ $isBookmarked ? 'true' : 'false' }},
         isVoting: false,
         isBookmarking: false,
         isReposted: {{ $userReposted ? 'true' : 'false' }},
         repostsCount: {{ $project->reposts_count }},
         isReposting: false,
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
                     if (window.notify) notify(type === 'up' ? 'Upvoted! Terima kasih atas apresiasinya.' : 'Feedback tercatat.');
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
                     if (window.notify) notify(this.isBookmarked ? 'Project disimpan ke Koleksi!' : 'Dihapus dari Koleksi.');
                 }
             } catch(e) {
                 if (window.notify) notify('Gagal menyimpan bookmark');
             } finally {
                 this.isBookmarking = false;
             }
         },
         copyLink() {
             navigator.clipboard.writeText(window.location.href);
             if (window.notify) notify('Tautan project berhasil disalin ke clipboard!');
         }
     }">

    <!-- Top Breadcrumb & Navigation -->
    <div class="flex items-center justify-between text-xs">
        <a href="{{ route('projects.index') }}" 
           class="inline-flex items-center gap-1.5 text-[#63636b] dark:text-[#a0a0a0] hover:text-[#0070f3] dark:hover:text-[#3291ff] transition font-medium">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span>Kembali ke Feed</span>
        </a>

        @if($project->status)
            @php
                $statusVariants = [
                    'idea' => 'neutral',
                    'prototype' => 'purple',
                    'beta' => 'teal',
                    'production' => 'success',
                    'archived' => 'neutral',
                ];
                $variant = $statusVariants[$project->status] ?? 'neutral';
            @endphp
            <div class="flex items-center gap-1.5 text-xs">
                <span class="text-[#63636b] dark:text-[#a0a0a0]">Status Proyek:</span>
                <x-badge :variant="$variant" size="sm">
                    {{ $project->getStatusLabel() }}
                </x-badge>
            </div>
        @endif
    </div>

    <!-- WARNING BANNER IF PROJECT IS FLAGGED / WARNED -->
    @if($project->warning_notes)
        <div class="p-4 rounded-2xl bg-amber-500/10 border border-amber-500/30 text-amber-800 dark:text-amber-200 flex items-start gap-3">
            <svg class="w-5 h-5 text-amber-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <div class="space-y-0.5 text-xs">
                <span class="font-bold block uppercase tracking-wider text-[10px] text-amber-600 dark:text-amber-400">Peringatan Resmi Moderator:</span>
                <p class="leading-relaxed">{{ $project->warning_notes }}</p>
            </div>
        </div>
    @endif

    <!-- ADMIN MODERATION CONTROL BAR -->
    @if(auth()->check() && auth()->user()->is_admin)
        <div class="p-3.5 rounded-2xl bg-rose-500/10 border border-rose-500/20 flex flex-wrap items-center justify-between gap-3 text-xs" x-data="{ openWarnModal: false, warnNotes: '{{ addslashes($project->warning_notes ?: 'Proyek ini sedang dalam peninjauan karena terindikasi melanggar pedoman komunitas NampungYuk.') }}' }">
            <div class="flex items-center gap-2">
                <span class="px-2 py-0.5 rounded-md bg-rose-500 text-white font-bold text-[10px] uppercase">Admin Mode</span>
                <span class="text-[#71717a] font-medium">Tindakan Moderasi:</span>
            </div>
            <div class="flex items-center gap-2">
                @if(!$project->warning_notes)
                    <button type="button" @click="openWarnModal = true" class="px-3 py-1.5 rounded-xl bg-amber-500/15 hover:bg-amber-500/25 text-amber-600 dark:text-amber-400 font-bold border border-amber-500/30 transition">
                        Pasang Peringatan
                    </button>
                @else
                    <form method="POST" action="{{ route('admin.projects.warn', $project->id) }}">
                        @csrf
                        <input type="hidden" name="action" value="remove">
                        <button type="submit" class="px-3 py-1.5 rounded-xl bg-amber-500/15 hover:bg-amber-500/25 text-amber-600 dark:text-amber-400 font-bold border border-amber-500/30 transition">
                            Cabut Peringatan
                        </button>
                    </form>
                @endif

                <form method="POST" action="{{ route('admin.projects.destroy', $project->id) }}" onsubmit="return confirm('Apakah Anda yakin ingin MENGHAPUS PROYEK ini secara permanen sebagai Admin?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-3 py-1.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold transition shadow-sm">
                        Hapus Proyek
                    </button>
                </form>
            </div>

            <!-- Modal Pasang Peringatan -->
            <div x-show="openWarnModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
                <div class="fixed inset-0 bg-black/60 dark:bg-black/80 backdrop-blur-md" @click="openWarnModal = false"></div>
                <div class="min-h-full flex items-center justify-center p-4">
                    <div class="relative w-full max-w-md bg-white dark:bg-[#0c0d12] border border-[#e4e4e7] dark:border-[#27272a] rounded-3xl p-6 shadow-2xl space-y-4 text-[#18181b] dark:text-[#fafafa]">
                        <h3 class="text-base font-bold">Pasang Peringatan Resmi pada Proyek</h3>
                        <form method="POST" action="{{ route('admin.projects.warn', $project->id) }}" class="space-y-4">
                            @csrf
                            <input type="hidden" name="action" value="set">
                            <div>
                                <label class="block text-xs font-semibold mb-1">Catatan Peringatan Publik:</label>
                                <textarea name="warning_notes" x-model="warnNotes" rows="3" required class="w-full px-3 py-2 rounded-xl bg-[#f8f9fa] dark:bg-[#16171d] border border-[#e4e4e7] dark:border-[#27272a] text-xs outline-none focus:border-amber-500 transition resize-none"></textarea>
                            </div>
                            <div class="flex items-center justify-end gap-2">
                                <button type="button" @click="openWarnModal = false" class="px-4 py-2 rounded-full border border-[#e4e4e7] dark:border-[#27272a] text-xs font-semibold text-[#71717a]">Batal</button>
                                <button type="submit" class="px-5 py-2 rounded-full bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs shadow-sm">Pasang Peringatan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- MAIN PROJECT ARTICLE CONTAINER -->
    <article class="ny-card bg-white dark:bg-[#0a0a0a] overflow-hidden border border-[#e4e4e7] dark:border-[#1f1f1f]">
        
        <!-- Header Info Area -->
        <div class="p-6 sm:p-8 border-b border-[#e4e4e7] dark:border-[#1f1f1f] space-y-5">
            
            <!-- Creator & Category Row -->
            <div class="flex items-center justify-between gap-4 flex-wrap">
                <div class="flex items-center gap-3">
                    @if($project->user)
                        <a href="{{ route('profile.show', $project->user->username) }}" class="shrink-0 hover:opacity-85 transition">
                            <x-user-avatar :user="$project->user" size="lg" />
                        </a>
                        <div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <a href="{{ route('profile.show', $project->user->username) }}" class="font-bold text-[#18181b] dark:text-[#fafafa] text-sm sm:text-base hover:text-[#0070f3] dark:hover:text-[#3291ff] transition">
                                    {{ $project->user->name }}
                                </a>
                                <span class="text-[#63636b] dark:text-[#a0a0a0] text-xs font-mono">&#64;{{ $project->user->username }}</span>
                            </div>
                            <div class="flex items-center gap-2 text-xs text-[#63636b] dark:text-[#a0a0a0] mt-0.5">
                                <span>Dipamerkan {{ $project->created_at->translatedFormat('d F Y') }}</span>
                                <span>&bull;</span>
                                <span>{{ $project->views_count }} tayangan</span>
                            </div>
                        </div>
                    @else
                        <x-user-avatar name="Anonim" size="lg" />
                        <div>
                            <p class="font-bold text-[#18181b] dark:text-[#fafafa] text-sm sm:text-base">Developer Anonim</p>
                            <span class="text-xs text-[#63636b]">{{ $project->created_at->translatedFormat('d F Y') }}</span>
                        </div>
                    @endif
                </div>

                <!-- Category Pill -->
                @if($project->category)
                    @auth
                        <a href="{{ route('projects.index', ['kategori' => $project->category->slug]) }}" 
                           class="text-xs font-mono font-medium px-3 py-1 rounded-lg bg-[#eeeeef] dark:bg-[#171717] text-[#18181b] dark:text-[#fafafa] hover:text-[#0070f3] dark:hover:text-[#3291ff] border border-[#e4e4e7] dark:border-[#1f1f1f] transition">
                            {{ $project->category->name }}
                        </a>
                    @else
                        <button type="button"
                                @click="if (window.openAuthModal) { window.openAuthModal('login'); } else { window.location.href = '{{ route('login') }}'; }"
                                class="text-xs font-mono font-medium px-3 py-1 rounded-lg bg-[#eeeeef] dark:bg-[#171717] text-[#18181b] dark:text-[#fafafa] hover:text-[#0070f3] dark:hover:text-[#3291ff] border border-[#e4e4e7] dark:border-[#1f1f1f] transition cursor-pointer">
                            {{ $project->category->name }}
                        </button>
                    @endauth
                @endif
            </div>

            <!-- Title & Tagline -->
            <div class="space-y-2">
                <h1 class="text-xl sm:text-3xl font-extrabold text-[#18181b] dark:text-[#fafafa] tracking-tight leading-snug">
                    {{ $project->title }}
                </h1>
                <p class="text-sm sm:text-base text-[#63636b] dark:text-[#a0a0a0] leading-relaxed max-w-3xl">
                    {{ $project->tagline }}
                </p>
            </div>

            <!-- External Project Action Buttons (Live Demo, GitHub, Prototype) -->
            <div class="flex items-center gap-2.5 flex-wrap pt-2">
                @if($project->demo_url)
                    <x-button :href="$project->demo_url" target="_blank" rel="noopener noreferrer" variant="primary" size="md">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                        </svg>
                        <span>Coba Live Demo</span>
                    </x-button>
                @endif

                @if($project->github_url)
                    <x-button :href="$project->github_url" target="_blank" rel="noopener noreferrer" variant="secondary" size="md">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.53 1.032 1.53 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z"/>
                        </svg>
                        <span>Source Code</span>
                    </x-button>
                @endif

                @if($project->prototype_url)
                    <x-button :href="$project->prototype_url" target="_blank" rel="noopener noreferrer" variant="soft" size="md">
                        <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>
                        </svg>
                        <span>Prototipe Interaktif</span>
                    </x-button>
                @endif

                <!-- Repost button (hidden for own project) -->
                @auth
                    @if($project->user_id !== auth()->id() && ! $project->isPrivate())
                        <button type="button" @click="repost()" :disabled="isReposting"
                                :class="isReposted ? 'bg-[#e8f2ff] dark:bg-[#3291ff]/12 text-[#0070f3] dark:text-[#3291ff] border-[#3291ff]/30' : ''"
                                class="btn-secondary text-xs py-2 px-3 disabled:opacity-60">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                            <span x-text="isReposted ? 'Reposted' : 'Repost'"></span>
                            <span class="hl-stat text-[10px] opacity-70" x-text="repostsCount > 0 ? repostsCount : ''"></span>
                        </button>
                    @endif
                @endauth

                @if($project->isRepost() && $project->repostedFrom)
                    <a href="{{ route('projects.show', $project->repostedFrom->slug) }}" class="btn-soft text-xs py-2 px-3" title="Project asal">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                        <span>Repost dari {{ $project->repostedFrom->user?->username ?? 'seseorang' }}</span>
                    </a>
                @endif

                <!-- Upvote control (likes only) -->
                <div class="inline-flex items-center rounded-lg bg-[#eeeeef] dark:bg-[#171717] p-0.5 border border-[#e4e4e7] dark:border-[#1f1f1f]">
                    @guest
                        <button type="button"
                                @click="if (window.openAuthModal) { window.openAuthModal('login'); } else { window.location.href = '{{ route('login') }}'; }"
                                aria-label="Upvote project ini"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md text-xs font-semibold text-[#63636b] dark:text-[#a0a0a0] hover:text-[#0070f3] dark:hover:text-[#3291ff] hover:bg-black/5 dark:hover:bg-white/5 transition active:scale-95 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7"/>
                            </svg>
                            <span class="font-mono text-xs" x-text="score">{{ $project->score }}</span>
                        </button>
                    @else
                        <button @click="vote('up')"
                                type="button"
                                :disabled="isVoting"
                                :aria-pressed="userVote === 'up'"
                                aria-label="Upvote project ini"
                                :class="{
                                    'bg-[#0070f3] text-white ': userVote === 'up',
                                    'text-[#63636b] dark:text-[#a0a0a0] hover:text-[#0070f3] dark:hover:text-[#3291ff]': userVote !== 'up'
                                }"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md text-xs font-semibold transition active:scale-95 disabled:opacity-60">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7"/>
                            </svg>
                            <span class="font-mono text-xs" x-text="score">{{ $project->score }}</span>
                        </button>
                    @endguest
                </div>

                <!-- Bookmark Button -->
                @guest
                    <button type="button"
                            @click="if (window.openAuthModal) { window.openAuthModal('login'); } else { window.location.href = '{{ route('login') }}'; }"
                            aria-label="Simpan project ke koleksi"
                            class="p-2 rounded-lg border border-[#e4e4e7] dark:border-[#1f1f1f] bg-white dark:bg-[#0a0a0a] text-[#63636b] dark:text-[#a0a0a0] hover:text-[#18181b] dark:hover:text-white transition active:scale-95 cursor-pointer"
                            title="Simpan ke Koleksi">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                        </svg>
                    </button>
                @else
                    <button @click="toggleBookmark()"
                            type="button"
                            :disabled="isBookmarking"
                            :aria-pressed="isBookmarked"
                            aria-label="Simpan project ke koleksi"
                            :class="{
                                'text-[#0070f3] dark:text-[#3291ff] bg-[#e8f2ff] dark:bg-[#3291ff]/12 border-[#3291ff]/30 dark:border-[#3291ff]/30': isBookmarked,
                                'text-[#63636b] dark:text-[#a0a0a0] hover:text-[#18181b] dark:hover:text-white border-[#e4e4e7] dark:border-[#1f1f1f] bg-white dark:bg-[#0a0a0a]': !isBookmarked
                            }"
                            class="p-2 rounded-lg border transition active:scale-95 disabled:opacity-60"
                            title="Simpan ke Koleksi">
                        <svg class="w-4 h-4" :fill="isBookmarked ? 'currentColor' : 'none'" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                        </svg>
                    </button>
                @endguest

                <!-- Overflow menu (⋯): Bagikan + Laporkan -->
                <div class="relative" x-data="{ menuOpen: false }" @click.outside="menuOpen = false">
                    <button @click="menuOpen = !menuOpen"
                            type="button"
                            aria-label="Menu lainnya"
                            aria-haspopup="true"
                            :aria-expanded="menuOpen"
                            class="p-2 rounded-lg border border-[#e4e4e7] dark:border-[#1f1f1f] bg-white dark:bg-[#0a0a0a] text-[#63636b] dark:text-[#a0a0a0] hover:text-[#18181b] dark:hover:text-white transition active:scale-95"
                            title="Aksi lainnya">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                            <circle cx="5" cy="12" r="2"/><circle cx="12" cy="12" r="2"/><circle cx="19" cy="12" r="2"/>
                        </svg>
                    </button>

                    <div x-show="menuOpen" x-cloak
                         x-transition:enter="transition ease-out duration-120"
                         x-transition:enter-start="opacity-0 -translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         class="absolute right-0 top-full mt-1.5 z-40 min-w-[190px] rounded-xl bg-white dark:bg-[#0a0a0a] border border-[#e4e4e7] dark:border-[#1f1f1f] shadow-lg overflow-hidden text-xs">
                        <!-- Share -->
                        <button @click="copyLink(); menuOpen = false"
                                type="button"
                                class="w-full flex items-center gap-2.5 px-3.5 py-2.5 text-left text-[#18181b] dark:text-[#fafafa] hover:bg-[#f5f5f5] dark:hover:bg-[#111111] transition">
                            <svg class="w-4 h-4 text-[#63636b] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/></svg>
                            <span>Bagikan / Salin Tautan</span>
                        </button>

                        <!-- Report (never for your own project) -->
                        @if($project->user_id !== auth()->id())
                            <div class="border-t border-[#e4e4e7] dark:border-[#1f1f1f]"></div>
                            @guest
                                <button @click="if (window.openAuthModal) { window.openAuthModal('login'); } else { window.location.href = '{{ route('login') }}'; }"
                                        type="button"
                                        class="w-full flex items-center gap-2.5 px-3.5 py-2.5 text-left text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/30 transition">
                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                    <span>Laporkan Project</span>
                                </button>
                            @else
                                <button @click="window.openReportModal({ type: 'project', id: {{ $project->id }}, title: '{{ addslashes($project->title) }}' }); menuOpen = false"
                                        type="button"
                                        class="w-full flex items-center gap-2.5 px-3.5 py-2.5 text-left text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/30 transition">
                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                    <span>Laporkan Project</span>
                                </button>
                            @endguest
                        @endif
                    </div>
                </div>
            </div>

        </div>

        <!-- TWO-COLUMN: Gallery (left) + About & Tools (right) -->
        @php $media = $project->galleryMedia(); @endphp
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-0 border-b border-[#e4e4e7] dark:border-[#1f1f1f]">

            <!-- LEFT: Media gallery (images + videos) -->
            <div class="border-b lg:border-b-0 lg:border-r border-[#e4e4e7] dark:border-[#1f1f1f] bg-[#000000]">
                @if(count($media) > 0)
                    <div x-data="{ active: 0 }">
                        <div class="overflow-hidden aspect-video flex items-center justify-center bg-[#000000]">
                            <template x-for="(item, i) in {{ Illuminate\Support\Js::from($media) }}" :key="i">
                                <template x-if="active === i">
                                    <span class="w-full h-full flex items-center justify-center">
                                        <template x-if="item.type === 'video'">
                                            <video :src="item.url" :poster="item.poster || null" controls playsinline preload="metadata"
                                                   class="w-full h-full object-contain"></video>
                                        </template>
                                        <template x-if="item.type === 'image'">
                                            <img :src="item.url"
                                                 alt="{{ $project->title }}"
                                                 loading="lazy"
                                                 decoding="async"
                                                 onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1555066931-4365d14bab8c?auto=format&fit=crop&w=1200&q=80';"
                                                 class="w-full h-full object-contain">
                                        </template>
                                    </span>
                                </template>
                            </template>
                        </div>

                        @if(count($media) > 1)
                            <div class="flex gap-2 p-3 overflow-x-auto bg-[#ffffff] dark:bg-[#000000] border-t border-[#e4e4e7] dark:border-[#1f1f1f]">
                                @foreach($media as $i => $item)
                                    <button type="button" @click="active = {{ $i }}"
                                            :class="active === {{ $i }} ? 'ring-2 ring-[#0070f3] dark:ring-[#3291ff]' : 'opacity-60 hover:opacity-100'"
                                            class="relative shrink-0 w-20 h-14 rounded-lg overflow-hidden border border-[#e4e4e7] dark:border-[#1f1f1f] transition bg-[#000000]">
                                        @if($item['type'] === 'video')
                                            @if(!empty($item['poster']))
                                                <img src="{{ $item['poster'] }}" alt="" loading="lazy" decoding="async" class="w-full h-full object-cover">
                                            @else
                                                <video src="{{ $item['url'] }}" preload="metadata" muted class="w-full h-full object-cover pointer-events-none"></video>
                                            @endif
                                            <span class="absolute inset-0 flex items-center justify-center bg-black/30">
                                                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                            </span>
                                        @else
                                            <img src="{{ $item['url'] }}" alt="" loading="lazy" decoding="async" class="w-full h-full object-cover">
                                        @endif
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @else
                    <div class="aspect-video flex items-center justify-center text-[#63636b] text-xs">Tanpa media</div>
                @endif
            </div>

            <!-- RIGHT: About + Tools -->
            <div class="p-6 sm:p-8 space-y-6">
                <!-- About This Project -->
                <section class="space-y-3">
                    <h2 class="text-sm font-bold uppercase tracking-wider text-[#63636b] dark:text-[#a0a0a0] flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#0070f3] dark:bg-[#3291ff]"></span>
                        <span>Tentang Project Ini</span>
                    </h2>

                    @if($project->description)
                        <div class="text-xs sm:text-sm text-[#18181b] dark:text-[#fafafa] leading-relaxed whitespace-pre-line space-y-3">
                            {{ $project->description }}
                        </div>
                    @else
                        <p class="text-xs text-[#63636b] italic">Creator belum menyertakan deskripsi panjang untuk project ini.</p>
                    @endif
                </section>

                <!-- Tools Used -->
                @if(is_array($project->tech_stacks) && count($project->tech_stacks) > 0)
                    <section class="space-y-3 pt-5 border-t border-[#e4e4e7] dark:border-[#1f1f1f]">
                        <h2 class="text-sm font-bold uppercase tracking-wider text-[#63636b] dark:text-[#a0a0a0] flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#0070f3] dark:bg-[#3291ff]"></span>
                            <span>Teknologi & Tools yang Digunakan</span>
                        </h2>
                        <div class="flex flex-wrap gap-2">
                            @foreach($project->tech_stacks as $tech)
                                <x-tech-pill :name="$tech" />
                            @endforeach
                        </div>
                    </section>
                @endif
            </div>
        </div>

        <!-- ARTICLE BODY CONTENT (rest) -->
        <div class="p-6 sm:p-8 space-y-8">

            <!-- SECTION 3: Knowledge Sharing — What I Learned -->
            @if(!empty(trim((string)$project->learnings)))
                <section class="space-y-3 pt-6 border-t border-[#e4e4e7]/80 dark:border-[#1f1f1f]">
                    <h2 class="text-sm font-bold uppercase tracking-wider text-[#63636b] dark:text-[#a0a0a0] font-mono flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        <span>Apa yang Saya Pelajari (Learnings)</span>
                    </h2>
                    
                    <div class="p-4 rounded-xl bg-emerald-50/40 dark:bg-emerald-950/20 border border-emerald-200/60 dark:border-emerald-800/40 text-xs sm:text-sm text-[#18181b] dark:text-[#fafafa] leading-relaxed whitespace-pre-line">
                        {{ $project->learnings }}
                    </div>
                </section>
            @endif

            <!-- SECTION 4: Knowledge Sharing — Challenges -->
            @if(!empty(trim((string)$project->challenges)))
                <section class="space-y-3 pt-6 border-t border-[#e4e4e7]/80 dark:border-[#1f1f1f]">
                    <h2 class="text-sm font-bold uppercase tracking-wider text-[#63636b] dark:text-[#a0a0a0] font-mono flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                        <span>Tantangan yang Dihadapi (Challenges)</span>
                    </h2>
                    
                    <div class="p-4 rounded-xl bg-amber-50/40 dark:bg-amber-950/20 border border-amber-200/60 dark:border-amber-800/40 text-xs sm:text-sm text-[#18181b] dark:text-[#fafafa] leading-relaxed whitespace-pre-line">
                        {{ $project->challenges }}
                    </div>
                </section>
            @endif

            <!-- SECTION 5: How To Run (Truthful Setup Instructions Only) -->
            @if($project->hasSetupInstructions())
                <section class="space-y-3 pt-6 border-t border-[#e4e4e7]/80 dark:border-[#1f1f1f]">
                    <div class="flex items-center justify-between">
                        <h2 class="text-sm font-bold uppercase tracking-wider text-[#63636b] dark:text-[#a0a0a0] font-mono flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#0070f3] dark:bg-[#3291ff]"></span>
                            <span>Cara Menjalankan Project (Setup Instructions)</span>
                        </h2>

                        <button @click="navigator.clipboard.writeText(`{{ addslashes($project->setup_instructions) }}`); if(window.notify) notify('Petunjuk instalasi disalin!');"
                                type="button"
                                class="text-xs font-mono text-[#0070f3] dark:text-[#3291ff] hover:underline inline-flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                            </svg>
                            <span>Salin Instruksi</span>
                        </button>
                    </div>
                    
                    <pre class="p-4 rounded-xl bg-[#000000] text-[#3291ff] font-mono text-xs overflow-x-auto leading-relaxed border border-[#1f1f1f]"><code>{{ $project->setup_instructions }}</code></pre>
                </section>
            @endif

        </div>

    </article>

    <!-- SECTION 6: Comments & Discussion -->
    <section id="komentar" class="ny-card p-6 sm:p-8 space-y-6 bg-white dark:bg-[#0a0a0a] border border-[#e4e4e7] dark:border-[#1f1f1f]">
        <div class="flex items-center justify-between">
            <h2 class="text-base sm:text-lg font-bold text-[#18181b] dark:text-[#fafafa] flex items-center gap-2">
                <span>Diskusi & Review Teknis</span>
                <span class="text-xs font-mono font-normal px-2 py-0.5 rounded-md bg-[#eeeeef] dark:bg-[#171717] text-[#63636b] dark:text-[#a0a0a0]">
                    {{ $project->comments_count }}
                </span>
            </h2>
        </div>

        <!-- Add Comment Form -->
        @auth
            <form action="{{ route('projects.comment', $project) }}" method="POST" class="space-y-3">
                @csrf
                <div class="space-y-1.5">
                    <label for="comment-content" class="text-xs font-semibold text-[#18181b] dark:text-[#fafafa]">
                        Tuliskan tanggapan, pertanyaan, atau masukan untuk creator:
                    </label>
                    <textarea name="content" 
                              id="comment-content"
                              rows="3" 
                              required
                              maxlength="1000"
                              placeholder="Bagikan apresiasi atau review arsitektur secara konstruktif..."
                              class="ny-input text-xs sm:text-sm"></textarea>
                </div>

                <div class="flex justify-end">
                    <x-button type="submit" variant="primary" size="sm">
                        Kirim Tanggapan
                    </x-button>
                </div>
            </form>
        @else
            <div class="p-4 rounded-xl bg-[#ffffff] dark:bg-[#000000] border border-[#e4e4e7] dark:border-[#1f1f1f] flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 text-xs">
                <div class="space-y-0.5">
                    <p class="font-semibold text-[#18181b] dark:text-[#fafafa]">Ingin memberikan masukan atau pertanyaan?</p>
                    <p class="text-[#63636b] dark:text-[#a0a0a0]">Silakan masuk dengan akun kamu untuk bergabung dalam diskusi teknis ini.</p>
                </div>
                <button type="button"
                        @click="if (window.openAuthModal) { window.openAuthModal('login'); } else { window.location.href = '{{ route('login') }}'; }"
                        class="btn-primary text-xs py-2 px-3.5 shrink-0 cursor-pointer">
                    Masuk untuk Berkomentar
                </button>
            </div>
        @endauth

        <!-- Comments List -->
        <div class="space-y-3 pt-2">
            @forelse($project->comments as $comment)
                <x-comment-card :comment="$comment" />
            @empty
                <p class="text-xs text-[#63636b] dark:text-[#a0a0a0] text-center py-6">
                    Belum ada diskusi untuk project ini. Jadilah yang pertama memberikan masukan!
                </p>
            @endforelse
        </div>
    </section>

    <!-- SECTION 7: Related Projects in Same Category -->
    @if(isset($relatedProjects) && count($relatedProjects) > 0)
        <section class="space-y-4 pt-4">
            <h2 class="text-sm font-bold uppercase tracking-wider text-[#63636b] dark:text-[#a0a0a0] font-mono">
                Project Terkait di Kategori Ini
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @foreach($relatedProjects as $rel)
                    <div class="ny-card p-4 space-y-2 bg-white dark:bg-[#0a0a0a] flex flex-col justify-between">
                        <div class="space-y-1.5">
                            @guest
                                <button type="button"
                                        @click="if (window.openAuthModal) { window.openAuthModal('login'); } else { window.location.href = '{{ route('login') }}'; }"
                                        class="text-[11px] font-mono text-[#0070f3] dark:text-[#3291ff] font-semibold hover:underline cursor-pointer">
                                    {{ $rel->category->name }}
                                </button>
                            @else
                                <a href="{{ route('projects.index', ['kategori' => $rel->category->slug]) }}" class="text-[11px] font-mono text-[#0070f3] dark:text-[#3291ff] font-semibold hover:underline">
                                    {{ $rel->category->name }}
                                </a>
                            @endguest
                            <h3 class="font-bold text-sm text-[#18181b] dark:text-[#fafafa] hover:text-[#0070f3] transition">
                                @guest
                                    <button type="button"
                                            @click="if (window.openAuthModal) { window.openAuthModal('login'); } else { window.location.href = '{{ route('login') }}'; }"
                                            class="text-left font-bold text-sm text-[#18181b] dark:text-[#fafafa] hover:text-[#0070f3] transition cursor-pointer">
                                        {{ $rel->title }}
                                    </button>
                                @else
                                    <a href="{{ route('projects.show', $rel->slug) }}">
                                        {{ $rel->title }}
                                    </a>
                                @endguest
                            </h3>
                            <p class="text-xs text-[#63636b] dark:text-[#a0a0a0] line-clamp-2">
                                {{ $rel->tagline }}
                            </p>
                        </div>

                        <div class="flex items-center justify-between text-[11px] pt-3 border-t border-[#e4e4e7]/60 dark:border-[#1f1f1f]/60">
                            <span class="font-medium text-[#18181b] dark:text-[#fafafa]">
                                {{ $rel->user?->name ?? 'Developer' }}
                            </span>
                            <span class="font-mono text-[#0070f3] font-semibold">
                                &uarr; {{ $rel->score }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

</div>
@endsection
