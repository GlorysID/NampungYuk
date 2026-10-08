@extends('layouts.app')

@section('title', 'Notifikasi — NampungYuk')

@section('content')
<div class="max-w-3xl mx-auto space-y-4">

    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2.5">
            <h1 class="text-lg font-bold text-[#000000] dark:text-[#fafafa]">Notifikasi</h1>
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

    <div class="hl-panel divide-y divide-[#eaeaea] dark:divide-[#1f1f1f] overflow-hidden">
        @forelse($notifications as $notification)
            @php $data = $notification->data; @endphp
            <a href="{{ $data['url'] ?? '#' }}"
               class="flex items-start gap-3 p-4 transition hover:bg-[#f5f5f5] dark:hover:bg-[#111111] {{ is_null($notification->read_at) ? 'bg-[#e8f2ff]/40 dark:bg-[#3291ff]/[0.06]' : '' }}">
                
                <!-- Icon / Avatar -->
                <div class="shrink-0">
                    @if(!empty($data['actor_avatar']))
                        <img src="{{ $data['actor_avatar'] }}" alt="" class="w-9 h-9 rounded-md object-cover ring-1 ring-[#eaeaea] dark:ring-[#1f1f1f]">
                    @else
                        <div class="w-9 h-9 rounded-md bg-[#f2f2f2] dark:bg-[#171717] flex items-center justify-center text-[#0070f3] dark:text-[#3291ff]">
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
                    <p class="text-xs sm:text-sm text-[#000000] dark:text-[#fafafa] leading-relaxed">
                        {{ $data['message'] ?? 'Ada aktivitas baru' }}
                    </p>
                    <p class="text-[11px] font-mono text-[#666666] dark:text-[#a0a0a0] mt-1">
                        {{ $notification->created_at->diffForHumans() }}
                    </p>
                </div>

                @if(is_null($notification->read_at))
                    <span class="w-2 h-2 rounded-full bg-[#0070f3] dark:bg-[#3291ff] mt-2 shrink-0"></span>
                @endif
            </a>
        @empty
            <div class="p-10 text-center space-y-2">
                <div class="w-11 h-11 rounded-lg bg-[#f2f2f2] dark:bg-[#171717] mx-auto flex items-center justify-center text-[#666666] dark:text-[#a0a0a0]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                </div>
                <p class="text-sm font-semibold text-[#000000] dark:text-[#fafafa]">Belum ada notifikasi</p>
                <p class="text-xs text-[#666666] dark:text-[#a0a0a0]">Ikuti developer lain untuk mendapat kabar saat mereka mengunggah project.</p>
            </div>
        @endforelse
    </div>

    @if($notifications->hasPages())
        <div>{{ $notifications->links() }}</div>
    @endif

</div>
@endsection
