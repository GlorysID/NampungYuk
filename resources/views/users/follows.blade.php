@extends('layouts.app')

@php $isFollowers = $mode === 'followers'; @endphp

@section('title', ($isFollowers ? 'Pengikut' : 'Mengikuti') . ' @' . $user->username . ' - NampungYuk')
@section('header_title', '@' . $user->username)
@section('header_subtitle', $isFollowers ? 'Pengikut' : 'Mengikuti')
@section('hide_header_notifications', true)

@section('content')
<div class="space-y-4">
    <a href="{{ route('profile.show', $user->username) }}"
       class="group inline-flex items-center gap-1.5 text-xs font-semibold text-[var(--text-muted)] hover:text-[var(--primary)] transition">
        <svg class="w-4 h-4 transition group-hover:-translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        <span>{{ $user->name }}</span>
    </a>

    @if($people->count() > 0)
        <section class="rounded-2xl bg-[var(--surface)] border border-[var(--border-color)] overflow-hidden">
            <div class="flex items-center justify-between gap-3 px-5 py-4 border-b border-[var(--border-color)]">
                <h2 class="text-xs font-bold uppercase tracking-wider text-[var(--text-muted)] font-mono">{{ $isFollowers ? 'Pengikut' : 'Mengikuti' }}</h2>
                <span class="text-[10px] font-mono px-2 py-0.5 rounded-md bg-[var(--surface-muted)] border border-[var(--border-color)] text-[var(--text-muted)]">
                    {{ number_format($people->total()) }} akun
                </span>
            </div>

            <div class="divide-y divide-[var(--border-color)]">
                @foreach($people as $person)
                    <div class="flex items-center gap-3.5 px-5 py-3.5">
                        <a href="{{ route('profile.show', $person->username) }}" class="shrink-0">
                            <x-user-avatar :user="$person" size="md" />
                        </a>
                        <div class="min-w-0 flex-1">
                            <a href="{{ route('profile.show', $person->username) }}"
                               class="block text-sm font-bold text-[var(--primary)] hover:underline truncate">{{ $person->name }}</a>
                            <p class="mt-0.5 text-xs font-mono text-[var(--text-muted)]">&#64;{{ $person->username }}</p>
                        </div>
                        <div class="shrink-0">
                            <x-follow-button :user="$person" size="sm" />
                        </div>
                    </div>
                @endforeach
            </div>

            @if($people->hasPages())
                <div class="px-5 py-4 border-t border-[var(--border-color)]">{{ $people->links() }}</div>
            @endif
        </section>
    @else
        <x-empty-state
            :title="$isFollowers ? 'Belum ada pengikut' : 'Belum mengikuti siapa pun'"
            :description="$isFollowers
                ? 'Kalau ada developer yang mengikuti akun ini, mereka akan muncul di sini.'
                : 'Akun ini belum mengikuti siapa pun. Jelajahi karya untuk menemukan developer yang menarik buat diikuti.'"
            :action-label="$isFollowers ? null : 'Jelajahi Karya'"
            :action-url="$isFollowers ? null : route('projects.index')">
            <x-slot:icon>
                <svg class="w-7 h-7 text-[var(--text-muted)]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M17 20v-1a4 4 0 00-4-4H7a4 4 0 00-4 4v1M10 11a4 4 0 100-8 4 4 0 000 8zM21 20v-1a4 4 0 00-3-3.87M16 3.13A4 4 0 0116 11"/></svg>
            </x-slot:icon>
        </x-empty-state>
    @endif
</div>
@endsection
