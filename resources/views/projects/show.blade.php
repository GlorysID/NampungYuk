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
           class="inline-flex items-center gap-1.5 text-[#5c6979] dark:text-[#7e8a9a] hover:text-[#0e9c8b] dark:hover:text-[#50d2c1] transition font-medium">
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
                <span class="text-[#5c6979] dark:text-[#7e8a9a]">Status Proyek:</span>
                <x-badge :variant="$variant" size="sm">
                    {{ $project->getStatusLabel() }}
                </x-badge>
            </div>
        @endif
    </div>

    <!-- MAIN PROJECT ARTICLE CONTAINER -->
    <article class="ny-card bg-white dark:bg-[#141821] overflow-hidden border border-[#d5dbe2] dark:border-[#262d3a]">
        
        <!-- Header Info Area -->
        <div class="p-6 sm:p-8 border-b border-[#d5dbe2] dark:border-[#262d3a] space-y-5">
            
            <!-- Creator & Category Row -->
            <div class="flex items-center justify-between gap-4 flex-wrap">
                <div class="flex items-center gap-3">
                    @if($project->user)
                        <a href="{{ route('profile.show', $project->user->username) }}" class="shrink-0 hover:opacity-85 transition">
                            <x-user-avatar :user="$project->user" size="lg" />
                        </a>
                        <div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <a href="{{ route('profile.show', $project->user->username) }}" class="font-bold text-[#10161f] dark:text-[#eaecf0] text-sm sm:text-base hover:text-[#0e9c8b] dark:hover:text-[#50d2c1] transition">
                                    {{ $project->user->name }}
                                </a>
                                <span class="text-[#5c6979] dark:text-[#7e8a9a] text-xs font-mono">&#64;{{ $project->user->username }}</span>
                            </div>
                            <div class="flex items-center gap-2 text-xs text-[#5c6979] dark:text-[#7e8a9a] mt-0.5">
                                <span>Dipamerkan {{ $project->created_at->translatedFormat('d F Y') }}</span>
                                <span>&bull;</span>
                                <span>{{ $project->views_count }} tayangan</span>
                            </div>
                        </div>
                    @else
                        <x-user-avatar name="Anonim" size="lg" />
                        <div>
                            <p class="font-bold text-[#10161f] dark:text-[#eaecf0] text-sm sm:text-base">Developer Anonim</p>
                            <span class="text-xs text-[#5c6979]">{{ $project->created_at->translatedFormat('d F Y') }}</span>
                        </div>
                    @endif
                </div>

                <!-- Category Pill -->
                @if($project->category)
                    <a href="{{ route('projects.index', ['kategori' => $project->category->slug]) }}" 
                       class="text-xs font-mono font-medium px-3 py-1 rounded-lg bg-[#e6eaee] dark:bg-[#1e2530] text-[#10161f] dark:text-[#eaecf0] hover:text-[#0e9c8b] dark:hover:text-[#50d2c1] border border-[#d5dbe2] dark:border-[#262d3a] transition">
                        {{ $project->category->name }}
                    </a>
                @endif
            </div>

            <!-- Title & Tagline -->
            <div class="space-y-2">
                <h1 class="text-xl sm:text-3xl font-extrabold text-[#10161f] dark:text-[#eaecf0] tracking-tight leading-snug">
                    {{ $project->title }}
                </h1>
                <p class="text-sm sm:text-base text-[#5c6979] dark:text-[#7e8a9a] leading-relaxed max-w-3xl">
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

                <!-- Voting controls in header -->
                <div class="inline-flex items-center rounded-lg bg-[#e6eaee] dark:bg-[#1e2530] p-0.5 border border-[#d5dbe2] dark:border-[#262d3a]">
                    <button @click="vote('up')"
                            type="button"
                            :disabled="isVoting"
                            :aria-pressed="userVote === 'up'"
                            aria-label="Upvote project ini"
                            :class="{
                                'bg-[#0e9c8b] text-white ': userVote === 'up',
                                'text-[#5c6979] dark:text-[#7e8a9a] hover:text-[#0e9c8b] dark:hover:text-[#50d2c1]': userVote !== 'up'
                            }"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md text-xs font-semibold transition active:scale-95 disabled:opacity-60">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7"/>
                        </svg>
                        <span class="font-mono text-xs" x-text="score">{{ $project->score }}</span>
                    </button>

                    <span class="h-3.5 w-px bg-[#d5dbe2] dark:bg-[#262d3a] mx-0.5" aria-hidden="true"></span>

                    <button @click="vote('down')"
                            type="button"
                            :disabled="isVoting"
                            :aria-pressed="userVote === 'down'"
                            aria-label="Downvote project ini"
                            :class="{
                                'bg-rose-600 text-white ': userVote === 'down',
                                'text-[#5c6979] dark:text-[#7e8a9a] hover:text-rose-600': userVote !== 'down'
                            }"
                            class="p-1.5 rounded-md transition active:scale-95 disabled:opacity-60">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                </div>

                <!-- Bookmark Button -->
                <button @click="toggleBookmark()"
                        type="button"
                        :disabled="isBookmarking"
                        :aria-pressed="isBookmarked"
                        aria-label="Simpan project ke koleksi"
                        :class="{
                            'text-[#0e9c8b] dark:text-[#50d2c1] bg-[#d9fbf4] dark:bg-[#50d2c1]/12 border-[#50d2c1]/30 dark:border-[#50d2c1]/30': isBookmarked,
                            'text-[#5c6979] dark:text-[#7e8a9a] hover:text-[#10161f] dark:hover:text-white border-[#d5dbe2] dark:border-[#262d3a] bg-white dark:bg-[#141821]': !isBookmarked
                        }"
                        class="p-2 rounded-lg border transition active:scale-95 disabled:opacity-60"
                        title="Simpan ke Koleksi">
                    <svg class="w-4 h-4" :fill="isBookmarked ? 'currentColor' : 'none'" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                    </svg>
                </button>

                <!-- Share Button -->
                <button @click="copyLink()"
                        type="button"
                        aria-label="Salin tautan project"
                        class="p-2 rounded-lg border border-[#d5dbe2] dark:border-[#262d3a] bg-white dark:bg-[#141821] text-[#5c6979] dark:text-[#7e8a9a] hover:text-[#10161f] dark:hover:text-white transition active:scale-95"
                        title="Salin Tautan">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
                    </svg>
                </button>
            </div>

        </div>

        <!-- Visual Preview Banner -->
        @if($project->thumbnail)
            <div class="bg-[#0b0e11] border-b border-[#d5dbe2] dark:border-[#262d3a] overflow-hidden max-h-[500px] flex items-center justify-center">
                <img src="{{ $project->thumbnail }}" 
                     alt="{{ $project->title }}" 
                     loading="lazy" 
                     decoding="async"
                     onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1555066931-4365d14bab8c?auto=format&fit=crop&w=1200&q=80';"
                     class="w-full max-h-[500px] object-cover sm:object-contain mx-auto">
            </div>
        @endif

        <!-- ARTICLE BODY CONTENT -->
        <div class="p-6 sm:p-8 space-y-8">
            
            <!-- SECTION 1: About This Project (Description) -->
            <section class="space-y-3">
                <h2 class="text-sm font-bold uppercase tracking-wider text-[#5c6979] dark:text-[#7e8a9a] font-mono flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#0e9c8b] dark:bg-teal-400"></span>
                    <span>Tentang Project Ini</span>
                </h2>
                
                @if($project->description)
                    <div class="text-xs sm:text-sm text-[#10161f] dark:text-[#eaecf0] leading-relaxed whitespace-pre-line space-y-3">
                        {{ $project->description }}
                    </div>
                @else
                    <p class="text-xs text-[#5c6979] italic">
                        Creator belum menyertakan deskripsi panjang untuk project ini.
                    </p>
                @endif
            </section>

            <!-- SECTION 2: Tech Stack -->
            @if(is_array($project->tech_stacks) && count($project->tech_stacks) > 0)
                <section class="space-y-3 pt-6 border-t border-[#d5dbe2]/80 dark:border-[#262d3a]">
                    <h2 class="text-sm font-bold uppercase tracking-wider text-[#5c6979] dark:text-[#7e8a9a] font-mono flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#0e9c8b] dark:bg-teal-400"></span>
                        <span>Teknologi & Tools yang Digunakan</span>
                    </h2>
                    
                    <div class="flex flex-wrap gap-2">
                        @foreach($project->tech_stacks as $tech)
                            <x-tech-pill :name="$tech" />
                        @endforeach
                    </div>
                </section>
            @endif

            <!-- SECTION 3: Knowledge Sharing — What I Learned -->
            @if(!empty(trim((string)$project->learnings)))
                <section class="space-y-3 pt-6 border-t border-[#d5dbe2]/80 dark:border-[#262d3a]">
                    <h2 class="text-sm font-bold uppercase tracking-wider text-[#5c6979] dark:text-[#7e8a9a] font-mono flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        <span>Apa yang Saya Pelajari (Learnings)</span>
                    </h2>
                    
                    <div class="p-4 rounded-xl bg-emerald-50/40 dark:bg-emerald-950/20 border border-emerald-200/60 dark:border-emerald-800/40 text-xs sm:text-sm text-[#10161f] dark:text-[#eaecf0] leading-relaxed whitespace-pre-line">
                        {{ $project->learnings }}
                    </div>
                </section>
            @endif

            <!-- SECTION 4: Knowledge Sharing — Challenges -->
            @if(!empty(trim((string)$project->challenges)))
                <section class="space-y-3 pt-6 border-t border-[#d5dbe2]/80 dark:border-[#262d3a]">
                    <h2 class="text-sm font-bold uppercase tracking-wider text-[#5c6979] dark:text-[#7e8a9a] font-mono flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                        <span>Tantangan yang Dihadapi (Challenges)</span>
                    </h2>
                    
                    <div class="p-4 rounded-xl bg-amber-50/40 dark:bg-amber-950/20 border border-amber-200/60 dark:border-amber-800/40 text-xs sm:text-sm text-[#10161f] dark:text-[#eaecf0] leading-relaxed whitespace-pre-line">
                        {{ $project->challenges }}
                    </div>
                </section>
            @endif

            <!-- SECTION 5: How To Run (Truthful Setup Instructions Only) -->
            @if($project->hasSetupInstructions())
                <section class="space-y-3 pt-6 border-t border-[#d5dbe2]/80 dark:border-[#262d3a]">
                    <div class="flex items-center justify-between">
                        <h2 class="text-sm font-bold uppercase tracking-wider text-[#5c6979] dark:text-[#7e8a9a] font-mono flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#0e9c8b] dark:bg-teal-400"></span>
                            <span>Cara Menjalankan Project (Setup Instructions)</span>
                        </h2>

                        <button @click="navigator.clipboard.writeText(`{{ addslashes($project->setup_instructions) }}`); if(window.notify) notify('Petunjuk instalasi disalin!');"
                                type="button"
                                class="text-xs font-mono text-[#0e9c8b] dark:text-[#50d2c1] hover:underline inline-flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                            </svg>
                            <span>Salin Instruksi</span>
                        </button>
                    </div>
                    
                    <pre class="p-4 rounded-xl bg-[#0b0e11] text-[#50d2c1] font-mono text-xs overflow-x-auto leading-relaxed border border-[#262d3a]"><code>{{ $project->setup_instructions }}</code></pre>
                </section>
            @endif

        </div>

    </article>

    <!-- SECTION 6: Comments & Discussion -->
    <section id="komentar" class="ny-card p-6 sm:p-8 space-y-6 bg-white dark:bg-[#141821] border border-[#d5dbe2] dark:border-[#262d3a]">
        <div class="flex items-center justify-between">
            <h2 class="text-base sm:text-lg font-bold text-[#10161f] dark:text-[#eaecf0] flex items-center gap-2">
                <span>Diskusi & Review Teknis</span>
                <span class="text-xs font-mono font-normal px-2 py-0.5 rounded-md bg-[#e6eaee] dark:bg-[#1e2530] text-[#5c6979] dark:text-[#7e8a9a]">
                    {{ $project->comments_count }}
                </span>
            </h2>
        </div>

        <!-- Add Comment Form -->
        @auth
            <form action="{{ route('projects.comment', $project) }}" method="POST" class="space-y-3">
                @csrf
                <div class="space-y-1.5">
                    <label for="comment-content" class="text-xs font-semibold text-[#10161f] dark:text-[#eaecf0]">
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
            <div class="p-4 rounded-xl bg-[#eceff1] dark:bg-[#0b0e11] border border-[#d5dbe2] dark:border-[#262d3a] flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 text-xs">
                <div class="space-y-0.5">
                    <p class="font-semibold text-[#10161f] dark:text-[#eaecf0]">Ingin memberikan masukan atau pertanyaan?</p>
                    <p class="text-[#5c6979] dark:text-[#7e8a9a]">Silakan masuk dengan akun kamu untuk bergabung dalam diskusi teknis ini.</p>
                </div>
                <x-button :href="route('login')" variant="primary" size="sm" class="shrink-0">
                    Masuk untuk Berkomentar
                </x-button>
            </div>
        @endauth

        <!-- Comments List -->
        <div class="space-y-3 pt-2">
            @forelse($project->comments as $comment)
                <x-comment-card :comment="$comment" />
            @empty
                <p class="text-xs text-[#5c6979] dark:text-[#7e8a9a] text-center py-6">
                    Belum ada diskusi untuk project ini. Jadilah yang pertama memberikan masukan!
                </p>
            @endforelse
        </div>
    </section>

    <!-- SECTION 7: Related Projects in Same Category -->
    @if(isset($relatedProjects) && count($relatedProjects) > 0)
        <section class="space-y-4 pt-4">
            <h2 class="text-sm font-bold uppercase tracking-wider text-[#5c6979] dark:text-[#7e8a9a] font-mono">
                Project Terkait di Kategori Ini
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @foreach($relatedProjects as $rel)
                    <div class="ny-card p-4 space-y-2 bg-white dark:bg-[#141821] flex flex-col justify-between">
                        <div class="space-y-1.5">
                            <span class="text-[11px] font-mono text-[#0e9c8b] dark:text-[#50d2c1] font-semibold">
                                {{ $rel->category->name }}
                            </span>
                            <h3 class="font-bold text-sm text-[#10161f] dark:text-[#eaecf0] hover:text-[#0e9c8b] transition">
                                <a href="{{ route('projects.show', $rel->slug) }}">
                                    {{ $rel->title }}
                                </a>
                            </h3>
                            <p class="text-xs text-[#5c6979] dark:text-[#7e8a9a] line-clamp-2">
                                {{ $rel->tagline }}
                            </p>
                        </div>

                        <div class="flex items-center justify-between text-[11px] pt-3 border-t border-[#d5dbe2]/60 dark:border-[#262d3a]/60">
                            <span class="font-medium text-[#10161f] dark:text-[#eaecf0]">
                                {{ $rel->user?->name ?? 'Developer' }}
                            </span>
                            <span class="font-mono text-[#0e9c8b] font-semibold">
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
