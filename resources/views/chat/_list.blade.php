@php
    // Shared conversation list + message requests + search + suggested creators.
    // Expects: $conversations, $requests, $search, $peopleResults, $suggested (optional), $active (optional username).
    $active = $active ?? null;
    $existingOtherIds = ($conversations ?? collect())->pluck('other.id')->filter()->all();
    $suggestedPeople = ($suggested ?? collect())
        ->reject(fn ($u) => in_array($u->id, $existingOtherIds, true))
        ->take(5)
        ->values();
@endphp

<div class="flex flex-col h-full min-h-0 bg-[var(--surface)]">
    {{-- Pane header + search --}}
    <div class="px-4 pt-3.5 pb-3 shrink-0 border-b border-[var(--border-color)] space-y-2.5">
        <div class="flex items-center justify-between gap-2">
            <div class="flex items-center gap-2 min-w-0">
                <h2 class="text-[14px] font-bold text-[var(--primary)] tracking-tight truncate">Pesan</h2>
                <span class="text-[10.5px] font-mono tabular-nums px-1.5 py-0.5 rounded-md bg-[var(--surface-muted)] border border-[var(--border-color)] text-[var(--text-muted)] leading-none">
                    {{ ($conversations ?? collect())->count() }}
                </span>
            </div>
            @if(($requests ?? collect())->count() > 0)
                <a href="#permintaan"
                   class="text-[11px] font-semibold text-amber-400 hover:text-amber-300 transition inline-flex items-center gap-1">
                    <span>{{ $requests->count() }} permintaan</span>
                </a>
            @endif
        </div>

        <form method="GET" action="{{ route('chat.index') }}" class="relative">
            <svg class="w-4 h-4 text-[var(--text-faint)] absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input type="text"
                   name="q"
                   value="{{ $search }}"
                   placeholder="Cari obrolan atau developer..."
                   class="w-full h-9 pl-9 pr-3 rounded-xl bg-[var(--surface-subtle)] border border-[var(--border-color)] focus:border-[var(--border-strong)] text-xs text-[var(--primary)] placeholder:text-[var(--text-faint)] outline-none transition">
        </form>
    </div>

    <div class="flex-1 overflow-y-auto min-h-0 no-scrollbar">
        {{-- Message requests --}}
        @if(($requests ?? collect())->count() > 0)
            <div id="permintaan" class="border-b border-amber-500/20" x-data="{ busy: null }">
                <div class="px-4 py-2 bg-amber-950/25 flex items-center gap-2">
                    <svg class="w-3.5 h-3.5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    <span class="text-[10.5px] font-mono font-bold uppercase tracking-wider text-amber-300">Permintaan Pesan</span>
                    <span class="ml-auto text-[10px] font-mono px-1.5 py-0.5 rounded bg-amber-900/50 text-amber-200">{{ $requests->count() }}</span>
                </div>
                <div class="divide-y divide-[var(--border-color)]">
                    @foreach($requests as $c)
                        <div class="flex items-center gap-3 px-4 py-3">
                            <a href="{{ route('profile.show', $c->other->username) }}" class="shrink-0">
                                <x-user-avatar :user="$c->other" size="md" />
                            </a>
                            <div class="min-w-0 flex-1">
                                <p class="font-semibold text-[13px] text-[var(--primary)] truncate">{{ $c->other->name }}</p>
                                <p class="text-[11.5px] text-[var(--text-muted)] truncate">{{ $c->last->body ?? 'Ingin mengirim pesan…' }}</p>
                            </div>
                            <div class="flex items-center gap-1.5 shrink-0">
                                <button type="button" :disabled="busy === {{ $c->id }}"
                                        @click="busy = {{ $c->id }}; fetch('{{ route('chat.accept', $c->other) }}', { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' } }).then(r => r.ok && window.location.reload())"
                                        class="px-2.5 py-1.5 rounded-lg bg-[var(--primary)] hover:bg-[var(--primary-hover)] text-[var(--primary-text)] text-[11.5px] font-semibold disabled:opacity-60 cursor-pointer shadow-xs">
                                    Terima
                                </button>
                                <button type="button" :disabled="busy === {{ $c->id }}"
                                        @click="busy = {{ $c->id }}; fetch('{{ route('chat.decline', $c->other) }}', { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' } }).then(r => r.ok && window.location.reload())"
                                        class="px-2.5 py-1.5 rounded-lg border border-rose-800/60 text-rose-400 text-[11.5px] font-semibold hover:bg-rose-950/30 disabled:opacity-60 cursor-pointer">
                                    Hapus
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Active / recent conversations --}}
        <div class="divide-y divide-[var(--border-color)]/60">
            @forelse($conversations as $c)
                @php $isActive = $active === $c->other->username; @endphp
                <a href="{{ route('chat.show', $c->other->username) }}" data-conv="{{ $c->id }}"
                   class="flex items-center gap-3 px-4 py-3 transition relative {{ $isActive ? 'bg-[var(--surface-hover)] border-l-2 border-emerald-400' : 'hover:bg-[var(--surface-subtle)] border-l-2 border-transparent' }}">
                    <x-user-avatar :user="$c->other" size="md" class="shrink-0" />
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center justify-between gap-2">
                            <p class="font-semibold text-[13.5px] text-[var(--primary)] truncate leading-tight">{{ $c->other->name }}</p>
                            @if($c->last)
                                <span data-conv-time class="text-[10.5px] text-[var(--text-faint)] font-mono shrink-0">{{ $c->last->created_at->diffForHumans(null, true) }}</span>
                            @endif
                        </div>
                        <p data-conv-preview class="text-[12px] truncate mt-0.5 {{ $c->unread > 0 ? 'text-[var(--primary)] font-medium' : 'text-[var(--text-muted)]' }}">
                            @if($c->last && $c->last->sender_id === auth()->id())
                                <span class="text-[var(--text-faint)] font-normal">Kamu: </span>
                            @endif
                            {{ $c->last->body ?? 'Mulai percakapan…' }}
                        </p>
                    </div>
                    @if($c->isPending())
                        <span class="shrink-0 text-[10px] font-semibold px-2 py-0.5 rounded-full bg-[var(--surface-muted)] text-[var(--text-muted)] border border-[var(--border-color)]">Menunggu</span>
                    @endif
                    <span data-conv-unread class="shrink-0 min-w-5 h-5 px-1.5 rounded-full bg-emerald-500 text-[var(--primary-text)] text-[10.5px] font-mono font-bold flex items-center justify-center {{ $c->unread > 0 ? '' : 'hidden' }}">{{ $c->unread }}</span>
                </a>
            @empty
                <div class="px-5 py-7 text-center space-y-1.5">
                    <p class="text-[13px] font-semibold text-[var(--primary)]">Belum ada obrolan</p>
                    <p class="text-[11.5px] text-[var(--text-muted)] leading-relaxed">Pilih kreator di bawah atau cari username untuk mulai berdiskusi.</p>
                </div>
            @endforelse
        </div>

        {{-- People search results --}}
        @if($search && ($peopleResults ?? collect())->count() > 0)
            <div class="border-t border-[var(--border-color)]">
                <p class="px-4 py-2 text-[10.5px] font-mono font-semibold uppercase tracking-[0.1em] text-[var(--text-faint)]">Hasil Pencarian Developer</p>
                <div class="divide-y divide-[var(--border-color)]/60">
                    @foreach($peopleResults as $dev)
                        <a href="{{ route('chat.show', $dev->username) }}" class="flex items-center gap-2.5 px-4 py-2.5 hover:bg-[var(--surface-subtle)] transition group">
                            <x-user-avatar :user="$dev" size="sm" />
                            <div class="min-w-0 flex-1">
                                <p class="font-semibold text-[12.5px] text-[var(--primary)] group-hover:text-emerald-400 transition truncate">{{ $dev->name }}</p>
                                <p class="text-[11px] font-mono text-[var(--text-muted)] truncate">&#64;{{ $dev->username }}</p>
                            </div>
                            <span class="text-[11px] font-semibold px-2.5 py-1 rounded-lg bg-[var(--surface-subtle)] border border-[var(--border-color)] text-[var(--text-muted)] group-hover:text-[var(--primary)] group-hover:border-[var(--border-strong)] transition shrink-0">Chat</span>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Suggested creators (fills sparse left pane with actionable contacts) --}}
        @if(! $search && $suggestedPeople->count() > 0)
            <div class="border-t border-[var(--border-color)] mt-1">
                <div class="px-4 py-2.5 flex items-center justify-between">
                    <p class="text-[10.5px] font-mono font-semibold uppercase tracking-[0.1em] text-[var(--text-faint)]">Mulai Obrolan Baru</p>
                    <span class="text-[10px] font-mono text-[var(--text-faint)]">Saran</span>
                </div>
                <div class="divide-y divide-[var(--border-color)]/50">
                    @foreach($suggestedPeople as $dev)
                        <a href="{{ route('chat.show', $dev->username) }}"
                           class="flex items-center gap-2.5 px-4 py-2.5 hover:bg-[var(--surface-subtle)] transition group">
                            <x-user-avatar :user="$dev" size="sm" class="shrink-0" />
                            <div class="min-w-0 flex-1">
                                <p class="font-medium text-[12.5px] text-[var(--primary)] group-hover:text-emerald-400 transition truncate leading-tight">{{ $dev->name }}</p>
                                <p class="text-[11px] font-mono text-[var(--text-muted)] truncate mt-0.5">&#64;{{ $dev->username }}</p>
                            </div>
                            <span class="text-[11px] font-medium px-2 py-0.5 rounded-md bg-[var(--surface-subtle)] border border-[var(--border-color)] text-[var(--text-muted)] group-hover:text-[var(--primary)] group-hover:border-[var(--border-strong)] transition shrink-0">
                                Sapa
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>
