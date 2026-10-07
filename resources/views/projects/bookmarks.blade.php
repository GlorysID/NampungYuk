@extends('layouts.app')

@section('title', 'Koleksi Tersimpan — NampungYuk')
@section('meta_description', 'Koleksi project codingan pilihan yang disimpan untuk dipelajari kembali.')

@section('content')
<div class="space-y-6 max-w-4xl mx-auto">

    <!-- Header Banner -->
    <div class="ny-card p-5 sm:p-6 flex items-center justify-between gap-4 bg-white dark:bg-[#141821] border border-[#d5dbe2] dark:border-[#262d3a]">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-[#d9fbf4] dark:bg-[#50d2c1]/12 text-[#0e9c8b] dark:text-[#50d2c1] border border-[#50d2c1]/30 dark:border-[#50d2c1]/30 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                </svg>
            </div>
            <div>
                <h1 class="text-base sm:text-lg font-extrabold text-[#10161f] dark:text-[#eaecf0]">
                    Koleksi Proyek Tersimpan
                </h1>
                <p class="text-xs text-[#5c6979] dark:text-[#7e8a9a]">
                    Project pilihan yang ingin kamu pelajari kembali, eksplorasi arsitekturnya, atau coba kodingannya.
                </p>
            </div>
        </div>

        <a href="{{ route('projects.index') }}" 
           class="text-xs text-[#5c6979] hover:text-[#0e9c8b] font-medium hidden sm:inline-flex items-center gap-1 transition">
            <span>Jelajahi Feed</span>
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
            </svg>
        </a>
    </div>

    <!-- Bookmarks List -->
    <div class="space-y-4">
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

    <!-- Pagination -->
    @if($projects->hasPages())
        <div class="pt-4">
            {{ $projects->links() }}
        </div>
    @endif

</div>
@endsection
