@extends('layouts.app')

@section('title', 'Koleksi Proyek Tersimpan — NampungYuk')

@section('content')
<div class="space-y-6">

    <!-- Header Banner (Vercel / Supabase Style) -->
    <div class="spotlight-card p-5 sm:p-6 flex items-center justify-between gap-4 border border-neutral-200/80 dark:border-white/[0.08] bg-white dark:bg-[#09090b]">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-orange-500/10 text-orange-500 border border-orange-500/20 flex items-center justify-center">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                </svg>
            </div>
            <div>
                <h1 class="text-base sm:text-lg font-extrabold text-neutral-900 dark:text-white">
                    Koleksi Proyek Tersimpan
                </h1>
                <p class="text-xs text-neutral-500 dark:text-neutral-400">
                    Daftar project codingan pilihan yang kamu simpan untuk inspirasi dan referensi arsitektur.
                </p>
            </div>
        </div>

        <a href="{{ route('projects.index') }}" 
           class="text-xs font-mono font-semibold text-neutral-500 hover:text-orange-500 transition hidden sm:inline-flex items-center gap-1">
            <span>Ke Feed</span>
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
            </svg>
        </a>
    </div>

    <!-- Single Column Bookmarks List (Spotlight Cards) -->
    <div class="space-y-4">
        @forelse($projects as $project)
            <article class="project-nav-card spotlight-card p-5 sm:p-6 space-y-4 group transition duration-200 border border-neutral-200/80 dark:border-white/[0.08] bg-white dark:bg-[#09090b]"
                     x-data="{
                         score: {{ $project->score }},
                         isBookmarked: true,
                         async toggleBookmark() {
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
                                     this.isBookmarked = data.bookmarked;
                                     notify(this.isBookmarked ? 'Disimpan kembali ke Koleksi.' : 'Dihapus dari Koleksi.');
                                     if (!this.isBookmarked) {
                                         setTimeout(() => location.reload(), 500);
                                     }
                                 }
                             } catch(e) {
                                 notify('Gagal mengubah status bookmark');
                             }
                         }
                     }">
                
                <!-- Top Row: Author & Category -->
                <div class="flex items-center justify-between gap-2 text-xs">
                    <div class="flex items-center gap-2.5 min-w-0">
                        @if($project->user)
                            <a href="{{ route('profile.show', $project->user->username) }}" class="shrink-0 hover:opacity-85 transition">
                                <img src="{{ $project->user->avatar ?: 'https://api.dicebear.com/7.x/bottts/svg?seed='.urlencode($project->user->name) }}" 
                                     alt="{{ $project->user->name }}" 
                                     class="w-8 h-8 rounded-xl bg-neutral-100 dark:bg-[#1a1a1e] object-cover ring-1 ring-neutral-200 dark:ring-white/10 shadow-2xs">
                            </a>
                            <div class="min-w-0 flex items-center gap-1.5 flex-wrap">
                                <a href="{{ route('profile.show', $project->user->username) }}" class="font-bold text-neutral-900 dark:text-white hover:text-orange-500 transition">
                                    {{ $project->user->name }}
                                </a>
                                <span class="text-neutral-400 font-mono text-[11px]">&#64;{{ $project->user->username }}</span>
                            </div>
                        @else
                            <span class="font-bold text-neutral-500">Anonymous Dev</span>
                        @endif
                    </div>

                    <span class="text-[10px] font-mono px-2.5 py-1 rounded-lg bg-neutral-100 dark:bg-white/[0.04] text-neutral-600 dark:text-neutral-400 border border-neutral-200 dark:border-white/[0.08]">
                        {{ $project->category->name }}
                    </span>
                </div>

                <!-- Title & Tagline -->
                <div class="space-y-1.5">
                    <h2 class="text-lg sm:text-xl font-bold text-neutral-900 dark:text-white leading-snug group-hover:text-orange-500 transition">
                        <a href="{{ route('projects.show', $project->slug) }}" class="card-detail-link">
                            {{ $project->title }}
                        </a>
                    </h2>
                    <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
                        {{ $project->tagline }}
                    </p>
                </div>

                <!-- Thumbnail -->
                @if($project->thumbnail)
                    <div class="overflow-hidden rounded-xl border border-neutral-200/80 dark:border-white/[0.08] bg-neutral-950 relative">
                        <a href="{{ route('projects.show', $project->slug) }}">
                            <img src="{{ $project->thumbnail }}" 
                                 alt="{{ $project->title }}" 
                                 class="w-full max-h-[380px] object-cover group-hover:scale-[1.02] transition duration-500">
                        </a>
                    </div>
                @endif

                <!-- Tech Stacks -->
                @if(is_array($project->tech_stacks) && count($project->tech_stacks) > 0)
                    <div class="flex flex-wrap gap-1.5 font-mono pt-1">
                        @foreach(array_slice($project->tech_stacks, 0, 4) as $tech)
                            <span class="linear-pill">#{{ $tech }}</span>
                        @endforeach
                    </div>
                @endif

                <!-- Action Bar -->
                <div class="flex items-center justify-between gap-2 pt-3 border-t border-neutral-100 dark:border-white/[0.06]">
                    <div class="flex items-center gap-2 text-xs">
                        <span class="inline-flex items-center gap-1 font-mono font-bold text-orange-500">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M3.293 9.707a1 1 0 010-1.414l6-6a1 1 0 011.414 0l6 6a1 1 0 01-1.414 1.414L11 5.414V17a1 1 0 11-2 0V5.414L4.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                            </svg>
                            <span>{{ $project->score }}</span>
                        </span>
                        <a href="{{ route('projects.show', $project->slug) }}#komentar" class="text-neutral-400 hover:text-neutral-200 font-mono text-[11px]">
                            {{ $project->comments_count }} review
                        </a>
                    </div>

                    <div class="flex items-center gap-2">
                        @if($project->demo_url)
                            <a href="{{ $project->demo_url }}" target="_blank" rel="noopener noreferrer"
                               class="inline-flex items-center gap-1 text-xs font-mono font-semibold text-emerald-500 hover:underline">
                                <span>Demo</span>
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                </svg>
                            </a>
                        @endif

                        <button @click="toggleBookmark()" 
                                class="inline-flex items-center gap-1 text-xs text-rose-500 hover:text-rose-400 transition font-mono px-3 py-1 rounded-lg bg-rose-500/10 border border-rose-500/20">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            <span>Hapus</span>
                        </button>
                    </div>
                </div>

            </article>
        @empty
            <div class="spotlight-card p-12 text-center space-y-3 border border-neutral-200/80 dark:border-white/[0.08] bg-white dark:bg-[#09090b]">
                <svg class="w-12 h-12 text-neutral-400 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                </svg>
                <h3 class="font-bold text-neutral-900 dark:text-white text-base">Belum ada proyek tersimpan</h3>
                <p class="text-xs text-neutral-500">Klik ikon bookmark pada kartu proyek mana pun di feed untuk menyimpannya ke koleksi ini.</p>
                <a href="{{ route('projects.index') }}" class="btn-vercel-primary inline-block mt-2 px-5 py-2.5 text-xs font-semibold transition">
                    Jelajahi Feed Kodingan
                </a>
            </div>
        @endforelse
    </div>

    <!-- PAGINATION -->
    <div class="pt-4">
        {{ $projects->links() }}
    </div>

</div>
@endsection
