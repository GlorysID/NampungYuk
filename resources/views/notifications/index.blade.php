@extends('layouts.app')

@section('title', 'Notifikasi — NampungYuk')

@section('content')
<div class="max-w-3xl mx-auto space-y-4">

    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2.5">
            <h1 class="text-lg font-bold text-[#10161f] dark:text-[#eaecf0]">Notifikasi</h1>
            <span class="hl-label">{{ $notifications->total() }} total</span>
        </div>

        @if($notifications->total() > 0)
            <form method="POST" action="{{ route('notifications.readAll') }}">
                @csrf
                <button type="submit" class="btn-secondary text-xs py-1.5 px-3">
                    Tandai semua dibaca
                </button>
            </form>
        @endif
    </div>

    <div class="hl-panel divide-y divide-[#d5dbe2] dark:divide-[#262d3a] overflow-hidden">
        @forelse($notifications as $notification)
            @php $data = $notification->data; @endphp
            <a href="{{ $data['url'] ?? '#' }}"
               class="flex items-start gap-3 p-4 transition hover:bg-[#f2f4f6] dark:hover:bg-[#1b212c] {{ is_null($notification->read_at) ? 'bg-[#d9fbf4]/40 dark:bg-[#50d2c1]/[0.06]' : '' }}">
                
                <!-- Icon / Avatar -->
                <div class="shrink-0">
                    @if(!empty($data['actor_avatar']))
                        <img src="{{ $data['actor_avatar'] }}" alt="" class="w-9 h-9 rounded-md object-cover ring-1 ring-[#d5dbe2] dark:ring-[#262d3a]">
                    @else
                        <div class="w-9 h-9 rounded-md bg-[#e6eaee] dark:bg-[#1e2530] flex items-center justify-center text-[#0e9c8b] dark:text-[#50d2c1]">
                            @if(($data['type'] ?? '') === 'project_uploaded')
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            @elseif(($data['type'] ?? '') === 'project_trending')
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                            @else
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            @endif
                        </div>
                    @endif
                </div>

                <!-- Body -->
                <div class="min-w-0 flex-1">
                    <p class="text-xs sm:text-sm text-[#10161f] dark:text-[#eaecf0] leading-relaxed">
                        {{ $data['message'] ?? 'Ada aktivitas baru' }}
                    </p>
                    <p class="text-[11px] font-mono text-[#5c6979] dark:text-[#7e8a9a] mt-1">
                        {{ $notification->created_at->diffForHumans() }}
                    </p>
                </div>

                @if(is_null($notification->read_at))
                    <span class="w-2 h-2 rounded-full bg-[#0e9c8b] dark:bg-[#50d2c1] mt-2 shrink-0"></span>
                @endif
            </a>
        @empty
            <div class="p-10 text-center space-y-2">
                <div class="w-11 h-11 rounded-lg bg-[#e6eaee] dark:bg-[#1e2530] mx-auto flex items-center justify-center text-[#5c6979] dark:text-[#7e8a9a]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                </div>
                <p class="text-sm font-semibold text-[#10161f] dark:text-[#eaecf0]">Belum ada notifikasi</p>
                <p class="text-xs text-[#5c6979] dark:text-[#7e8a9a]">Ikuti developer lain untuk mendapat kabar saat mereka mengunggah project.</p>
            </div>
        @endforelse
    </div>

    @if($notifications->hasPages())
        <div>{{ $notifications->links() }}</div>
    @endif

</div>
@endsection
