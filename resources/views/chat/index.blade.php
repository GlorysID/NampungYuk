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
        <h1 class="text-xl font-bold text-[#0B0B0D] dark:text-[#EAE8E1]">Obrolan</h1>
        <p class="text-xs text-[#6E6D67] dark:text-[#8E8D86] mt-0.5">Ngobrol langsung dengan sesama developer.</p>
    </div>

    <!-- DESKTOP: 2-pane (list left + empty panel right) / MOBILE: list only -->
    <div class="lg:grid lg:grid-cols-[20rem_1fr] lg:gap-4 h-[calc(100dvh-9.5rem)] lg:h-[calc(100vh-6.5rem)]">

        <!-- Conversation list pane -->
        <div class="bg-white dark:bg-[#121214] border border-[#E2DFD7] dark:border-[#242426] rounded-2xl overflow-hidden shadow-xs flex flex-col min-h-0 h-full">
            @include('chat._list')
        </div>

        <!-- Empty state pane (desktop only) -->
        <div class="hidden lg:flex bg-white dark:bg-[#121214] border border-[#E2DFD7] dark:border-[#242426] rounded-2xl flex-col items-center justify-center text-center p-10 shadow-xs h-full">
            <div class="w-14 h-14 rounded-2xl bg-[#EFECE6] dark:bg-[#161619] text-[#6E6D67] dark:text-[#8E8D86] mx-auto flex items-center justify-center border border-[#E2DFD7] dark:border-[#242426]">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
            </div>
            <h3 class="font-bold text-base text-[#0B0B0D] dark:text-[#EAE8E1] mt-4">Pilih percakapan</h3>
            <p class="text-xs text-[#6E6D67] dark:text-[#8E8D86] max-w-xs mt-1">Pilih obrolan di sebelah kiri, atau cari nama developer untuk memulai percakapan baru.</p>
        </div>
    </div>

</div>
@endsection
