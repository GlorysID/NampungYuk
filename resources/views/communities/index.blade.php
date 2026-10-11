@extends('layouts.app')

@section('title', 'Komunitas Developer — NampungYuk')
@section('meta_description', 'Temukan komunitas developer sesuai minatmu. Berbagi ilmu, tanya jawab coding, dan kolaborasi project.')

@section('content')
<div class="space-y-6" x-data="{ 
    liveStageModal: false,
    audioPlaying: false,
    toggleStageAudio() {
        this.audioPlaying = !this.audioPlaying;
        if (window.notify) notify(this.audioPlaying ? 'Mendengarkan Live Stage Komunitas...' : 'Audio Live Stage dijeda.');
    }
}">

    <!-- Page Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3">
        <div>
            <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-[#0070f3]/10 text-[#0070f3] dark:text-[#3291ff] text-[11px] font-semibold mb-1">
                <span>💬 Forum & Diskusi Developer</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-black text-[#18181b] dark:text-[#fafafa] tracking-tight">Komunitas</h1>
            <p class="text-xs text-[#63636b] dark:text-[#a0a0a0] mt-0.5">Tanya jawab coding, diskusikan tantangan arsitektur, dan terhubung dengan sesama developer.</p>
        </div>
        
        <div class="flex items-center gap-2 shrink-0">
            @auth
                <button type="button" @click="$refs.createModal.showModal()" class="btn-primary text-xs py-2 px-4 inline-flex items-center gap-1.5 cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Buat Komunitas</span>
                </button>
            @else
                <button type="button" @click="window.openAuthModal('login')" class="btn-primary text-xs py-2 px-4 inline-flex items-center gap-1.5 cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Buat Komunitas</span>
                </button>
            @endauth
        </div>
    </div>

    <!-- VIEW MODE SWITCHER & FILTER TABS (Hybrid 3B) -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 pb-1 border-b border-[#e4e4e7] dark:border-[#1f1f1f]">
        
        <!-- View Toggle: Linimasa Diskusi vs Katalog Komunitas -->
        <div class="inline-flex items-center p-1 rounded-xl bg-[#e9e9ec] dark:bg-[#121214] border border-[#e4e4e7] dark:border-[#27272a] text-xs">
            <a href="{{ route('communities.index', array_filter(['view' => 'feed', 'tab' => $tab, 'q' => $search])) }}"
               class="px-3.5 py-1.5 rounded-lg font-semibold transition inline-flex items-center gap-1.5 {{ $view !== 'direktori' ? 'bg-white dark:bg-[#27272a] text-[#18181b] dark:text-white shadow-sm' : 'text-[#71717a] hover:text-[#18181b] dark:hover:text-white' }}">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                <span>Linimasa Diskusi</span>
            </a>
            <a href="{{ route('communities.index', array_filter(['view' => 'direktori', 'tab' => $tab, 'q' => $search])) }}"
               class="px-3.5 py-1.5 rounded-lg font-semibold transition inline-flex items-center gap-1.5 {{ $view === 'direktori' ? 'bg-white dark:bg-[#27272a] text-[#18181b] dark:text-white shadow-sm' : 'text-[#71717a] hover:text-[#18181b] dark:hover:text-white' }}">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                <span>Direktori Komunitas</span>
            </a>
        </div>

        <!-- Pill filter tabs: Untukmu, Diikuti, Populer, Baru -->
        <div class="inline-flex items-center gap-1.5 overflow-x-auto max-w-full">
            @foreach(['untukmu' => 'Untukmu', 'diikuti' => 'Diikuti', 'populer' => 'Populer', 'baru' => 'Baru'] as $key => $label)
                <a href="{{ route('communities.index', array_filter(['view' => $view, 'tab' => $key, 'q' => $search])) }}"
                   class="shrink-0 inline-flex items-center px-3 py-1.5 rounded-full text-xs font-medium transition {{ $tab === $key ? 'bg-[#0070f3] text-white dark:bg-[#3291ff] dark:text-[#000000] font-bold shadow-sm' : 'text-[#63636b] dark:text-[#a0a0a0] hover:bg-[#f5f5f5] dark:hover:bg-[#18181b]' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>

    </div>

    <!-- MAIN TWO-COLUMN WORKSPACE -->
    <div class="grid grid-cols-1 xl:grid-cols-12 gap-6 items-start">

        <!-- LEFT/MAIN COLUMN (xl:col-span-8) -->
        <div class="xl:col-span-8 space-y-4">

            <!-- Search box -->
            <form method="GET" action="{{ route('communities.index') }}" class="relative w-full">
                @if($tab) <input type="hidden" name="tab" value="{{ $tab }}"> @endif
                @if($view) <input type="hidden" name="view" value="{{ $view }}"> @endif
                <svg class="w-4 h-4 text-[#71717a] absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" name="q" value="{{ $search }}" placeholder="{{ $view === 'direktori' ? 'Cari nama komunitas atau topik...' : 'Cari pertanyaan atau topik diskusi developer...' }}" class="ny-input text-xs sm:text-sm !pl-10">
            </form>

            <!-- ========================================== -->
            <!-- LIVE STAGE / COMMUNITY SPACE WIDGET (UI)  -->
            <!-- (Audio & Video Stage placed in Community) -->
            <!-- ========================================== -->
            <div class="ny-card p-4 sm:p-5 bg-gradient-to-r from-[#0070f3]/10 via-[#000000] to-[#7928ca]/10 border border-[#0070f3]/30 dark:border-[#3291ff]/30 shadow-lg relative overflow-hidden group">
                <!-- Background ambient glow -->
                <div class="absolute -right-10 -top-10 w-48 h-48 rounded-full bg-[#0070f3]/15 blur-3xl pointer-events-none"></div>

                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 relative z-10">
                    <div class="space-y-1.5 min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-rose-500/15 border border-rose-500/30 text-rose-500 dark:text-rose-400 text-[10px] font-bold uppercase tracking-wider">
                                <span class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></span>
                                <span>Live Stage Komunitas</span>
                            </span>
                            <span class="text-[11px] text-[#71717a] font-mono">Web Dev Indonesia</span>
                        </div>
                        
                        <h3 class="text-sm sm:text-base font-bold text-white tracking-tight flex items-center gap-2">
                            <span>Bedah Portofolio & Tips Magang Frontend</span>
                        </h3>
                        
                        <p class="text-[11px] text-[#a1a1aa] flex items-center gap-2">
                            <span>Host: <strong class="text-white">@alexdev</strong></span>
                            <span>&bull;</span>
                            <span class="inline-flex items-center gap-1 text-emerald-400">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"/></svg>
                                <span>24 Pendengar Langsung</span>
                            </span>
                        </p>
                    </div>

                    <!-- Stage CTA & Sound Wave Indicator -->
                    <div class="flex items-center gap-3 shrink-0">
                        <!-- Sound wave mini animation -->
                        <div class="hidden sm:flex items-center gap-0.5 h-5 px-2 bg-white/5 rounded-lg border border-white/10">
                            <span class="w-0.5 bg-[#3291ff] rounded-full animate-bounce h-3"></span>
                            <span class="w-0.5 bg-[#3291ff] rounded-full animate-bounce [animation-delay:0.2s] h-4"></span>
                            <span class="w-0.5 bg-[#3291ff] rounded-full animate-bounce [animation-delay:0.4s] h-2"></span>
                            <span class="w-0.5 bg-[#3291ff] rounded-full animate-bounce [animation-delay:0.1s] h-5"></span>
                        </div>

                        <button type="button" 
                                @click="liveStageModal = true"
                                class="btn-primary py-2 px-3.5 text-xs font-bold inline-flex items-center gap-1.5 shadow-md active:scale-95 transition cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Dengarkan Stage</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- VIEW 1: LINIMASA DISKUSI (DEFAULT FEED 3B) -->
            @if($view !== 'direktori')
                <div class="space-y-4">
                    
                    @forelse($posts as $post)
                        <x-community-post 
                            :post="$post" 
                            :initial-vote="$userVotes[$post->id] ?? null" 
                            :can-comment="true" 
                            :can-moderate="false" 
                            :show-community="true" />
                    @empty
                        <x-empty-state 
                            title="Belum ada diskusi di kategori ini" 
                            description="Jadilah yang pertama membuka obrolan atau pertanyaan di komunitas developer!"
                            action-label="Lihat Semua Komunitas"
                            :action-url="route('communities.index', ['view' => 'direktori'])" />
                    @endforelse

                    @if($posts->hasPages())
                        <div class="pt-4">{{ $posts->links() }}</div>
                    @endif

                    @guest
                        <!-- Soft Auth Prompt inside Feed -->
                        <div class="ny-card p-6 border border-[#27272a] bg-[#09090b] text-center space-y-3">
                            <h4 class="text-sm font-bold text-white">Ingin ikut berdiskusi atau bertanya ke sesama developer?</h4>
                            <p class="text-xs text-[#a1a1aa] max-w-md mx-auto">
                                Masuk ke akun NampungYuk untuk membuat postingan, memberikan upvote, atau bergabung ke komunitas favoritmu.
                            </p>
                            <div class="pt-1 flex items-center justify-center gap-2">
                                <button type="button" @click="window.openAuthModal('login')" class="px-4 py-2 rounded-full border border-[#3f3f46] text-xs font-semibold text-white hover:bg-white/5 transition cursor-pointer">
                                    Masuk ke Akun
                                </button>
                                <button type="button" @click="window.openAuthModal('register')" class="px-4 py-2 rounded-full bg-white text-black font-bold text-xs hover:bg-[#e4e4e7] transition cursor-pointer">
                                    Daftar Gratis
                                </button>
                            </div>
                        </div>
                    @endguest

                </div>

            <!-- VIEW 2: DIREKTORI KATALOG KOMUNITAS -->
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @forelse($communities as $community)
                        @php $joined = in_array($community->id, $joinedIds); @endphp
                        <div class="ny-card p-4 flex flex-col gap-3">
                            <div class="flex items-start gap-3">
                                @if($community->icon)
                                    <img src="{{ $community->icon }}" alt="" class="w-12 h-12 rounded-xl object-cover shrink-0">
                                @else
                                    <div class="w-12 h-12 rounded-xl ny-gradient-bg text-white flex items-center justify-center font-bold text-lg shrink-0">{{ mb_substr($community->name, 0, 1) }}</div>
                                @endif
                                <div class="min-w-0 flex-1">
                                    <a href="{{ route('communities.show', $community->slug) }}" class="font-bold text-sm text-[#18181b] dark:text-[#fafafa] hover:text-[#0070f3] dark:hover:text-[#3291ff] transition truncate block">{{ $community->name }}</a>
                                    <div class="flex items-center gap-1.5 mt-0.5 flex-wrap">
                                        @if($community->topic)
                                            <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-[#e6f0ff] dark:bg-[#3291ff]/12 text-[#0070f3] dark:text-[#47a8ff] border border-[#3291ff]/25">{{ $community->topic }}</span>
                                        @endif
                                        @if($community->isPrivate())
                                            <span class="text-[10px] font-bold uppercase text-amber-600 dark:text-amber-400">Private</span>
                                        @else
                                            <span class="text-[10px] font-bold uppercase text-emerald-600 dark:text-emerald-400">Publik</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <p class="text-xs text-[#63636b] dark:text-[#a0a0a0] leading-relaxed line-clamp-3 min-h-[3rem]">
                                {{ $community->description ?: 'Belum ada deskripsi komunitas.' }}
                            </p>

                            <div class="flex items-center gap-3 text-[11px] text-[#63636b] dark:text-[#a0a0a0] font-mono">
                                <span class="inline-flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    {{ number_format($community->members_count) }}
                                </span>
                                <span class="inline-flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                    {{ number_format($community->posts_count) }}
                                </span>
                            </div>

                            <div class="flex items-center gap-2 mt-auto">
                                @auth
                                    <button type="button" x-data="communityJoin({ joined: {{ $joined ? 'true' : 'false' }}, url: '{{ route('communities.join', $community) }}' })"
                                            @click="toggle()" :disabled="loading"
                                            :class="joined ? 'bg-[#eeeeef] dark:bg-[#171717] text-[#18181b] dark:text-[#fafafa]' : 'bg-[#0070f3] text-white dark:bg-[#3291ff] dark:text-[#000000]'"
                                            class="flex-1 text-xs font-semibold py-2 rounded-lg transition disabled:opacity-60 cursor-pointer"
                                            x-text="joined ? 'Bergabung' : 'Gabung'"></button>
                                @else
                                    <button type="button" @click="window.openAuthModal('login')" class="flex-1 text-xs font-semibold py-2 rounded-lg bg-[#0070f3] text-white dark:bg-[#3291ff] dark:text-[#000000] cursor-pointer">
                                        Gabung
                                    </button>
                                @endauth
                                <a href="{{ route('communities.show', $community->slug) }}" class="btn-secondary text-xs py-2 px-3 {{ auth()->check() ? '' : 'flex-1 text-center' }}">Lihat Komunitas</a>
                            </div>
                        </div>
                    @empty
                        <div class="sm:col-span-2">
                            <x-empty-state title="Belum ada komunitas" description="Jadilah yang pertama membuat komunitas developer!" />
                        </div>
                    @endforelse
                </div>

                @if($communities->hasPages())
                    <div class="pt-4">{{ $communities->links() }}</div>
                @endif
            @endif

        </div>

        <!-- RIGHT SIDEBAR (Top Communities & Followed) -->
        <aside class="xl:col-span-4 space-y-4 xl:sticky xl:top-20">
            
            <!-- Top Communities List -->
            <div class="ny-card p-4 space-y-3">
                <div class="flex items-center justify-between">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-[#63636b] dark:text-[#a0a0a0] flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-[#0070f3] dark:text-[#3291ff]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                        <span>Top Komunitas</span>
                    </h3>
                    <a href="{{ route('communities.index', ['view' => 'direktori']) }}" class="text-[11px] font-semibold text-[#0070f3] dark:text-[#3291ff] hover:underline">
                        Lihat Semua
                    </a>
                </div>

                <div class="space-y-2">
                    @foreach($topCommunities as $i => $c)
                        <a href="{{ route('communities.show', $c->slug) }}" class="flex items-center gap-2.5 p-2 rounded-xl hover:bg-[#f7f7f8] dark:hover:bg-[#111111] transition group">
                            <span class="text-xs font-mono font-bold text-[#63636b] dark:text-[#a0a0a0] w-4 shrink-0">{{ $i + 1 }}</span>
                            @if($c->icon)
                                <img src="{{ $c->icon }}" alt="" class="w-8 h-8 rounded-lg object-cover shrink-0">
                            @else
                                <div class="w-8 h-8 rounded-lg ny-gradient-bg text-white flex items-center justify-center text-xs font-bold shrink-0">{{ mb_substr($c->name, 0, 1) }}</div>
                            @endif
                            <div class="min-w-0 flex-1">
                                <p class="font-semibold text-xs text-[#18181b] dark:text-[#fafafa] group-hover:text-[#0070f3] dark:group-hover:text-[#3291ff] transition truncate">{{ $c->name }}</p>
                                <p class="text-[10px] text-[#63636b] dark:text-[#a0a0a0]">{{ number_format($c->members_count) }} anggota</p>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>

            <!-- Followed Communities (If logged in) -->
            @auth
                @if($followedCommunities->count() > 0)
                    <div class="ny-card p-4 space-y-3">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-[#63636b] dark:text-[#a0a0a0] flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-[#0070f3] dark:text-[#3291ff]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Komunitas yang Diikuti</span>
                        </h3>
                        <div class="space-y-1.5">
                            @foreach($followedCommunities as $c)
                                <a href="{{ route('communities.show', $c->slug) }}" class="flex items-center gap-2.5 p-2 rounded-xl hover:bg-[#f7f7f8] dark:hover:bg-[#111111] transition group">
                                    @if($c->icon)
                                        <img src="{{ $c->icon }}" alt="" class="w-7 h-7 rounded-lg object-cover shrink-0">
                                    @else
                                        <div class="w-7 h-7 rounded-lg ny-gradient-bg text-white flex items-center justify-center text-xs font-bold shrink-0">{{ mb_substr($c->name, 0, 1) }}</div>
                                    @endif
                                    <p class="font-semibold text-xs text-[#18181b] dark:text-[#fafafa] group-hover:text-[#0070f3] dark:group-hover:text-[#3291ff] transition truncate flex-1">{{ $c->name }}</p>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endauth

            <!-- Information Guide Card -->
            <div class="p-4 rounded-2xl border border-[#e4e4e7] dark:border-[#1f1f1f] bg-white dark:bg-[#0a0a0a] text-xs space-y-2 text-[#71717a]">
                <h4 class="font-bold text-white text-xs flex items-center gap-1.5">
                    <span>💡 Tips Komunitas</span>
                </h4>
                <p class="text-[11px] leading-relaxed">
                    Setiap komunitas punya ruang diskusi dan topik khusus. Ajukan pertanyaan seputar bug, minta review arsitektur kode, atau adakan Live Stage santai.
                </p>
            </div>

        </aside>

    </div>

    <!-- ============================================== -->
    <!-- LIVE STAGE / SPACE INTERACTIVE AUDIO MODAL (UI) -->
    <!-- ============================================== -->
    <div x-show="liveStageModal" 
         x-cloak 
         @keydown.escape.window="liveStageModal = false"
         class="fixed inset-0 z-50 overflow-y-auto" 
         role="dialog" 
         aria-modal="true">
        
        <div class="fixed inset-0 bg-black/80 backdrop-blur-md" @click="liveStageModal = false"></div>

        <div class="min-h-full flex items-center justify-center p-4">
            <div class="relative w-full max-w-lg bg-[#000000] border border-[#27272a] rounded-3xl p-6 sm:p-7 space-y-6 text-white shadow-2xl">
                
                <!-- Stage Header -->
                <div class="flex items-start justify-between gap-3">
                    <div class="space-y-1">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-rose-500/15 border border-rose-500/30 text-rose-400 text-[10px] font-bold uppercase tracking-wider">
                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-pulse"></span>
                            <span>Live Panggung Komunitas</span>
                        </span>
                        <h3 class="text-base sm:text-lg font-bold">Bedah Portofolio & Tips Magang Frontend</h3>
                        <p class="text-xs text-[#a1a1aa]">Komunitas Web Dev Indonesia</p>
                    </div>

                    <button type="button" @click="liveStageModal = false" class="w-8 h-8 rounded-full bg-white/5 hover:bg-white/10 flex items-center justify-center text-[#71717a] hover:text-white transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <!-- Speakers Stage Grid -->
                <div class="space-y-2">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-[#71717a]">Pembicara di Panggung</p>
                    <div class="grid grid-cols-3 gap-3">
                        <!-- Speaker 1 (Host) -->
                        <div class="p-3 rounded-2xl bg-[#09090b] border border-[#27272a] flex flex-col items-center text-center space-y-2 relative">
                            <div class="relative">
                                <div class="w-12 h-12 rounded-full ny-gradient-bg flex items-center justify-center text-white font-bold ring-2 ring-[#0070f3] animate-pulse">
                                    AL
                                </div>
                                <span class="absolute -bottom-1 -right-1 w-4 h-4 rounded-full bg-[#0070f3] flex items-center justify-center text-[9px] text-white">
                                    <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-14 0m7 7v3m0-3a7 7 0 01-7-7m7 7a7 7 0 007-7M12 4a3 3 0 00-3 3v4a3 3 0 006 0V7a3 3 0 00-3-3z"/></svg>
                                </span>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-white truncate max-w-[85px]">@alexdev</p>
                                <span class="text-[9px] px-1.5 py-0.5 rounded-full bg-[#0070f3]/20 text-[#3291ff] font-semibold">Host</span>
                            </div>
                        </div>

                        <!-- Speaker 2 -->
                        <div class="p-3 rounded-2xl bg-[#09090b] border border-[#27272a] flex flex-col items-center text-center space-y-2 relative">
                            <div class="relative">
                                <div class="w-12 h-12 rounded-full bg-[#18181b] border border-[#27272a] flex items-center justify-center text-white font-bold">
                                    RN
                                </div>
                                <span class="absolute -bottom-1 -right-1 w-4 h-4 rounded-full bg-emerald-500 flex items-center justify-center text-[9px] text-white">
                                    <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-14 0m7 7v3m0-3a7 7 0 01-7-7m7 7a7 7 0 007-7M12 4a3 3 0 00-3 3v4a3 3 0 006 0V7a3 3 0 00-3-3z"/></svg>
                                </span>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-white truncate max-w-[85px]">@rina_ui</p>
                                <span class="text-[9px] px-1.5 py-0.5 rounded-full bg-white/10 text-[#a1a1aa] font-semibold">Speaker</span>
                            </div>
                        </div>

                        <!-- Speaker 3 -->
                        <div class="p-3 rounded-2xl bg-[#09090b] border border-[#27272a] flex flex-col items-center text-center space-y-2 relative">
                            <div class="relative">
                                <div class="w-12 h-12 rounded-full bg-[#18181b] border border-[#27272a] flex items-center justify-center text-white font-bold">
                                    FK
                                </div>
                                <span class="absolute -bottom-1 -right-1 w-4 h-4 rounded-full bg-rose-500 flex items-center justify-center text-[9px] text-white">
                                    <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"/></svg>
                                </span>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-white truncate max-w-[85px]">@fajar_k</p>
                                <span class="text-[9px] px-1.5 py-0.5 rounded-full bg-white/10 text-[#a1a1aa] font-semibold">Muted</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Audience Row Preview -->
                <div class="space-y-2 pt-2 border-t border-[#18181b]">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-[#71717a]">Pendengar (24)</p>
                    <div class="flex items-center gap-1.5 flex-wrap">
                        @for($i = 1; $i <= 8; $i++)
                            <div class="w-8 h-8 rounded-full bg-[#18181b] border border-[#27272a] text-[10px] text-[#a1a1aa] font-bold flex items-center justify-center" title="Pendengar {{ $i }}">
                                D{{ $i }}
                            </div>
                        @endfor
                        <div class="w-8 h-8 rounded-full bg-[#0070f3]/10 text-[#3291ff] text-[10px] font-bold flex items-center justify-center">
                            +16
                        </div>
                    </div>
                </div>

                <!-- Stage Controls Bar -->
                <div class="pt-2 border-t border-[#18181b] flex items-center justify-between">
                    <button type="button" @click="toggleStageAudio()" class="px-4 py-2.5 rounded-full bg-white text-black font-bold text-xs hover:bg-[#e4e4e7] transition flex items-center gap-2 cursor-pointer">
                        <span x-text="audioPlaying ? '⏸ Jeda Audio' : '▶ Dengarkan Audio'"></span>
                    </button>
                    
                    <button type="button" @click="if (window.notify) notify('Permintaan naik panggung telah dikirim ke Host!');" class="px-3.5 py-2.5 rounded-full border border-[#27272a] text-xs font-semibold text-white hover:bg-white/5 transition flex items-center gap-1.5 cursor-pointer">
                        <span>✋ Angkat Tangan</span>
                    </button>
                </div>

            </div>
        </div>
    </div>

    <!-- Create Community Modal -->
    @auth
        <dialog x-ref="createModal" class="rounded-3xl p-0 w-full max-w-md backdrop:bg-black/60 bg-transparent">
            <form method="POST" action="{{ route('communities.store') }}" class="ny-card bg-white dark:bg-[#0a0a0a] p-6 space-y-4 text-left border border-[#27272a]">
                @csrf
                <div class="flex items-center justify-between">
                    <h3 class="font-bold text-sm text-[#18181b] dark:text-[#fafafa]">Buat Komunitas Baru</h3>
                    <button type="button" @click="$refs.createModal.close()" class="text-[#71717a] hover:text-white">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="space-y-1">
                    <label class="text-xs font-semibold text-[#18181b] dark:text-[#fafafa]">Nama Komunitas</label>
                    <input type="text" name="name" required maxlength="60" class="ny-input text-sm" placeholder="mis. Web Dev Indonesia">
                </div>
                <div class="space-y-1">
                    <label class="text-xs font-semibold text-[#18181b] dark:text-[#fafafa]">Topik</label>
                    <input type="text" name="topic" maxlength="60" class="ny-input text-sm" placeholder="mis. Programming, AI, Mobile">
                </div>
                <div class="space-y-1">
                    <label class="text-xs font-semibold text-[#18181b] dark:text-[#fafafa]">Deskripsi</label>
                    <textarea name="description" rows="3" maxlength="500" class="ny-input text-sm resize-none" placeholder="Komunitas ini dibuat untuk..."></textarea>
                </div>
                <div class="space-y-1">
                    <label class="text-xs font-semibold text-[#18181b] dark:text-[#fafafa]">Visibilitas</label>
                    <select name="visibility" class="ny-input text-sm">
                        <option value="public">Publik</option>
                        <option value="private">Private</option>
                    </select>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="$refs.createModal.close()" class="btn-secondary text-xs py-2 px-4 cursor-pointer">Batal</button>
                    <button type="submit" class="btn-primary text-xs py-2 px-4 cursor-pointer">Buat Komunitas</button>
                </div>
            </form>
        </dialog>
    @endauth

</div>
@endsection
