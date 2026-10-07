@props([
    'project',
    'initialVote' => null,
    'isBookmarked' => false,
])

<article class="project-nav-card ny-card p-4 sm:p-5 space-y-3.5 group bg-white dark:bg-[#141821] border border-[#d5dbe2] dark:border-[#262d3a] transition duration-150"
         x-data="{
             score: {{ $project->score }},
             userVote: '{{ $initialVote }}',
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

    <!-- 1. Visual Preview Banner (Thumbnail) -->
    @if($project->thumbnail)
        <div class="relative overflow-hidden rounded-xl bg-[#e6eaee] dark:bg-[#1e2530] border border-[#d5dbe2]/60 dark:border-[#262d3a] aspect-video max-h-[280px]">
            <a href="{{ route('projects.show', $project->slug) }}" class="block w-full h-full" tabindex="-1" aria-hidden="true">
                <img src="{{ $project->thumbnail }}" 
                     alt="{{ $project->title }}" 
                     loading="lazy" 
                     decoding="async"
                     onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1555066931-4365d14bab8c?auto=format&fit=crop&w=1200&q=80';"
                     class="w-full h-full object-cover group-hover:scale-[1.01] transition duration-300">
            </a>

            @if($project->demo_url)
                <a href="{{ $project->demo_url }}" target="_blank" rel="noopener noreferrer"
                   class="absolute top-3 right-3 inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-mono font-medium bg-white/95 dark:bg-[#141821]/95 text-[#0e9c8b] dark:text-[#50d2c1] border border-[#d5dbe2] dark:border-[#262d3a] hover:bg-white transition"
                   title="Buka Live Demo">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    <span>Live Demo</span>
                    <svg class="w-3 h-3 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                    </svg>
                </a>
            @elseif($project->prototype_url)
                <a href="{{ $project->prototype_url }}" target="_blank" rel="noopener noreferrer"
                   class="absolute top-3 right-3 inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-mono font-medium bg-white/95 dark:bg-[#141821]/95 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-800 hover:bg-white transition"
                   title="Buka Prototipe Interaktif">
                    <span class="w-1.5 h-1.5 rounded-full bg-purple-500"></span>
                    <span>Prototipe</span>
                </a>
            @endif
        </div>
    @endif

    <!-- 2. Creator & Category Metadata -->
    <x-project-meta :project="$project" />

    <!-- 3. Project Title & Tagline -->
    <div class="space-y-1">
        <h2 class="text-base sm:text-lg font-bold text-[#10161f] dark:text-[#eaecf0] leading-snug group-hover:text-[#0e9c8b] dark:group-hover:text-[#50d2c1] transition">
            <a href="{{ route('projects.show', $project->slug) }}" class="card-detail-link focus-visible:rounded">
                {{ $project->title }}
            </a>
        </h2>

        <p class="text-xs sm:text-sm text-[#5c6979] dark:text-[#7e8a9a] leading-relaxed line-clamp-2">
            {{ $project->tagline }}
        </p>
    </div>

    <!-- 4. Tech Stacks Chips -->
    @if(is_array($project->tech_stacks) && count($project->tech_stacks) > 0)
        <div class="flex flex-wrap gap-1.5 pt-0.5">
            @foreach(array_slice($project->tech_stacks, 0, 5) as $tech)
                <x-tech-pill :name="$tech" />
            @endforeach
            @if(count($project->tech_stacks) > 5)
                <span class="text-[11px] font-mono text-[#5c6979] dark:text-[#7e8a9a] self-center">
                    +{{ count($project->tech_stacks) - 5 }}
                </span>
            @endif
        </div>
    @endif

    <!-- 5. Action Bar (Voting, Comments, View Count, Bookmark, Share) -->
    <x-project-actions :project="$project" :initial-vote="$initialVote" :is-bookmarked="$isBookmarked" />

</article>
