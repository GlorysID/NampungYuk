@extends('layouts.app')

@section('title', 'Notifikasi — NampungYuk')
@section('header_title', 'Notifikasi')

@section('content')
<div class="max-w-3xl mx-auto space-y-4">

    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2.5">
            <h1 class="text-xl font-bold text-[#0B0B0D] dark:text-[#EAE8E1]">Notifikasi</h1>
            <span class="text-xs font-mono px-2 py-0.5 rounded-full bg-[#EFECE6] dark:bg-[#161619] text-[#6E6D67] dark:text-[#8E8D86] border border-[#E2DFD7] dark:border-[#242426]">{{ $notifications->total() }} total</span>
        </div>

        @if($notifications->total() > 0)
            <form method="POST" action="{{ route('notifications.readAll') }}">
                @csrf
                <button type="submit" class="btn-secondary text-xs py-1.5 px-3.5 cursor-pointer shadow-xs">
                    Tandai semua dibaca
                </button>
            </form>
        @endif
    </div>

    <div class="bg-white dark:bg-[#121214] border border-[#E2DFD7] dark:border-[#242426] rounded-2xl divide-y divide-[#E2DFD7] dark:divide-[#242426] overflow-hidden shadow-xs">
        @forelse($notifications as $notification)
            @php $data = $notification->data; @endphp
            <a href="{{ $data['url'] ?? '#' }}"
               class="flex items-start gap-3.5 p-4 transition hover:bg-[#FAF8F5] dark:hover:bg-[#161619] {{ is_null($notification->read_at) ? 'bg-[#EFECE6]/60 dark:bg-[#19191D]/90 border-l-2 border-[#0B0B0D] dark:border-[#EAE8E1]' : 'border-l-2 border-transparent' }}">
                
                <!-- Icon / Avatar -->
                <div class="shrink-0">
                    @if(!empty($data['actor_avatar']))
                        <img src="{{ $data['actor_avatar'] }}" alt="" class="w-9 h-9 rounded-xl object-cover border border-[#E2DFD7] dark:border-[#242426]">
                    @else
                        <div class="w-9 h-9 rounded-xl bg-[#EFECE6] dark:bg-[#161619] flex items-center justify-center text-[#0B0B0D] dark:text-[#EAE8E1] border border-[#E2DFD7] dark:border-[#242426]">
                            @if(($data['type'] ?? '') === 'project_uploaded' || ($data['type'] ?? '') === 'project_reposted')
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            @elseif(($data['type'] ?? '') === 'project_trending')
                                <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                            @elseif(($data['type'] ?? '') === 'community_post' || ($data['type'] ?? '') === 'community_comment')
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            @else
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            @endif
                        </div>
                    @endif
                </div>

                <!-- Body -->
                <div class="min-w-0 flex-1">
                    <p class="text-xs sm:text-sm text-[#0B0B0D] dark:text-[#EAE8E1] leading-relaxed">
                        {{ $data['message'] ?? 'Ada aktivitas baru' }}
                    </p>
                    <p class="text-[11px] font-mono text-[#908F88] dark:text-[#605F5A] mt-1">
                        {{ $notification->created_at->diffForHumans() }}
                    </p>
                </div>

                @if(is_null($notification->read_at))
                    <span class="w-2 h-2 rounded-full bg-[#0B0B0D] dark:bg-[#EAE8E1] mt-2 shrink-0" title="Belum dibaca"></span>
                @endif
            </a>
        @empty
            <div class="p-10 text-center space-y-2">
                <div class="w-12 h-12 rounded-2xl bg-[#EFECE6] dark:bg-[#161619] mx-auto flex items-center justify-center text-[#908F88] dark:text-[#605F5A] border border-[#E2DFD7] dark:border-[#242426]">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                </div>
                <p class="text-sm font-semibold text-[#0B0B0D] dark:text-[#EAE8E1]">Belum ada notifikasi</p>
                <p class="text-xs text-[#6E6D67] dark:text-[#8E8D86]">Ikuti developer lain untuk mendapat kabar saat mereka mempublikasikan project baru.</p>
            </div>
        @endforelse
    </div>

    @if($notifications->hasPages())
        <div>{{ $notifications->links() }}</div>
    @endif

</div>
@endsection
