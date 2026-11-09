@extends('layouts.app')

@section('title', 'Obrolan dengan ' . $other->name . ' — NampungYuk')
@section('header_title', 'Obrolan')
@section('main_width', 'max-w-none')
@section('hide_right_rail', '1')
@section('hide_footer', '1')

@section('content')
@php
    $seed = $messages->map(fn ($m) => [
        'id' => $m->id,
        'sender_id' => $m->sender_id,
        'body' => $m->body,
        'time' => $m->created_at->format('H:i'),
        'created_at' => $m->created_at->toIso8601String(),
        'read_at' => $m->read_at?->toIso8601String(),
        'edited_at' => $m->edited_at?->toIso8601String(),
    ])->values();
@endphp

<script>
    window.__chatSeed = @json($seed);
    window.__chatHasMore = @json($hasMore);
</script>

<div x-data="inboxLive({ me: {{ auth()->id() }} })">

    <!-- Mobile back link -->
    <a href="{{ route('chat.index') }}" class="lg:hidden inline-flex items-center gap-1.5 text-xs text-[var(--text-muted)] hover:text-[var(--primary)] transition font-medium mb-3">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        <span>Kembali ke Obrolan</span>
    </a>

    <!-- DESKTOP: 2-pane / MOBILE: conversation only -->
    <div class="lg:grid lg:grid-cols-[20rem_1fr] lg:gap-4 h-[calc(100dvh-9.5rem)] lg:h-[calc(100vh-6.5rem)]">

        <!-- List pane (desktop only) -->
        <div class="hidden lg:flex bg-[var(--surface)] border border-[var(--border-color)] rounded-2xl flex-col overflow-hidden min-h-0 shadow-xs h-full">
            @include('chat._list', ['active' => $other->username])
        </div>

        <!-- Conversation pane -->
        <div class="min-h-0 flex flex-col h-full"
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
                 canRespond: {{ $canRespond ? 'true' : 'false' }}
             })">
            {{-- Message request banner (reactive - ALWAYS kept in DOM for Echo updates) --}}
            <div x-show="isPending" x-cloak class="mb-3 p-3.5 rounded-xl border flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs shrink-0"
                 :class="canRespond ? 'border-amber-300 dark:border-amber-500/30 bg-amber-50 dark:bg-amber-950/30 text-amber-900 dark:text-amber-200'
                                    : 'border-[var(--border-color)] bg-[var(--surface-subtle)] text-[var(--text-muted)]'">
                <div class="flex items-start gap-2.5">
                    <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span x-show="canRespond">
                        <strong>{{ $other->name }}</strong> mengirim pesan, tetapi kamu belum mengikuti akunnya. Terima permintaan ini untuk mulai membalas.
                    </span>
                    <span x-show="!canRespond">
                        Pesanmu menunggu diterima oleh <strong>{{ $other->name }}</strong>. Kamu hanya bisa mengirim satu pesan pembuka sampai permintaan diterima.
                    </span>
                </div>
                <div x-show="canRespond" x-cloak x-data="{ busy: false }" class="flex items-center gap-2 shrink-0">
                    <button type="button" :disabled="busy"
                            @click="busy = true; fetch('{{ route('chat.accept', $other) }}', { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' } }).then(r => r.ok && window.location.reload())"
                            class="px-4 py-2 rounded-lg bg-[var(--primary)] hover:bg-[var(--primary-hover)] text-[var(--primary-text)] font-semibold disabled:opacity-60 cursor-pointer shadow-xs">
                        Terima
                    </button>
                    <button type="button" :disabled="busy"
                            @click="busy = true; fetch('{{ route('chat.decline', $other) }}', { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' } }).then(r => r.ok && (window.location.href = '{{ route('chat.index') }}'))"
                            class="px-4 py-2 rounded-lg border border-rose-200 dark:border-rose-800/60 text-rose-600 dark:text-rose-400 font-semibold hover:bg-rose-50 dark:hover:bg-rose-950/30 disabled:opacity-60 cursor-pointer">
                        Hapus
                    </button>
                </div>
            </div>

            <div class="bg-[var(--surface)] border border-[var(--border-color)] rounded-2xl flex flex-col flex-1 min-h-0 overflow-hidden shadow-xs">
                <!-- Header -->
                <div class="flex items-center gap-3 p-3.5 border-b border-[var(--border-color)] shrink-0">
                    <a href="{{ route('profile.show', $other->username) }}" class="shrink-0 hover:opacity-85 transition">
                        <x-user-avatar :user="$other" size="md" />
                    </a>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2">
                            <p class="font-bold text-sm text-[var(--primary)] truncate">{{ $other->name }}</p>
                            @if($isPending && ! $canRespond)
                                <span class="shrink-0 text-[10px] font-semibold px-1.5 py-0.5 rounded-full bg-[var(--surface-muted)] text-[var(--text-muted)] border border-[var(--border-color)]">Menunggu</span>
                            @endif
                        </div>
                        <p class="text-[11px] text-[var(--text-muted)] font-mono truncate" x-show="!typing">&#64;{{ $other->username }}</p>
                        <p class="text-[11px] text-emerald-600 dark:text-emerald-400 font-medium truncate" x-show="typing" x-cloak
                           x-text="'{{ addslashes($other->name) }} sedang mengetik…'"></p>
                    </div>
                    <a href="{{ route('profile.show', $other->username) }}" class="btn-secondary text-xs py-1.5 px-3 shrink-0">Profil</a>

                    <!-- Conversation actions -->
                    <div class="relative shrink-0" x-data="{ menuOpen: false }" @click.outside="menuOpen = false">
                        <button @click="menuOpen = !menuOpen" type="button" aria-label="Aksi percakapan"
                                :aria-expanded="menuOpen" aria-haspopup="true"
                                class="p-2 rounded-lg border border-[var(--border-color)] bg-[var(--surface)] text-[var(--text-muted)] hover:text-[var(--primary)] transition cursor-pointer">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><circle cx="5" cy="12" r="2"/><circle cx="12" cy="12" r="2"/><circle cx="19" cy="12" r="2"/></svg>
                        </button>
                        <div x-show="menuOpen" x-cloak
                             x-transition:enter="transition ease-out duration-120"
                             x-transition:enter-start="opacity-0 -translate-y-1"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             class="absolute right-0 top-full mt-1.5 z-40 min-w-[200px] rounded-xl bg-[var(--surface)] border border-[var(--border-color)] shadow-lg overflow-hidden text-xs">
                            <button type="button"
                                    @click="menuOpen = false; if (confirm('Bersihkan SEMUA pesan di percakapan ini? Tindakan ini tidak bisa dibatalkan.')) { fetch('{{ route('chat.clear', $other) }}', { method: 'DELETE', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' } }).then(r => r.ok && window.location.reload()) }"
                                    class="w-full flex items-center gap-2.5 px-3.5 py-2.5 text-left text-[var(--primary)] hover:bg-[var(--surface-muted)] transition cursor-pointer">
                                <svg class="w-4 h-4 text-[#6E6D67] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                <span>Bersihkan Semua Pesan</span>
                            </button>
                            <div class="border-t border-[var(--border-color)]"></div>
                            <button type="button"
                                    @click="menuOpen = false; if (confirm('Hapus percakapan ini beserta semua pesannya?')) { fetch('{{ route('chat.destroy', $other) }}', { method: 'DELETE', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' } }).then(r => r.ok && (window.location.href = '{{ route('chat.index') }}')) }"
                                    class="w-full flex items-center gap-2.5 px-3.5 py-2.5 text-left text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/30 transition cursor-pointer">
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                <span>Hapus Percakapan</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Messages container -->
                <div class="flex-1 overflow-y-auto p-4 space-y-1 bg-[#F6F5F0]/60 dark:bg-[#0B0B0D]" x-ref="scroll">
                    <div class="flex justify-center pb-2">
                        <button type="button" x-show="hasMore" x-cloak @click="loadOlder()" :disabled="loadingOlder"
                                class="text-[11px] font-semibold text-[#6E6D67] hover:text-[var(--primary)] transition disabled:opacity-50 cursor-pointer">
                            <span x-show="!loadingOlder">Muat pesan lebih lama</span>
                            <span x-show="loadingOlder" x-cloak>Memuat…</span>
                        </button>
                    </div>

                    <template x-if="messages.length === 0">
                        <div class="h-full flex items-center justify-center text-center text-xs text-[var(--text-muted)]">
                            Belum ada pesan. Mulai obrolan dengan {{ $other->name }}.
                        </div>
                    </template>

                    <template x-for="(m, i) in messages" :key="m.id">
                        <div>
                            <template x-if="showDay(i)">
                                <div class="flex items-center gap-3 py-3">
                                    <div class="flex-1 h-px bg-[var(--border-color)]"></div>
                                    <span class="text-[10px] font-mono text-[var(--text-faint)] shrink-0" x-text="dayLabel(m)"></span>
                                    <div class="flex-1 h-px bg-[var(--border-color)]"></div>
                                </div>
                            </template>

                            <div class="flex group" :class="m.mine ? 'justify-end' : 'justify-start'">
                                <!-- Actions (own messages only) - Accessible on touchscreens via opacity-100 sm:opacity-0 sm:group-hover:opacity-100 -->
                                <div x-show="m.mine && editingId !== m.id" x-cloak
                                     class="self-center mr-1.5 flex items-center gap-1 opacity-100 sm:opacity-0 sm:group-hover:opacity-100 sm:focus-within:opacity-100 transition shrink-0">
                                    <button type="button" @click="startEdit(m)"
                                            class="text-[#908F88] hover:text-[var(--primary)] p-1 rounded cursor-pointer"
                                            title="Ubah pesan" aria-label="Ubah pesan">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>
                                    <button type="button" @click="removeMessage(m.id)"
                                            class="text-[#908F88] hover:text-rose-500 p-1 rounded cursor-pointer"
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
                                        <button type="button" @click="cancelEdit()" class="text-[11px] font-semibold text-[#6E6D67] hover:text-[var(--primary)] transition cursor-pointer">Batal</button>
                                        <button type="button" @click="saveEdit(m.id)" :disabled="savingEdit || !editDraft.trim()"
                                                class="text-[11px] font-semibold text-[var(--primary)] hover:underline disabled:opacity-50 cursor-pointer">Simpan</button>
                                    </div>
                                </div>

                                <div x-show="editingId !== m.id" class="max-w-[75%] px-3.5 py-2 rounded-2xl text-sm leading-relaxed shadow-xs"
                                     :class="m.mine
                                        ? 'bg-[#EAE8E1] text-[#0B0B0D] font-medium rounded-br-md'
                                        : 'bg-[#161619] text-[#EAE8E1] border border-[#242426] rounded-bl-md'">
                                    <p class="whitespace-pre-line" x-text="m.body"></p>
                                    <div class="flex items-center justify-end gap-1 mt-1">
                                        <span class="text-[10px] font-mono"
                                              :class="m.mine ? 'text-[#6E6D67]' : 'text-[#8E8D86]'"
                                              x-text="m.edited_at ? 'diubah · ' + m.time : m.time"></span>
                                        <template x-if="m.mine">
                                            <span class="text-[10px] leading-none" :title="m.read_at ? 'Dibaca' : 'Terkirim'">
                                                <template x-if="!m.read_at">
                                                    <svg class="w-3 h-3 text-[#6E6D67]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                                </template>
                                                <template x-if="m.read_at">
                                                    <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M2 13l4 4L13 10m2 7l6-6"/></svg>
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
                <form x-show="canSend" x-cloak @submit.prevent="send()" class="flex items-center gap-2 p-3 border-t border-[var(--border-color)] shrink-0 bg-[var(--surface)]">
                    <input type="text" x-model="draft" maxlength="2000" placeholder="Tulis pesan..."
                           class="ny-input text-sm flex-1 bg-[var(--surface-subtle)] border border-[var(--border-color)] text-[var(--primary)] placeholder-[#908F88] focus:border-[var(--primary)]" :disabled="sending"
                           @input="pingTyping()">
                    <button type="submit" class="text-xs py-2.5 px-4 shrink-0 cursor-pointer rounded-xl bg-[var(--primary)] hover:bg-[var(--primary-hover)] text-[var(--primary-text)] font-semibold transition disabled:opacity-50 shadow-xs" :disabled="sending || !draft.trim()">
                        <span x-show="!sending">Kirim</span>
                        <span x-show="sending" x-cloak>…</span>
                    </button>
                </form>
                <div x-show="!canSend" x-cloak class="p-3 border-t border-[var(--border-color)] shrink-0 text-center text-xs text-[var(--text-muted)] bg-[var(--surface-subtle)]">
                    <span x-show="canRespond">Terima permintaan pesan di atas untuk mulai membalas.</span>
                    <span x-show="!canRespond">Menunggu {{ $other->name }} menerima permintaan pesanmu.</span>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
