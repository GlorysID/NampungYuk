@extends('layouts.app')

@section('title', 'Obrolan — NampungYuk')

@section('content')
<div x-data="inboxLive({ me: {{ auth()->id() }} })">

    <!-- Mobile heading (hidden on desktop 2-pane) -->
    <div class="lg:hidden mb-4">
        <h1 class="text-xl font-bold text-[#18181b] dark:text-[#fafafa]">Obrolan</h1>
        <p class="text-xs text-[#63636b] dark:text-[#a0a0a0] mt-0.5">Ngobrol langsung dengan developer lain.</p>
    </div>

    <!-- DESKTOP: 2-pane (list left + empty panel right) / MOBILE: list only -->
    <div class="lg:grid lg:grid-cols-[20rem_1fr] lg:gap-4 lg:h-[calc(100vh-9rem)]">

        <!-- Conversation list pane -->
        <div class="ny-card overflow-hidden lg:flex lg:flex-col lg:min-h-0">
            @include('chat._list')
        </div>

        <!-- Empty state pane (desktop only) -->
        <div class="hidden lg:flex ny-card flex-col items-center justify-center text-center p-10">
            <div class="w-16 h-16 rounded-2xl ny-gradient-bg text-white mx-auto flex items-center justify-center shadow-[0_12px_30px_-8px_rgba(0,112,243,0.6)]">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
            </div>
            <h3 class="font-bold text-base text-[#18181b] dark:text-[#fafafa] mt-4">Pilih percakapan</h3>
            <p class="text-sm text-[#63636b] dark:text-[#a0a0a0] max-w-xs mt-1">Pilih obrolan di sebelah kiri, atau cari developer untuk mulai percakapan baru.</p>
        </div>
    </div>

</div>
@endsection
