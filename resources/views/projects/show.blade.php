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
         packageManager: 'git',
         get installCommand() {
             const repo = '{{ $project->github_url ? $project->github_url . '.git' : 'https://github.com/developer/' . $project->slug . '.git' }}';
             if (this.packageManager === 'git') return 'git clone ' + repo;
             if (this.packageManager === 'npm') return 'npm install && npm run dev';
             if (this.packageManager === 'pnpm') return 'pnpm install && pnpm dev';
             if (this.packageManager === 'bun') return 'bun install && bun dev';
             return 'git clone ' + repo;
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
                 const data = await res.json();
                 if (data.success) {
                     this.score = data.score;
                     this.userVote = data.user_vote;
                     notify(type === 'up' ? 'Upvoted! Terima kasih atas dukungannya.' : 'Feedback tercatat.');
                 }
             } catch(e) {
                 notify('Gagal memberikan vote');
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
                 const data = await res.json();
                 if (data.success) {
                     this.score = data.score ?? this.score;
                     this.isBookmarked = data.bookmarked;
                     notify(this.isBookmarked ? 'Proyek disimpan ke Koleksi!' : 'Dihapus dari Koleksi.');
                 }
             } catch(e) {
                 notify('Gagal menyimpan bookmark');
             } finally {
                 this.isBookmarking = false;
             }
         },
         copyCommand() {
             navigator.clipboard.writeText(this.installCommand);
             notify('Command instalasi disalin ke clipboard!');
         },
         copyLink() {
             navigator.clipboard.writeText(window.location.href);
             notify('Tautan berhasil disalin ke clipboard!');
         }
     }">

    <!-- Top Navigation & Breadcrumb -->
    <div class="flex items-center justify-between text-xs font-mono">
        <a href="{{ route('projects.index') }}" 
           class="inline-flex items-center gap-1.5 text-neutral-500 dark:text-neutral-400 hover:text-orange-500 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span>Kembali ke Feed</span>
        </a>

        <div class="flex items-center gap-2">
            <span class="text-neutral-400">Status:</span>
            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-mono font-semibold bg-emerald-500/10 text-emerald-500 border border-emerald-500/20">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-radar"></span>
                <span>Production Ready</span>
            </span>
        </div>
    </div>

    <!-- Main Workstation Card (Vercel / Supabase Style) -->
    <article class="spotlight-card border border-neutral-200/80 dark:border-white/[0.08] bg-white dark:bg-[#09090b] overflow-hidden">
        
        <!-- Header Info Area -->
        <div class="p-6 sm:p-8 border-b border-neutral-100 dark:border-white/[0.08] space-y-4">
            
            <!-- Top developer & category bar -->
            <div class="flex items-center justify-between gap-4 flex-wrap">
                
                <!-- Developer Profile link -->
                <div class="flex items-center gap-3">
                    @if($project->user)
                        <a href="{{ route('profile.show', $project->user->username) }}" class="shrink-0 hover:opacity-85 transition">
                            <img src="{{ $project->user->avatar ?? 'https://api.dicebear.com/7.x/bottts/svg?seed='.urlencode($project->user->name ?? 'Dev') }}" 
                                 alt="{{ $project->user->name ?? 'Anonymous' }}" 
                                 class="w-11 h-11 rounded-2xl bg-neutral-100 dark:bg-[#1a1a1e] object-cover ring-1 ring-neutral-200 dark:ring-white/10 shadow-2xs">
                        </a>
                    @else
                        <img src="https://api.dicebear.com/7.x/bottts/svg?seed=Anonymous" 
                             alt="Anonymous" 
                             class="w-11 h-11 rounded-2xl bg-neutral-100 dark:bg-[#1a1a1e] object-cover">
                    @endif
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            @if($project->user)
                                <a href="{{ route('profile.show', $project->user->username) }}" class="font-bold text-neutral-900 dark:text-white text-sm sm:text-base hover:text-orange-500 transition">
                                    {{ $project->user->name }}
                                </a>
                                <span class="text-neutral-400 text-xs font-mono">&#64;{{ $project->user->username }}</span>
                            @else
                                <span class="font-bold text-neutral-900 dark:text-white text-sm sm:text-base">Anonymous Dev</span>
                            @endif
                        </div>
                        <div class="flex items-center gap-2 text-xs text-neutral-400 font-mono mt-0.5">
                            <span>Dipamerkan {{ $project->created_at->diffForHumans() }}</span>
                            <span>&bull;</span>
                            <span>{{ $project->views_count }} tayangan</span>
                        </div>
                    </div>
                </div>

                <!-- Category & Live Demo Status -->
                <div class="flex items-center gap-2">
                    @if($project->demo_url)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 text-xs font-mono font-bold border border-emerald-500/20">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-radar"></span>
                            <span>Live Demo</span>
                        </span>
                    @endif

                    <a href="{{ route('projects.index', ['kategori' => $project->category->slug]) }}" 
                       class="inline-flex items-center gap-1.5 text-xs font-mono font-semibold px-3 py-1.5 rounded-lg bg-neutral-100 dark:bg-white/[0.04] text-neutral-700 dark:text-neutral-300 hover:text-orange-500 border border-neutral-200 dark:border-white/[0.08] transition">
                        <span>{{ $project->category->name }}</span>
                    </a>
                </div>
            </div>

            <!-- Title & Tagline -->
            <div class="space-y-2">
                <h1 class="text-xl sm:text-2xl lg:text-3xl font-extrabold text-neutral-900 dark:text-white leading-tight">
                    {{ $project->title }}
                </h1>
                <p class="text-sm sm:text-base text-neutral-600 dark:text-neutral-400 leading-relaxed">
                    {{ $project->tagline }}
                </p>
            </div>

            <!-- Action CTAs: Demo, GitHub, Bookmark, Share -->
            <div class="flex flex-wrap items-center gap-2.5 pt-2">
                @if($project->demo_url)
                    <a href="{{ $project->demo_url }}" target="_blank" rel="noopener noreferrer"
                       class="btn-vercel-primary inline-flex items-center gap-2 px-4 py-2 text-xs sm:text-sm font-semibold rounded-xl shadow-xs active:scale-95 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                        </svg>
                        <span>Buka Live Demo</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                        </svg>
                    </a>
                @endif

                @if($project->github_url)
                    <a href="{{ $project->github_url }}" target="_blank" rel="noopener noreferrer"
                       class="inline-flex items-center gap-2 px-4 py-2 bg-neutral-100 dark:bg-white/[0.05] hover:bg-neutral-200 dark:hover:bg-white/[0.1] text-neutral-900 dark:text-white text-xs sm:text-sm font-semibold rounded-xl border border-neutral-300 dark:border-white/[0.08] shadow-xs active:scale-95 transition">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.53 1.032 1.53 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z"/>
                        </svg>
                        <span>GitHub Repository</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                        </svg>
                    </a>
                @endif

                @if($project->prototype_url)
                    <a href="{{ $project->prototype_url }}" target="_blank" rel="noopener noreferrer"
                       class="inline-flex items-center gap-2 px-4 py-2 bg-purple-500/10 hover:bg-purple-500/20 text-purple-600 dark:text-purple-400 text-xs sm:text-sm font-semibold rounded-xl border border-purple-500/30 shadow-xs active:scale-95 transition"
                       title="Buka Prototipe Interaktif (Figma / Framer / v0)">
                        <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>
                        </svg>
                        <span>Buka Prototype</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                        </svg>
                    </a>
                @endif

                <button @click="toggleBookmark()" 
                        :class="{ 'text-orange-500 bg-orange-500/10 font-bold border-orange-500/30': isBookmarked, 'text-neutral-700 dark:text-neutral-300 bg-neutral-100 dark:bg-white/[0.04] hover:text-orange-500': !isBookmarked }"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold rounded-xl border border-neutral-200 dark:border-white/[0.08] transition active:scale-95">
                    <svg class="w-3.5 h-3.5" :fill="isBookmarked ? 'currentColor' : 'none'" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                    </svg>
                    <span x-text="isBookmarked ? 'Tersimpan di Koleksi' : 'Simpan ke Koleksi'"></span>
                </button>

                <button @click="copyLink()" 
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-neutral-100 dark:bg-white/[0.04] hover:bg-neutral-200 dark:hover:bg-white/[0.08] text-neutral-700 dark:text-neutral-300 text-xs font-semibold rounded-xl border border-neutral-200 dark:border-white/[0.08] transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
                    </svg>
                    <span>Bagikan</span>
                </button>
            </div>
        </div>

        <!-- Terminal Install Command Window with Package Manager Switcher -->
        <div class="p-6 sm:p-8 bg-neutral-50 dark:bg-[#070709] border-b border-neutral-100 dark:border-white/[0.08]">
            <div class="rounded-xl border border-neutral-200 dark:border-white/[0.08] bg-[#09090b] overflow-hidden">
                <!-- macOS window bar with package manager tabs -->
                <div class="code-window-bar flex items-center justify-between flex-wrap gap-2">
                    <div class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-full window-dot-red"></span>
                        <span class="w-2.5 h-2.5 rounded-full window-dot-yellow"></span>
                        <span class="w-2.5 h-2.5 rounded-full window-dot-green"></span>
                        <span class="ml-2 text-neutral-400 font-mono text-[11px]">quick_install.sh</span>
                    </div>

                    <div class="flex items-center gap-1 font-mono text-[11px]">
                        <button @click="packageManager = 'git'" :class="packageManager === 'git' ? 'text-white bg-white/10 font-bold' : 'text-neutral-400 hover:text-white'" class="px-2 py-0.5 rounded transition">git</button>
                        <button @click="packageManager = 'npm'" :class="packageManager === 'npm' ? 'text-white bg-white/10 font-bold' : 'text-neutral-400 hover:text-white'" class="px-2 py-0.5 rounded transition">npm</button>
                        <button @click="packageManager = 'pnpm'" :class="packageManager === 'pnpm' ? 'text-white bg-white/10 font-bold' : 'text-neutral-400 hover:text-white'" class="px-2 py-0.5 rounded transition">pnpm</button>
                        <button @click="packageManager = 'bun'" :class="packageManager === 'bun' ? 'text-white bg-white/10 font-bold' : 'text-neutral-400 hover:text-white'" class="px-2 py-0.5 rounded transition">bun</button>
                    </div>

                    <button @click="copyCommand()" class="text-orange-400 hover:text-orange-300 font-mono text-[11px] flex items-center gap-1 transition">
                        <span>Copy</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>
                    </button>
                </div>
                
                <div class="p-4 font-mono text-xs sm:text-sm text-neutral-200 flex items-center gap-2 select-all overflow-x-auto">
                    <span class="text-emerald-400 select-none">$</span>
                    <span x-text="installCommand"></span>
                </div>
            </div>
        </div>

        <!-- Preview Image -->
        @if($project->thumbnail)
            <div class="bg-neutral-950 flex items-center justify-center border-b border-neutral-200 dark:border-white/[0.08] relative overflow-hidden">
                <img src="{{ $project->thumbnail }}" 
                     alt="{{ $project->title }}" 
                     class="w-full max-h-[520px] object-contain">
            </div>
        @endif

        <!-- Tech Stacks -->
        @if(is_array($project->tech_stacks) && count($project->tech_stacks) > 0)
            <div class="p-6 sm:p-8 pb-2 border-b border-neutral-100 dark:border-white/[0.06]">
                <span class="text-[11px] font-mono font-bold text-neutral-400 uppercase tracking-widest block mb-2.5">
                    Tech Stacks & Dependencies
                </span>
                <div class="flex flex-wrap gap-1.5 font-mono">
                    @foreach($project->tech_stacks as $tech)
                        <a href="{{ route('projects.index', ['tech' => $tech]) }}" class="linear-pill">
                            #{{ $tech }}
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Description / Documentation -->
        <div class="p-6 sm:p-8 pt-4">
            <span class="text-[11px] font-mono font-bold text-neutral-400 uppercase tracking-widest block mb-2.5">
                Bedah Arsitektur & Catatan Teknis
            </span>
            <div class="text-sm leading-relaxed text-neutral-700 dark:text-neutral-300 whitespace-pre-line p-5 rounded-2xl bg-neutral-50 dark:bg-[#0c0c0e] border border-neutral-200 dark:border-white/[0.06] font-mono text-xs sm:text-sm">
                {{ $project->description }}
            </div>
        </div>

        <!-- Bottom Upvote Bar -->
        <div class="p-5 sm:p-8 bg-neutral-50 dark:bg-[#070709] border-t border-neutral-200 dark:border-white/[0.08] flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="flex items-center bg-white dark:bg-[#141418] border border-neutral-200 dark:border-white/[0.08] rounded-xl p-1 shadow-xs">
                    <button @click="vote('up')" 
                            :class="{ 'text-orange-500 bg-orange-500/10 font-bold': userVote === 'up', 'text-neutral-600 dark:text-neutral-400 hover:text-orange-500': userVote !== 'up' }"
                            class="flex items-center gap-1.5 px-4 py-2 rounded-lg font-semibold text-xs transition active:scale-95">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M3.293 9.707a1 1 0 010-1.414l6-6a1 1 0 011.414 0l6 6a1 1 0 01-1.414 1.414L11 5.414V17a1 1 0 11-2 0V5.414L4.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <span>Upvote</span>
                    </button>
                    <span class="px-3 font-mono font-bold text-neutral-900 dark:text-neutral-100 text-xs" x-text="score"></span>
                    <button @click="vote('down')" 
                            :class="{ 'text-rose-500 bg-rose-500/10': userVote === 'down', 'text-neutral-400 hover:text-rose-500': userVote !== 'down' }"
                            class="p-2 rounded-lg text-xs transition active:scale-95">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 10.293a1 1 0 010 1.414l-6 6a1 1 0 01-1.414 0l-6-6a1 1 0 111.414-1.414L9 14.586V3a1 1 0 012 0v11.586l4.293-4.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                    </button>
                </div>
                <span class="text-xs text-neutral-400 hidden sm:inline-flex items-center gap-1.5 font-mono">
                    <svg class="w-3.5 h-3.5 text-orange-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M3.293 9.707a1 1 0 010-1.414l6-6a1 1 0 011.414 0l6 6a1 1 0 01-1.414 1.414L11 5.414V17a1 1 0 11-2 0V5.414L4.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                    </svg>
                    <span>Beri apresiasi upvote jika kamu menyukai arsitektur & kodenya!</span>
                </span>
            </div>
        </div>

    </article>

    <!-- Comments / Peer Code Review Section -->
    <section id="komentar" class="spotlight-card border border-neutral-200/80 dark:border-white/[0.08] bg-white dark:bg-[#09090b] p-6 sm:p-8 space-y-5">
        <div class="flex items-center justify-between border-b border-neutral-100 dark:border-white/[0.08] pb-3">
            <h3 class="font-bold text-neutral-900 dark:text-white text-sm sm:text-base flex items-center gap-2">
                <svg class="w-4 h-4 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
                <span>Peer Code Review & Technical Feedback</span>
                <span class="text-xs font-mono px-2 py-0.5 rounded-md bg-neutral-100 dark:bg-white/[0.05] text-neutral-500 dark:text-neutral-400">
                    {{ $project->comments_count }}
                </span>
            </h3>
        </div>

        <!-- Post Comment Form -->
        <form action="{{ route('projects.comment', $project) }}" method="POST" class="space-y-3">
            @csrf
            @guest
                <div>
                    <label for="guest_name" class="block text-xs font-semibold text-neutral-700 dark:text-neutral-300 mb-1 font-mono">
                        Nama / Handle Developer
                    </label>
                    <input type="text" name="guest_name" id="guest_name" required
                           placeholder="Contoh: Rian Developer" 
                           class="w-full sm:w-72 px-3 py-2 text-xs bg-neutral-50 dark:bg-[#111114] border border-neutral-300 dark:border-white/[0.08] rounded-xl focus:border-orange-500 text-neutral-900 dark:text-white">
                </div>
            @endguest

            <div>
                <label for="content" class="block text-xs font-semibold text-neutral-700 dark:text-neutral-300 mb-1 font-mono">
                    Tulis Review Arsitektur atau Feedback Teknis
                </label>
                <textarea name="content" id="content" rows="3" required
                          placeholder="Berikan masukan kode, arsitektur, optimasi query, atau feedback teknis..." 
                          class="w-full px-4 py-3 text-xs sm:text-sm bg-neutral-50 dark:bg-[#111114] border border-neutral-300 dark:border-white/[0.08] rounded-xl focus:border-orange-500 text-neutral-900 dark:text-white placeholder-neutral-400 font-mono"></textarea>
            </div>

            <div class="flex justify-end">
                <button type="submit" 
                        class="btn-vercel-primary inline-flex items-center gap-1.5 px-5 py-2 text-xs sm:text-sm rounded-xl shadow-xs active:scale-95 transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                    </svg>
                    <span>Kirim Review Codingan</span>
                </button>
            </div>
        </form>

        <!-- Comments List -->
        <div class="space-y-3 pt-2">
            @forelse($project->comments as $comment)
                <div class="p-4 rounded-xl bg-neutral-50 dark:bg-[#111114] border border-neutral-200 dark:border-white/[0.06] flex gap-3">
                    @if($comment->user)
                        <a href="{{ route('profile.show', $comment->user->username) }}" class="shrink-0 hover:opacity-85 transition">
                            <img src="{{ $comment->authorAvatar() }}" 
                                 alt="{{ $comment->authorName() }}" 
                                 class="w-8 h-8 rounded-lg bg-neutral-200 dark:bg-[#1a1a1e] object-cover ring-1 ring-neutral-200 dark:ring-white/10">
                        </a>
                    @else
                        <img src="{{ $comment->authorAvatar() }}" 
                             alt="{{ $comment->authorName() }}" 
                             class="w-8 h-8 rounded-lg bg-neutral-200 dark:bg-[#1a1a1e] shrink-0">
                    @endif
                    <div class="space-y-1 min-w-0 flex-1">
                        <div class="flex items-center justify-between gap-2">
                            @if($comment->user)
                                <a href="{{ route('profile.show', $comment->user->username) }}" class="text-xs font-bold text-neutral-900 dark:text-neutral-200 hover:text-orange-500 transition">
                                    {{ $comment->authorName() }}
                                </a>
                            @else
                                <span class="text-xs font-bold text-neutral-900 dark:text-neutral-200">
                                    {{ $comment->authorName() }}
                                </span>
                            @endif
                            <span class="text-[11px] text-neutral-400 font-mono">
                                {{ $comment->created_at->diffForHumans() }}
                            </span>
                        </div>
                        <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-300 leading-relaxed whitespace-pre-line">
                            {{ $comment->content }}
                        </p>
                    </div>
                </div>
            @empty
                <p class="text-center py-6 text-xs text-neutral-400 font-mono">
                    Belum ada review teknis. Jadilah developer pertama yang memberikan masukan!
                </p>
            @endforelse
        </div>
    </section>

    <!-- Related Projects Section -->
    @if(count($relatedProjects) > 0)
        <section class="space-y-3 pt-2">
            <h4 class="font-bold text-neutral-900 dark:text-white text-xs uppercase font-mono tracking-wider">
                Karya Codingan Serupa di Kategori {{ $project->category->name }}
            </h4>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @foreach($relatedProjects as $related)
                    <a href="{{ route('projects.show', $related->slug) }}" 
                       class="spotlight-card border border-neutral-200/80 dark:border-white/[0.08] bg-white dark:bg-[#0a0a0c] p-4 group transition duration-150 block">
                        <h4 class="font-bold text-xs sm:text-sm text-neutral-900 dark:text-neutral-100 group-hover:text-orange-500 transition truncate">
                            {{ $related->title }}
                        </h4>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400 line-clamp-1 mt-1">
                            {{ $related->tagline }}
                        </p>
                        <div class="flex items-center justify-between mt-3 text-[11px] text-neutral-400 font-mono">
                            <span class="inline-flex items-center gap-1 text-orange-500 font-bold">
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M3.293 9.707a1 1 0 010-1.414l6-6a1 1 0 011.414 0l6 6a1 1 0 01-1.414 1.414L11 5.414V17a1 1 0 11-2 0V5.414L4.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                                </svg>
                                <span>{{ $related->score }} pts</span>
                            </span>
                            <span>{{ $related->comments_count }} review</span>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

</div>
@endsection
