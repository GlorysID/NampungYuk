@php
    // Shared conversation list + message requests + search.
    // Expects: $conversations, $requests, $search, $peopleResults, $active (optional username).
    $active = $active ?? null;
@endphp

<div class="flex flex-col h-full min-h-0">
    {{-- Search --}}
    <div class="p-3 shrink-0 border-b border-[#E2DFD7] dark:border-[#242426]">
        <form method="GET" action="{{ route('chat.index') }}" class="relative">
            <svg class="w-4 h-4 text-[#908F88] absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input type="text" name="q" value="{{ $search }}" placeholder="Cari obrolan atau akun..." class="ny-input text-xs sm:text-sm !pl-10">
        </form>
    </div>

    <div class="flex-1 overflow-y-auto min-h-0">
        {{-- Message requests --}}
        @if(($requests ?? collect())->count() > 0)
            <div class="border-b border-amber-300 dark:border-amber-500/20" x-data="{ busy: null }">
                <div class="px-4 py-2 bg-amber-50 dark:bg-amber-950/30 flex items-center gap-2">
                    <svg class="w-3.5 h-3.5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-amber-800 dark:text-amber-300">Permintaan Pesan</span>
                    <span class="text-[10px] font-mono px-1.5 py-0.5 rounded bg-amber-200/70 dark:bg-amber-900/50 text-amber-900 dark:text-amber-200">{{ $requests->count() }}</span>
                </div>
                <div class="divide-y divide-[#E2DFD7] dark:divide-[#242426]">
                    @foreach($requests as $c)
                        <div class="flex items-center gap-3 p-3.5">
                            <a href="{{ route('profile.show', $c->other->username) }}" class="shrink-0">
                                <x-user-avatar :user="$c->other" size="md" />
                            </a>
                            <div class="min-w-0 flex-1">
                                <p class="font-semibold text-sm text-[#0B0B0D] dark:text-[#EAE8E1] truncate">{{ $c->other->name }}</p>
                                <p class="text-xs text-[#6E6D67] dark:text-[#8E8D86] truncate">{{ $c->last->body ?? 'Ingin mengirim pesan…' }}</p>
                            </div>
                            <div class="flex items-center gap-1.5 shrink-0">
                                <button type="button" :disabled="busy === {{ $c->id }}"
                                        @click="busy = {{ $c->id }}; fetch('{{ route('chat.accept', $c->other) }}', { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' } }).then(r => r.ok && window.location.reload())"
                                        class="px-2.5 py-1.5 rounded-lg bg-[#0B0B0D] hover:bg-[#222225] dark:bg-[#EAE8E1] dark:hover:bg-[#DCD9D0] text-[#EAE8E1] dark:text-[#0B0B0D] text-xs font-semibold disabled:opacity-60 cursor-pointer shadow-xs">
                                    Terima
                                </button>
                                <button type="button" :disabled="busy === {{ $c->id }}"
                                        @click="busy = {{ $c->id }}; fetch('{{ route('chat.decline', $c->other) }}', { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' } }).then(r => r.ok && window.location.reload())"
                                        class="px-2.5 py-1.5 rounded-lg border border-rose-200 dark:border-rose-800/60 text-rose-600 dark:text-rose-400 text-xs font-semibold hover:bg-rose-50 dark:hover:bg-rose-950/30 disabled:opacity-60 cursor-pointer">
                                    Hapus
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Active / recent conversations --}}
        <div class="divide-y divide-[#E2DFD7] dark:divide-[#242426]">
            @forelse($conversations as $c)
                <a href="{{ route('chat.show', $c->other->username) }}" data-conv="{{ $c->id }}"
                   class="flex items-center gap-3 p-3.5 transition {{ $active === $c->other->username ? 'bg-[#EFECE6] dark:bg-[#19191D] border-l-2 border-[#EAE8E1]' : 'hover:bg-[#FAF8F5] dark:hover:bg-[#161619]' }}">
                    <x-user-avatar :user="$c->other" size="md" />
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center justify-between gap-2">
                            <p class="font-semibold text-sm text-[#0B0B0D] dark:text-[#EAE8E1] truncate">{{ $c->other->name }}</p>
                            @if($c->last)
                                <span data-conv-time class="text-[10px] text-[#908F88] dark:text-[#605F5A] font-mono shrink-0">{{ $c->last->created_at->diffForHumans(null, true) }}</span>
                            @endif
                        </div>
                        <p data-conv-preview class="text-xs text-[#6E6D67] dark:text-[#8E8D86] truncate">
                            @if($c->last && $c->last->sender_id === auth()->id())
                                <span class="text-[#908F88] dark:text-[#605F5A]">Kamu: </span>
                            @endif
                            {{ $c->last->body ?? 'Mulai percakapan…' }}
                        </p>
                    </div>
                    @if($c->isPending())
                        <span class="shrink-0 text-[10px] font-semibold px-2 py-0.5 rounded-full bg-[#EFECE6] dark:bg-[#161619] text-[#6E6D67] dark:text-[#8E8D86] border border-[#E2DFD7] dark:border-[#242426]">Menunggu</span>
                    @endif
                    <span data-conv-unread class="shrink-0 min-w-5 h-5 px-1.5 rounded-full bg-[#0B0B0D] text-[#EAE8E1] dark:bg-[#EAE8E1] dark:text-[#0B0B0D] text-[10px] font-mono font-bold flex items-center justify-center {{ $c->unread > 0 ? '' : 'hidden' }}">{{ $c->unread }}</span>
                </a>
            @empty
                <div class="p-8 text-center space-y-2">
                    <p class="text-xs text-[#6E6D67] dark:text-[#8E8D86]">Belum ada obrolan. Mulai obrolan dengan developer lain.</p>
                </div>
            @endforelse
        </div>

        {{-- People search results --}}
        @if($search && ($peopleResults ?? collect())->count() > 0)
            <div class="border-t border-[#E2DFD7] dark:border-[#242426]">
                <p class="px-4 py-2 text-[10px] font-bold uppercase tracking-wider text-[#6E6D67] dark:text-[#8E8D86]">Akun Developer</p>
                <div class="divide-y divide-[#E2DFD7] dark:divide-[#242426]">
                    @foreach($peopleResults as $dev)
                        <a href="{{ route('chat.show', $dev->username) }}" class="flex items-center gap-2.5 p-3 hover:bg-[#FAF8F5] dark:hover:bg-[#161619] transition group">
                            <x-user-avatar :user="$dev" size="sm" />
                            <div class="min-w-0 flex-1">
                                <p class="font-semibold text-xs text-[#0B0B0D] dark:text-[#EAE8E1] group-hover:text-[#222225] dark:group-hover:text-[#DCD9D0] transition truncate">{{ $dev->name }}</p>
                                <p class="text-[10px] text-[#6E6D67] dark:text-[#8E8D86] truncate">&#64;{{ $dev->username }}</p>
                            </div>
                            <svg class="w-4 h-4 text-[#908F88] group-hover:text-[#0B0B0D] dark:group-hover:text-[#EAE8E1] transition shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>
