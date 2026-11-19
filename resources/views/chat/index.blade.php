@extends('layouts.app')

@section('title', 'Obrolan — NampungYuk')
@section('header_title', 'Obrolan')
@section('main_width', 'max-w-none')
@section('hide_right_rail', '1')
@section('hide_footer', '1')
@section('full_bleed', '1')
@section('compact_sidebar', '1')

@section('header')
    <!-- Mobile-only compact header (desktop merges seamlessly into full-height 2-pane) -->
    <header class="lg:hidden sticky top-0 z-20 h-14 px-4 flex items-center justify-between gap-3 bg-[var(--bg-app)]/90 backdrop-blur-md border-b border-[var(--border-color)]">
        <div class="flex items-center gap-2.5 min-w-0">
            <button @click="mobileSidebarOpen = true" type="button" aria-label="Buka Menu Sidebar"
                    class="p-2 -ml-1 rounded-lg border border-[var(--border-color)] text-[var(--text-muted)] hover:text-[var(--primary)] transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <h1 class="text-[15px] font-bold text-[var(--primary)] truncate">Obrolan</h1>
        </div>
    </header>
@endsection

@section('content')
<div x-data="inboxLive({ me: {{ auth()->id() }} })" class="flex-1 flex flex-col min-h-0 overflow-hidden">

    <!-- DESKTOP: full-bleed seamless 2-pane / MOBILE: list only -->
    <div class="flex-1 min-h-0 h-full overflow-hidden flex flex-col lg:grid lg:grid-rows-[minmax(0,1fr)] lg:grid-cols-[23rem_1fr] xl:grid-cols-[25.5rem_1fr] lg:divide-x lg:divide-[var(--border-color)]">

        <!-- Conversation list pane -->
        <div class="flex-1 flex flex-col min-h-0 h-full overflow-hidden">
            @include('chat._list')
        </div>

        <!-- Empty state pane (desktop only) -->
        <div class="hidden lg:flex flex-col items-center justify-center text-center p-10 h-full min-h-0 overflow-y-auto relative bg-[var(--bg-app)] bg-[radial-gradient(rgba(255,255,255,0.035)_1px,transparent_1px)] [background-size:20px_20px]">
            <div class="max-w-2xl w-full space-y-8 relative z-10">
                <div class="space-y-3">
                    <div class="w-16 h-16 rounded-2xl bg-[var(--surface)] text-emerald-400 mx-auto flex items-center justify-center border border-[var(--border-color)] shadow-xs">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-2xl text-[var(--primary)] tracking-tight">Ruang Obrolan Developer</h3>
                    <p class="text-[15px] text-[var(--text-muted)] leading-relaxed max-w-lg mx-auto">
                        Pilih percakapan di sebelah kiri, atau langsung sapa developer di bawah untuk berdiskusi soal proyek, arsitektur, dan kolaborasi.
                    </p>
                </div>

                @if(($suggested ?? collect())->count() > 0)
                    <div class="bg-[var(--surface)] border border-[var(--border-color)] rounded-2xl p-5 text-left space-y-3.5">
                        <p class="px-1 text-xs font-mono font-semibold uppercase tracking-[0.1em] text-[var(--text-faint)]">
                            Developer Disarankan
                        </p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            @foreach($suggested->take(4) as $dev)
                                <a href="{{ route('chat.show', $dev->username) }}"
                                   class="flex items-center gap-3.5 p-3.5 rounded-xl bg-[var(--surface-subtle)] border border-[var(--border-color)] hover:border-[var(--border-strong)] hover:bg-[var(--surface-hover)] transition group">
                                    <x-user-avatar :user="$dev" size="md" class="shrink-0" />
                                    <div class="min-w-0 flex-1">
                                        <p class="text-[15px] font-semibold text-[var(--primary)] group-hover:text-emerald-400 transition truncate leading-tight">{{ $dev->name }}</p>
                                        <p class="text-[13px] font-mono text-[var(--text-muted)] truncate mt-0.5">&#64;{{ $dev->username }}</p>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

</div>
@endsection
