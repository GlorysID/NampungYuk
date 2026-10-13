@extends('layouts.app')

@section('title', 'Obrolan dengan ' . $other->name . ' — NampungYuk')

@section('content')
@php
    $seed = $messages->map(fn ($m) => [
        'id' => $m->id,
        'sender_id' => $m->sender_id,
        'body' => $m->body,
        'time' => $m->created_at->format('H:i'),
        'created_at' => $m->created_at->toIso8601String(),
        'read_at' => $m->read_at?->toIso8601String(),
    ])->values();
@endphp

<script>
    window.__chatSeed = @json($seed);
    window.__chatHasMore = @json($hasMore);
</script>

<div x-data="inboxLive({ me: {{ auth()->id() }} })">

    <!-- Mobile back link -->
    <a href="{{ route('chat.index') }}" class="lg:hidden inline-flex items-center gap-1.5 text-xs text-[#63636b] dark:text-[#a0a0a0] hover:text-[#0070f3] transition font-medium mb-3">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        <span>Kembali ke Obrolan</span>
    </a>

    <!-- DESKTOP: 2-pane / MOBILE: conversation only -->
    <div class="lg:grid lg:grid-cols-[20rem_1fr] lg:gap-4 lg:h-[calc(100vh-9rem)]">

        <!-- List pane (desktop only) -->
        <div class="hidden lg:flex ny-card flex-col overflow-hidden min-h-0">
            @include('chat._list', ['active' => $other->username])
        </div>

        <!-- Conversation pane -->
        <div class="min-h-0 flex flex-col">
            {{-- Message request banner --}}
            @if($isPending)
                <div class="mb-3 p-3.5 rounded-xl border flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs
                            {{ $canRespond ? 'border-amber-300 dark:border-amber-500/30 bg-amber-50 dark:bg-amber-950/30 text-amber-900 dark:text-amber-200'
                                           : 'border-[#e4e4e7] dark:border-[#1f1f1f] bg-[#f7f7f8] dark:bg-[#111111] text-[#63636b] dark:text-[#a0a0a0]' }}"
                     x-data="{ busy: false }">
                    <div class="flex items-start gap-2.5">
                        <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <span>
                            @if($canRespond)
                                <strong>{{ $other->name }}</strong> ingin mengirim pesan. Kamu belum saling mengikuti, jadi ini masuk sebagai <strong>permintaan pesan</strong>.
                            @else
                                Pesanmu menunggu diterima oleh <strong>{{ $other->name }}</strong>. Kamu hanya bisa mengirim satu pesan pembuka sampai permintaan diterima.
                            @endif
                        </span>
                    </div>
                    @if($canRespond)
                        <div class="flex items-center gap-2 shrink-0">
                            <button type="button" :disabled="busy"
                                    @click="busy = true; fetch('{{ route('chat.accept', $other) }}', { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' } }).then(r => r.ok && window.location.reload())"
                                    class="px-4 py-2 rounded-lg bg-[#0070f3] hover:bg-[#0761d1] text-white font-semibold disabled:opacity-60">
                                Terima
                            </button>
                            <button type="button" :disabled="busy"
                                    @click="busy = true; fetch('{{ route('chat.decline', $other) }}', { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' } }).then(r => r.ok && (window.location.href = '{{ route('chat.index') }}'))"
                                    class="px-4 py-2 rounded-lg border border-rose-300 dark:border-rose-800/60 text-rose-600 dark:text-rose-400 font-semibold hover:bg-rose-50 dark:hover:bg-rose-950/30 disabled:opacity-60">
                                Hapus
                            </button>
                        </div>
                    @endif
                </div>
            @endif

            <div class="ny-card flex flex-col flex-1 min-h-0 overflow-hidden"
                 x-data="chatRoom({
                     conversationId: {{ $conversationId ?? 'null' }},
                     me: {{ auth()->id() }},
                     sendUrl: '{{ route('chat.send', $other) }}',
                     typingUrl: '{{ route('chat.typing', $other) }}',
                     olderUrl: '{{ route('chat.older', $other) }}',
                     deleteUrl: '{{ url('obrolan/pesan') }}/__ID__',
                     otherName: '{{ addslashes($other->name) }}'
                 })">
                <!-- Header -->
                <div class="flex items-center gap-3 p-3.5 border-b border-[#e4e4e7] dark:border-[#1f1f1f] shrink-0">
                    <a href="{{ route('profile.show', $other->username) }}" class="shrink-0 hover:opacity-85 transition">
                        <x-user-avatar :user="$other" size="md" />
                    </a>
                    <div class="min-w-0 flex-1">
                        <p class="font-bold text-sm text-[#18181b] dark:text-[#fafafa] truncate">{{ $other->name }}</p>
                        <p class="text-[11px] text-[#63636b] dark:text-[#a0a0a0] font-mono truncate" x-show="!typing">&#64;{{ $other->username }}</p>
                        <p class="text-[11px] text-[#0070f3] dark:text-[#3291ff] font-medium truncate" x-show="typing" x-cloak
                           x-text="'{{ addslashes($other->name) }} sedang mengetik…'"></p>
                    </div>
                    <a href="{{ route('profile.show', $other->username) }}" class="btn-secondary text-xs py-1.5 px-3 shrink-0">Profil</a>
                </div>

                <!-- Messages -->
                <div class="flex-1 overflow-y-auto p-4 space-y-1 bg-[#fafafa] dark:bg-[#060606]" x-ref="scroll">
                    <div class="flex justify-center pb-2">
                        <button type="button" x-show="hasMore" x-cloak @click="loadOlder()" :disabled="loadingOlder"
                                class="text-[11px] font-semibold text-[#63636b] dark:text-[#a0a0a0] hover:text-[#0070f3] dark:hover:text-[#3291ff] transition disabled:opacity-50">
                            <span x-show="!loadingOlder">Muat pesan lama</span>
                            <span x-show="loadingOlder" x-cloak>Memuat…</span>
                        </button>
                    </div>

                    <template x-if="messages.length === 0">
                        <div class="h-full flex items-center justify-center text-center text-xs text-[#63636b] dark:text-[#a0a0a0]">
                            Belum ada pesan. Mulai percakapan dengan {{ $other->name }}.
                        </div>
                    </template>

                    <template x-for="(m, i) in messages" :key="m.id">
                        <div>
                            <template x-if="showDay(i)">
                                <div class="flex items-center gap-3 py-3">
                                    <div class="flex-1 h-px bg-[#e4e4e7] dark:bg-[#1f1f1f]"></div>
                                    <span class="text-[10px] font-mono text-[#63636b] dark:text-[#a0a0a0] shrink-0" x-text="dayLabel(m)"></span>
                                    <div class="flex-1 h-px bg-[#e4e4e7] dark:bg-[#1f1f1f]"></div>
                                </div>
                            </template>

                            <div class="flex group" :class="m.mine ? 'justify-end' : 'justify-start'">
                                <button type="button" x-show="m.mine" x-cloak @click="removeMessage(m.id)"
                                        class="self-center mr-1.5 opacity-0 group-hover:opacity-100 text-[#63636b] dark:text-[#a0a0a0] hover:text-rose-500 transition shrink-0"
                                        title="Hapus pesan" aria-label="Hapus pesan">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>

                                <div class="max-w-[75%] px-3.5 py-2 rounded-2xl text-sm leading-relaxed shadow-sm"
                                     :class="m.mine
                                        ? 'bg-[#0070f3] dark:bg-[#3291ff] text-white dark:text-[#000000] rounded-br-md'
                                        : 'bg-white dark:bg-[#171717] text-[#18181b] dark:text-[#fafafa] rounded-bl-md border border-[#eaeaea] dark:border-[#1f1f1f]'">
                                    <p class="whitespace-pre-line" x-text="m.body"></p>
                                    <div class="flex items-center justify-end gap-1 mt-1">
                                        <span class="text-[10px] font-mono"
                                              :class="m.mine ? 'text-white/70 dark:text-black/60' : 'text-[#8f8f8f] dark:text-[#666666]'"
                                              x-text="m.time"></span>
                                        <template x-if="m.mine">
                                            <span class="text-[10px] leading-none" :title="m.read_at ? 'Dibaca' : 'Terkirim'">
                                                <template x-if="!m.read_at">
                                                    <svg class="w-3 h-3 text-white/70 dark:text-black/60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                                </template>
                                                <template x-if="m.read_at">
                                                    <svg class="w-3.5 h-3.5 text-emerald-200 dark:text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M2 13l4 4L13 10m2 7l6-6"/></svg>
                                                </template>
                                            </span>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Composer (locked until a pending request is accepted) -->
                @if($canSend)
                    <form @submit.prevent="send()" class="flex items-center gap-2 p-3 border-t border-[#e4e4e7] dark:border-[#1f1f1f] shrink-0">
                        <input type="text" x-model="draft" maxlength="2000" placeholder="Tulis pesan..."
                               class="ny-input text-sm flex-1" :disabled="sending"
                               @input="pingTyping()">
                        <button type="submit" class="btn-primary text-xs py-2.5 px-4 shrink-0" :disabled="sending || !draft.trim()">
                            <span x-show="!sending">Kirim</span>
                            <span x-show="sending" x-cloak>…</span>
                        </button>
                    </form>
                @else
                    <div class="p-3 border-t border-[#e4e4e7] dark:border-[#1f1f1f] shrink-0 text-center text-xs text-[#63636b] dark:text-[#a0a0a0] bg-[#fafafa] dark:bg-[#060606]">
                        @if($canRespond)
                            Terima permintaan pesan di atas untuk mulai membalas.
                        @else
                            Menunggu {{ $other->name }} menerima permintaan pesanmu.
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>

</div>
@endsection
