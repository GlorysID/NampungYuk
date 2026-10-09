@extends('layouts.app')

@section('title', 'Obrolan — NampungYuk')

@section('content')
<div class="space-y-5">

    <div>
        <h1 class="text-xl font-bold text-[#18181b] dark:text-[#fafafa]">Obrolan</h1>
        <p class="text-xs text-[#63636b] dark:text-[#a0a0a0] mt-0.5">Ngobrol langsung dengan developer lain.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        <!-- Conversation list -->
        <div class="lg:col-span-7 space-y-3">
            <div class="ny-card p-4">
                <form method="GET" action="{{ route('chat.index') }}" class="relative">
                    <svg class="w-4 h-4 text-[#63636b] absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" name="q" placeholder="Cari percakapan..." class="ny-input text-sm !pl-10">
                </form>
            </div>

            <div class="ny-card p-10 text-center space-y-3">
                <div class="w-16 h-16 rounded-2xl ny-gradient-bg text-white mx-auto flex items-center justify-center shadow-[0_12px_30px_-8px_rgba(0,112,243,0.6)]">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                </div>
                <h3 class="font-bold text-base text-[#18181b] dark:text-[#fafafa]">Belum ada percakapan</h3>
                <p class="text-sm text-[#63636b] dark:text-[#a0a0a0] max-w-sm mx-auto">Mulai obrolan dengan memilih developer di samping. Fitur chat realtime akan segera hadir.</p>
            </div>
        </div>

        <!-- Suggested people -->
        <aside class="lg:col-span-5 space-y-4">
            <div class="ny-card p-4 space-y-3">
                <h3 class="text-xs font-bold uppercase tracking-wider text-[#63636b] dark:text-[#a0a0a0] flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-[#0070f3] dark:text-[#3291ff]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Mulai Obrolan</span>
                </h3>
                <div class="space-y-2">
                    @foreach($suggested as $dev)
                        <a href="{{ route('chat.show', $dev->username) }}" class="flex items-center gap-2.5 p-2 rounded-lg hover:bg-[#f5f5f5] dark:hover:bg-[#111111] transition group">
                            <x-user-avatar :user="$dev" size="sm" />
                            <div class="min-w-0 flex-1">
                                <p class="font-semibold text-xs text-[#18181b] dark:text-[#fafafa] group-hover:text-[#0070f3] dark:group-hover:text-[#3291ff] transition truncate">{{ $dev->name }}</p>
                                <p class="text-[10px] text-[#63636b] dark:text-[#a0a0a0] truncate">&#64;{{ $dev->username }}</p>
                            </div>
                            <svg class="w-4 h-4 text-[#63636b] dark:text-[#a0a0a0] group-hover:text-[#0070f3] transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    @endforeach
                </div>
            </div>
        </aside>
    </div>

</div>
@endsection
