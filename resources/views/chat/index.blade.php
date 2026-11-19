@extends('layouts.app')

@section('title', 'Obrolan — NampungYuk')
@section('header_title', 'Obrolan')
@section('main_width', 'max-w-none')
@section('hide_right_rail', '1')
@section('hide_footer', '1')

@section('content')
<div x-data="inboxLive({ me: {{ auth()->id() }} })">

    <!-- Mobile heading (hidden on desktop 2-pane) -->
    <div class="lg:hidden mb-3">
        <h1 class="text-xl font-bold text-[var(--primary)]">Obrolan</h1>
        <p class="text-xs text-[var(--text-muted)] mt-0.5">Ngobrol langsung dengan sesama developer.</p>
    </div>

    <!-- DESKTOP: unified 2-pane surface / MOBILE: list only -->
    <div class="bg-[var(--surface)] border border-[var(--border-color)] rounded-2xl overflow-hidden shadow-xs lg:grid lg:grid-cols-[20.5rem_1fr] lg:divide-x lg:divide-[var(--border-color)] h-[calc(100dvh-9.5rem)] lg:h-[calc(100vh-6.25rem)]">

        <!-- Conversation list pane -->
        <div class="flex flex-col min-h-0 h-full overflow-hidden">
            @include('chat._list')
        </div>

        <!-- Empty state pane (desktop only) -->
        <div class="hidden lg:flex flex-col items-center justify-center text-center p-10 h-full relative overflow-hidden bg-[var(--bg-app)] bg-[radial-gradient(rgba(255,255,255,0.035)_1px,transparent_1px)] [background-size:20px_20px]">
            <div class="max-w-md w-full space-y-6 relative z-10">
                <div class="space-y-2.5">
                    <div class="w-13 h-13 rounded-2xl bg-[var(--surface)] text-emerald-400 mx-auto flex items-center justify-center border border-[var(--border-color)] shadow-xs">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-[17px] text-[var(--primary)] tracking-tight">Ruang Obrolan Developer</h3>
                    <p class="text-[13px] text-[var(--text-muted)] leading-relaxed">
                        Pilih percakapan di sebelah kiri, atau langsung sapa developer di bawah untuk berdiskusi soal proyek, arsitektur, dan kolaborasi.
                    </p>
                </div>

                @if(($suggested ?? collect())->count() > 0)
                    <div class="bg-[var(--surface)] border border-[var(--border-color)] rounded-2xl p-3 text-left space-y-2">
                        <p class="px-2 pt-0.5 text-[10.5px] font-mono font-semibold uppercase tracking-[0.1em] text-[var(--text-faint)]">
                            Developer Disarankan
                        </p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            @foreach($suggested->take(4) as $dev)
                                <a href="{{ route('chat.show', $dev->username) }}"
                                   class="flex items-center gap-2.5 p-2.5 rounded-xl bg-[var(--surface-subtle)] border border-[var(--border-color)] hover:border-[var(--border-strong)] hover:bg-[var(--surface-hover)] transition group">
                                    <x-user-avatar :user="$dev" size="sm" class="shrink-0" />
                                    <div class="min-w-0 flex-1">
                                        <p class="text-[12.5px] font-semibold text-[var(--primary)] group-hover:text-emerald-400 transition truncate leading-tight">{{ $dev->name }}</p>
                                        <p class="text-[11px] font-mono text-[var(--text-muted)] truncate mt-0.5">&#64;{{ $dev->username }}</p>
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
