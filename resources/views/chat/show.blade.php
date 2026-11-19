@extends('layouts.app')

@section('title', 'Obrolan dengan ' . $other->name . ' — NampungYuk')
@section('header_title', 'Obrolan')
@section('main_width', 'max-w-none')
@section('hide_right_rail', '1')
@section('hide_footer', '1')

@section('content')
@php
    $showReadReceipts = $showReadReceipts ?? true;
    $seed = $messages->map(fn ($m) => [
        'id' => $m->id,
        'sender_id' => $m->sender_id,
        'body' => $m->body,
        'time' => $m->created_at->format('H:i'),
        'created_at' => $m->created_at->toIso8601String(),
        'read_at' => $showReadReceipts ? $m->read_at?->toIso8601String() : null,
        'edited_at' => $m->edited_at?->toIso8601String(),
    ])->values();
@endphp

<script>
    window.__chatSeed = @json($seed);
    window.__chatHasMore = @json($hasMore);
    window.__activeChatConvId = @json($conversationId);
</script>

<div x-data="inboxLive({ me: {{ auth()->id() }} })">

    <!-- Mobile back link -->
    <a href="{{ route('chat.index') }}" class="lg:hidden inline-flex items-center gap-1.5 text-xs text-[var(--text-muted)] hover:text-[var(--primary)] transition font-medium mb-3">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        <span>Kembali ke Obrolan</span>
    </a>

    <!-- DESKTOP: unified 2-pane surface / MOBILE: conversation only -->
    <div class="bg-[var(--surface)] border border-[var(--border-color)] rounded-2xl overflow-hidden shadow-xs lg:grid lg:grid-cols-[20.5rem_1fr] lg:divide-x lg:divide-[var(--border-color)] h-[calc(100dvh-9.5rem)] lg:h-[calc(100vh-6.25rem)]">

        <!-- List pane (desktop only) -->
        <div class="hidden lg:flex flex-col overflow-hidden min-h-0 h-full">
            @include('chat._list', ['active' => $other->username])
        </div>

        <!-- Conversation pane -->
        <div class="min-h-0 flex flex-col h-full overflow-hidden"
             x-data="chatRoom({
                 conversationId: {{ $conversationId ?? 'null' }},
                 me: {{ auth()->id() }},
                 sendUrl: '{{ route('chat.send', $other) }}',
                 typingUrl: '{{ route('chat.typing', $other) }}',
                 olderUrl: '{{ route('chat.older', $other) }}',
                 deleteUrl: '{{ url('obrolan/pesan') }}/__ID__',
                 editUrl: '{{ url('obrolan/pesan') }}/__ID__',
                 otherName: '{{ addslashes($other->name) }}',
                 isPending: {{ $isPending ? 'true' : 'false' }},
                 canSend: {{ $canSend ? 'true' : 'false' }},
                 canRespond: {{ $canRespond ? 'true' : 'false' }},
                 blockedByPolicy: {{ $blockedByPolicy ? 'true' : 'false' }}
             })">

            <!-- Header -->
            <div class="flex items-center gap-3 px-4 py-3 border-b border-[var(--border-color)] bg-[var(--surface)] shrink-0">
                <a href="{{ route('profile.show', $other->username) }}" class="shrink-0 hover:opacity-85 transition">
                    <x-user-avatar :user="$other" size="md" />
                </a>
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2">
                        <a href="{{ route('profile.show', $other->username) }}" class="font-bold text-[14px] text-[var(--primary)] hover:text-emerald-400 transition truncate">
                            {{ $other->name }}
                        </a>
                        <span x-show="isPending && !canRespond" x-cloak
                              class="shrink-0 text-[10px] font-semibold px-2 py-0.5 rounded-full bg-[var(--surface-muted)] text-[var(--text-muted)] border border-[var(--border-color)]">
                            Menunggu izin
                        </span>
                    </div>
                    <p class="text-[11.5px] text-[var(--text-muted)] font-mono truncate" x-show="!typing">&#64;{{ $other->username }}</p>
                    <p class="text-[11.5px] text-emerald-400 font-medium truncate" x-show="typing" x-cloak
                       x-text="'{{ addslashes($other->name) }} sedang mengetik…'"></p>
                </div>

                <a href="{{ route('profile.show', $other->username) }}"
                   class="h-8 px-3 rounded-lg border border-[var(--border-color)] bg-[var(--surface-subtle)] hover:bg-[var(--surface-hover)] hover:border-[var(--border-strong)] text-xs font-semibold text-[var(--primary)] inline-flex items-center gap-1.5 transition shrink-0">
                    <span>Profil</span>
                </a>

                <!-- Conversation actions -->
                <div class="relative shrink-0" x-data="{ menuOpen: false }" @click.outside="menuOpen = false">
                    <button @click="menuOpen = !menuOpen" type="button" aria-label="Aksi percakapan"
                            :aria-expanded="menuOpen" aria-haspopup="true"
                            class="w-8 h-8 rounded-lg border border-[var(--border-color)] bg-[var(--surface-subtle)] hover:bg-[var(--surface-hover)] text-[var(--text-muted)] hover:text-[var(--primary)] flex items-center justify-center transition cursor-pointer">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><circle cx="5" cy="12" r="2"/><circle cx="12" cy="12" r="2"/><circle cx="19" cy="12" r="2"/></svg>
                    </button>
                    <div x-show="menuOpen" x-cloak
                         x-transition:enter="transition ease-out duration-120"
                         x-transition:enter-start="opacity-0 -translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         class="absolute right-0 top-full mt-1.5 z-40 min-w-[200px] rounded-xl bg-[var(--surface)] border border-[var(--border-color)] shadow-xl overflow-hidden text-xs">
                        <button type="button"
                                @click="menuOpen = false; if (confirm('Bersihkan SEMUA pesan di percakapan ini? Tindakan ini tidak bisa dibatalkan.')) { fetch('{{ route('chat.clear', $other) }}', { method: 'DELETE', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' } }).then(r => r.ok && window.location.reload()) }"
                                class="w-full flex items-center gap-2.5 px-3.5 py-2.5 text-left text-[var(--primary)] hover:bg-[var(--surface-hover)] transition cursor-pointer">
                            <svg class="w-4 h-4 text-[var(--text-muted)] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            <span>Bersihkan Semua Pesan</span>
                        </button>
                        <div class="border-t border-[var(--border-color)]"></div>
                        <button type="button"
                                @click="menuOpen = false; if (confirm('Hapus percakapan ini beserta semua pesannya?')) { fetch('{{ route('chat.destroy', $other) }}', { method: 'DELETE', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' } }).then(r => r.ok && (window.location.href = '{{ route('chat.index') }}')) }"
                                class="w-full flex items-center gap-2.5 px-3.5 py-2.5 text-left text-rose-400 hover:bg-rose-950/30 transition cursor-pointer">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            <span>Hapus Percakapan</span>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Message request banner (reactive - ALWAYS kept in DOM for Echo updates) --}}
            <div x-show="isPending" x-cloak class="px-4 py-3 border-b flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs shrink-0"
                 :class="canRespond ? 'border-amber-500/30 bg-amber-950/30 text-amber-200'
                                    : 'border-[var(--border-color)] bg-[var(--surface-subtle)] text-[var(--text-muted)]'">
                <div class="flex items-start gap-2.5">
                    <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span x-show="canRespond">
                        <strong>{{ $other->name }}</strong> mengirim permintaan pesan. Terima permintaan ini untuk mulai membalas.
                    </span>
                    <span x-show="!canRespond">
                        Pesanmu menunggu diterima oleh <strong>{{ $other->name }}</strong>. Kamu hanya bisa mengirim satu pesan pembuka sampai permintaan diterima.
                    </span>
                </div>
                <div x-show="canRespond" x-cloak x-data="{ busy: false }" class="flex items-center gap-2 shrink-0">
                    <button type="button" :disabled="busy"
                            @click="busy = true; fetch('{{ route('chat.accept', $other) }}', { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' } }).then(r => r.ok && window.location.reload())"
                            class="px-3.5 py-1.5 rounded-lg bg-[var(--primary)] hover:bg-[var(--primary-hover)] text-[var(--primary-text)] font-semibold disabled:opacity-60 cursor-pointer shadow-xs">
                        Terima
                    </button>
                    <button type="button" :disabled="busy"
                            @click="busy = true; fetch('{{ route('chat.decline', $other) }}', { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' } }).then(r => r.ok && (window.location.href = '{{ route('chat.index') }}'))"
                            class="px-3.5 py-1.5 rounded-lg border border-rose-800/60 text-rose-400 font-semibold hover:bg-rose-950/30 disabled:opacity-60 cursor-pointer">
                        Hapus
                    </button>
                </div>
            </div>

            <!-- Messages container (subtle dot-grid canvas + profile intro header) -->
            <div class="flex-1 overflow-y-auto px-4 sm:px-6 py-5 space-y-2 bg-[var(--bg-app)] bg-[radial-gradient(rgba(255,255,255,0.035)_1px,transparent_1px)] [background-size:20px_20px]" x-ref="scroll">
                <div class="flex justify-center">
                    <button type="button" x-show="hasMore" x-cloak @click="loadOlder()" :disabled="loadingOlder"
                            class="mb-2 px-3 py-1 rounded-full bg-[var(--surface)] border border-[var(--border-color)] text-[11px] font-semibold text-[var(--text-muted)] hover:text-[var(--primary)] hover:border-[var(--border-strong)] transition disabled:opacity-50 cursor-pointer">
                        <span x-show="!loadingOlder">Muat pesan lebih lama</span>
                        <span x-show="loadingOlder" x-cloak>Memuat…</span>
                    </button>
                </div>

                <!-- Conversation Profile Intro Card (fills empty top space on short threads) -->
                <div x-show="!hasMore" class="flex flex-col items-center text-center pt-3 pb-6 mb-2 border-b border-[var(--border-color)]/60">
                    <a href="{{ route('profile.show', $other->username) }}" class="hover:opacity-90 transition">
                        <x-user-avatar :user="$other" size="lg" />
                    </a>
                    <a href="{{ route('profile.show', $other->username) }}" class="mt-3 text-[16px] font-bold text-[var(--primary)] hover:text-emerald-400 transition leading-tight">
                        {{ $other->name }}
                    </a>
                    <p class="text-[12px] font-mono text-[var(--text-muted)] mt-0.5">&#64;{{ $other->username }}</p>
                    @if($other->bio)
                        <p class="text-[12.5px] text-[var(--text-muted)] max-w-sm mt-2 leading-relaxed line-clamp-2">{{ $other->bio }}</p>
                    @endif
                    <div class="flex items-center justify-center flex-wrap gap-2 mt-3 text-[11px] font-mono text-[var(--text-faint)]">
                        <span class="px-2 py-0.5 rounded-md bg-[var(--surface)] border border-[var(--border-color)]">
                            <strong class="text-[var(--primary)]">{{ number_format($other->projects_count ?? 0) }}</strong> karya
                        </span>
                        <span class="px-2 py-0.5 rounded-md bg-[var(--surface)] border border-[var(--border-color)]">
                            <strong class="text-[var(--primary)]">{{ number_format($other->followers_count ?? 0) }}</strong> pengikut
                        </span>
                        <span class="px-2 py-0.5 rounded-md bg-[var(--surface)] border border-[var(--border-color)]">
                            Bergabung {{ $other->created_at?->translatedFormat('M Y') }}
                        </span>
                    </div>
                </div>

                <!-- Quick starter prompts when no messages yet -->
                <template x-if="messages.length === 0">
                    <div class="py-4 flex flex-col items-center text-center space-y-3">
                        <p class="text-xs text-[var(--text-muted)]">
                            Belum ada pesan. Mulai obrolan dengan <strong class="text-[var(--primary)]">{{ $other->name }}</strong>.
                        </p>
                        <div x-show="canSend" class="flex flex-wrap items-center justify-center gap-2 max-w-md">
                            @foreach(['Halo, salam kenal!', 'Keren banget karyanya!', 'Boleh diskusi soal tech stack?'] as $starter)
                                <button type="button"
                                        @click="draft = @js($starter); $nextTick(() => $refs.composerInput?.focus())"
                                        class="px-3 py-1.5 rounded-full bg-[var(--surface)] border border-[var(--border-color)] hover:border-emerald-500/40 hover:text-emerald-400 text-[12px] text-[var(--text-muted)] transition cursor-pointer">
                                    {{ $starter }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                </template>

                <template x-for="(m, i) in messages" :key="m.id">
                    <div>
                        <template x-if="showDay(i)">
                            <div class="flex items-center justify-center py-3">
                                <span class="px-3 py-0.5 rounded-full bg-[var(--surface)] border border-[var(--border-color)] text-[10.5px] font-mono text-[var(--text-faint)]" x-text="dayLabel(m)"></span>
                            </div>
                        </template>

                        <div class="flex items-end gap-2 group" :class="m.mine ? 'justify-end' : 'justify-start'">
                            <!-- Mini avatar for incoming messages -->
                            <template x-if="!m.mine">
                                <div class="shrink-0 mb-0.5">
                                    <x-user-avatar :user="$other" size="xs" />
                                </div>
                            </template>

                            <!-- Actions (own messages only) -->
                            <div x-show="m.mine && editingId !== m.id" x-cloak
                                 class="self-center flex items-center gap-0.5 opacity-100 sm:opacity-0 sm:group-hover:opacity-100 sm:focus-within:opacity-100 transition shrink-0">
                                <button type="button" @click="startEdit(m)"
                                        class="text-[var(--text-faint)] hover:text-[var(--primary)] hover:bg-[var(--surface)] p-1.5 rounded-lg transition cursor-pointer"
                                        title="Ubah pesan" aria-label="Ubah pesan">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>
                                <button type="button" @click="removeMessage(m.id)"
                                        class="text-[var(--text-faint)] hover:text-rose-400 hover:bg-rose-950/30 p-1.5 rounded-lg transition cursor-pointer"
                                        title="Hapus pesan" aria-label="Hapus pesan">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>

                            <!-- Inline edit box -->
                            <div x-show="editingId === m.id" x-cloak class="w-full max-w-[75%] flex flex-col items-end gap-1.5">
                                <textarea x-ref="editInput" x-model="editDraft" rows="2" maxlength="2000"
                                          @keydown.enter.prevent="saveEdit(m.id)"
                                          @keydown.escape="cancelEdit()"
                                          class="ny-input text-sm w-full resize-none"></textarea>
                                <div class="flex items-center gap-2">
                                    <button type="button" @click="cancelEdit()" class="text-[11px] font-semibold text-[var(--text-muted)] hover:text-[var(--primary)] transition cursor-pointer">Batal</button>
                                    <button type="button" @click="saveEdit(m.id)" :disabled="savingEdit || !editDraft.trim()"
                                            class="text-[11px] font-semibold text-[var(--primary)] hover:underline disabled:opacity-50 cursor-pointer">Simpan</button>
                                </div>
                            </div>

                            <!-- Message bubble -->
                            <div x-show="editingId !== m.id" class="max-w-[74%] px-3.5 py-2.5 rounded-2xl text-[13.5px] leading-relaxed shadow-xs"
                                 :class="m.mine
                                    ? 'bg-[var(--primary)] text-[var(--primary-text)] font-medium rounded-br-sm'
                                    : 'bg-[var(--surface)] text-[var(--primary)] border border-[var(--border-color)] rounded-bl-sm'">
                                <p class="whitespace-pre-line break-words" x-text="m.body"></p>
                                <div class="flex items-center justify-end gap-1 mt-1">
                                    <span class="text-[10px] font-mono tabular-nums"
                                          :class="m.mine ? 'opacity-65' : 'text-[var(--text-faint)]'"
                                          x-text="m.edited_at ? 'diubah · ' + m.time : m.time"></span>
                                    <template x-if="m.mine">
                                        <span class="text-[10px] leading-none" :title="m.read_at ? 'Dibaca' : 'Terkirim'">
                                            <template x-if="!m.read_at">
                                                <svg class="w-3 h-3 opacity-65" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                            </template>
                                            <template x-if="m.read_at">
                                                <svg class="w-3.5 h-3.5 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M2 13l4 4L13 10m2 7l6-6"/></svg>
                                            </template>
                                        </span>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Composer (locked reactively until a pending request is accepted) -->
            <form x-show="canSend" x-cloak @submit.prevent="send()" class="p-3.5 border-t border-[var(--border-color)] shrink-0 bg-[var(--surface)]">
                <div class="flex items-center gap-2 rounded-2xl bg-[var(--surface-subtle)] border border-[var(--border-color)] focus-within:border-[var(--border-strong)] pl-4 pr-1.5 py-1.5 transition">
                    <input type="text"
                           x-ref="composerInput"
                           x-model="draft"
                           maxlength="2000"
                           placeholder="Tulis pesan untuk {{ $other->name }}..."
                           class="flex-1 bg-transparent text-[13.5px] text-[var(--primary)] placeholder:text-[var(--text-faint)] outline-none py-1"
                           :disabled="sending"
                           @input="pingTyping()">
                    <button type="submit"
                            :disabled="sending || !draft.trim()"
                            aria-label="Kirim pesan"
                            class="h-9 px-4 shrink-0 inline-flex items-center gap-1.5 cursor-pointer rounded-xl bg-[var(--primary)] hover:bg-[var(--primary-hover)] text-[var(--primary-text)] text-xs font-bold transition disabled:opacity-35 disabled:cursor-not-allowed shadow-xs">
                        <span x-show="!sending">Kirim</span>
                        <span x-show="sending" x-cloak>…</span>
                        <svg x-show="!sending" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M5 12h14M13 5l7 7-7 7"/>
                        </svg>
                    </button>
                </div>
            </form>
            <div x-show="!canSend" x-cloak class="p-3.5 border-t border-[var(--border-color)] shrink-0 text-center text-xs text-[var(--text-muted)] bg-[var(--surface-subtle)]">
                <span x-show="canRespond">Terima permintaan pesan di atas untuk mulai membalas.</span>
                <span x-show="blockedByPolicy"><strong>{{ $other->name }}</strong> tidak dapat menerima pesan saat ini.</span>
                <span x-show="!canRespond && !blockedByPolicy">Menunggu <strong>{{ $other->name }}</strong> menerima permintaan pesanmu.</span>
            </div>
        </div>
    </div>

</div>
@endsection
