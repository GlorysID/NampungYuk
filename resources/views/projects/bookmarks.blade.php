@extends('layouts.app')

@section('title', 'Koleksi Tersimpan — NampungYuk')
@section('meta_description', 'Koleksi project codingan pilihan yang disimpan untuk dipelajari kembali.')
@section('header_title', 'Koleksi Tersimpan')

@section('content')
<div class="space-y-6 max-w-4xl mx-auto">

    <!-- Header Banner -->
    <div class="rounded-2xl bg-[var(--surface)] border border-[var(--border-color)] p-5 sm:p-6 flex items-center justify-between gap-4 shadow-xs">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-[var(--primary)] text-[var(--primary-text)] flex items-center justify-center shrink-0 shadow-xs">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                </svg>
            </div>
            <div>
                <h1 class="text-lg sm:text-xl font-extrabold text-[var(--primary)] tracking-tight">
                    Koleksi Proyek Tersimpan
                </h1>
                <p class="text-sm text-[var(--text-muted)] leading-relaxed mt-0.5">
                    Project pilihan yang ingin kamu pelajari kembali, eksplorasi arsitekturnya, atau coba kodingannya.
                </p>
            </div>
        </div>

        <a href="{{ route('projects.index') }}" 
           class="px-3.5 py-2 rounded-xl border border-[var(--border-color)] bg-[var(--surface-muted)] hover:bg-[var(--surface-chip-hover)] text-[var(--primary)] text-xs font-semibold hidden sm:inline-flex items-center gap-1.5 transition">
            <span>Jelajahi Feed</span>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
            </svg>
        </a>
    </div>

    <!-- Bookmarks List (infinite scroll) -->
    <div x-data="infiniteFeed({
            nextPageUrl: {{ $projects->hasMorePages() ? Illuminate\Support\Js::from(route('feeds.bookmarks', ['page' => $projects->currentPage() + 1])) : 'null' }},
            lastPage: {{ $projects->lastPage() }}
         })">
        <div class="space-y-4" x-ref="items" data-feed-items>
            @forelse($projects as $project)
                @php
                    $initialVote = $userVotes[$project->id] ?? null;
                @endphp
                <x-project-card :project="$project" :initial-vote="$initialVote" :is-bookmarked="true" />
            @empty
                <x-empty-state 
                    title="Belum ada project yang disimpan" 
                    description="Simpan project menarik dari feed dengan menekan tombol bookmark agar kamu bisa mempelajarinya kembali nanti."
                    action-label="Jelajahi Feed"
                    :action-url="route('projects.index')">
                    <x-slot:icon>
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                        </svg>
                    </x-slot:icon>
                </x-empty-state>
            @endforelse
        </div>

        <div x-ref="sentinel" class="pt-4 flex justify-center">
            <button type="button" x-show="!done" @click="loadMore()" :disabled="loading"
                    class="px-5 py-2.5 rounded-xl border border-[var(--border-color)] bg-[var(--surface-muted)] hover:bg-[var(--surface-chip-hover)] text-[var(--primary)] text-xs font-semibold shadow-xs transition disabled:opacity-60 cursor-pointer">
                <span x-show="!loading">Muat lebih banyak</span>
                <span x-show="loading" x-cloak>Memuat...</span>
            </button>
        </div>
    </div>

</div>
@endsection
