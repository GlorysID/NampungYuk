@extends('layouts.app')

@section('title', 'Koleksi Tersimpan — NampungYuk')
@section('meta_description', 'Koleksi project codingan pilihan yang disimpan untuk dipelajari kembali.')

@section('content')
<div class="space-y-6 max-w-4xl mx-auto">

    <!-- Header Banner -->
    <div class="ny-card p-5 sm:p-6 flex items-center justify-between gap-4 bg-white dark:bg-[#0a0a0a] border border-[#e4e4e7] dark:border-[#1f1f1f]">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-[#e8f2ff] dark:bg-[#3291ff]/12 text-[#0070f3] dark:text-[#3291ff] border border-[#3291ff]/30 dark:border-[#3291ff]/30 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                </svg>
            </div>
            <div>
                <h1 class="text-base sm:text-lg font-extrabold text-[#18181b] dark:text-[#fafafa]">
                    Koleksi Proyek Tersimpan
                </h1>
                <p class="text-xs text-[#63636b] dark:text-[#a0a0a0]">
                    Project pilihan yang ingin kamu pelajari kembali, eksplorasi arsitekturnya, atau coba kodingannya.
                </p>
            </div>
        </div>

        <a href="{{ route('projects.index') }}" 
           class="text-xs text-[#63636b] hover:text-[#0070f3] font-medium hidden sm:inline-flex items-center gap-1 transition">
            <span>Jelajahi Feed</span>
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                    action-label="Explore Projects"
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
            <button type="button" x-show="!done" @click="loadMore()" :disabled="loading" class="btn-secondary text-xs py-2.5 px-5 disabled:opacity-60">
                <span x-show="!loading">Muat lebih banyak</span>
                <span x-show="loading" x-cloak>Memuat...</span>
            </button>
        </div>
    </div>

</div>
@endsection
