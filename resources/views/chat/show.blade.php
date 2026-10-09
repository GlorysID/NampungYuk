@extends('layouts.app')

@section('title', 'Obrolan dengan ' . $other->name . ' — NampungYuk')

@section('content')
<div class="max-w-2xl mx-auto space-y-4">

    <a href="{{ route('chat.index') }}" class="inline-flex items-center gap-1.5 text-xs text-[#63636b] dark:text-[#a0a0a0] hover:text-[#0070f3] transition font-medium">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        <span>Kembali ke Obrolan</span>
    </a>

    <div class="ny-card p-4 flex items-center gap-3">
        <x-user-avatar :user="$other" size="md" />
        <div class="min-w-0">
            <p class="font-bold text-sm text-[#18181b] dark:text-[#fafafa] truncate">{{ $other->name }}</p>
            <p class="text-[11px] text-[#63636b] dark:text-[#a0a0a0] font-mono truncate">&#64;{{ $other->username }}</p>
        </div>
        <a href="{{ route('profile.show', $other->username) }}" class="ml-auto btn-secondary text-xs py-1.5 px-3">Profil</a>
    </div>

    <div class="ny-card p-10 text-center space-y-3">
        <div class="w-16 h-16 rounded-2xl ny-gradient-bg text-white mx-auto flex items-center justify-center">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
        </div>
        <h3 class="font-bold text-base text-[#18181b] dark:text-[#fafafa]">Chat realtime segera hadir</h3>
        <p class="text-sm text-[#63636b] dark:text-[#a0a0a0] max-w-sm mx-auto">Fitur obrolan langsung dengan {{ $other->name }} sedang dalam pengembangan.</p>
    </div>

</div>
@endsection
