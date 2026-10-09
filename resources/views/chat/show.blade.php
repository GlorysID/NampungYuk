@extends('layouts.app')

@section('title', 'Obrolan dengan ' . $other->name . ' — NampungYuk')

@section('content')
<div class="max-w-2xl mx-auto space-y-4">

    <a href="{{ route('chat.index') }}" class="inline-flex items-center gap-1.5 text-xs text-[#63636b] dark:text-[#a0a0a0] hover:text-[#0070f3] transition font-medium">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        <span>Kembali ke Obrolan</span>
    </a>

    <div class="ny-card flex flex-col h-[calc(100vh-14rem)] overflow-hidden"
         x-data="chatRoom({
             conversationId: {{ $conversation->id }},
             me: {{ auth()->id() }},
             sendUrl: '{{ route('chat.send', $other) }}'
         })">
        <!-- Header -->
        <div class="flex items-center gap-3 p-3.5 border-b border-[#e4e4e7] dark:border-[#1f1f1f] shrink-0">
            <x-user-avatar :user="$other" size="md" />
            <div class="min-w-0 flex-1">
                <p class="font-bold text-sm text-[#18181b] dark:text-[#fafafa] truncate">{{ $other->name }}</p>
                <p class="text-[11px] text-[#63636b] dark:text-[#a0a0a0] font-mono truncate">&#64;{{ $other->username }}</p>
            </div>
            <a href="{{ route('profile.show', $other->username) }}" class="btn-secondary text-xs py-1.5 px-3 shrink-0">Profil</a>
        </div>

        <!-- Messages -->
        <div class="flex-1 overflow-y-auto p-4 space-y-3" x-ref="scroll">
            @forelse($messages as $m)
                <div class="flex {{ $m->sender_id === auth()->id() ? 'justify-end' : 'justify-start' }}">
                    <div class="max-w-[75%] px-3.5 py-2 rounded-2xl text-sm leading-relaxed {{ $m->sender_id === auth()->id() ? 'bg-[#0070f3] dark:bg-[#3291ff] text-white dark:text-[#000000] rounded-br-md' : 'bg-[#eeeeef] dark:bg-[#171717] text-[#18181b] dark:text-[#fafafa] rounded-bl-md' }}">
                        <p class="whitespace-pre-line">{{ $m->body }}</p>
                        <p class="text-[10px] mt-1 {{ $m->sender_id === auth()->id() ? 'text-white/70 dark:text-black/60' : 'text-[#8f8f8f] dark:text-[#666666]' }} font-mono">{{ $m->created_at->format('H:i') }}</p>
                    </div>
                </div>
            @empty
                <div class="h-full flex items-center justify-center text-center text-xs text-[#63636b] dark:text-[#a0a0a0]">
                    Belum ada pesan. Mulai percakapan dengan {{ $other->name }}.
                </div>
            @endforelse

            <template x-for="m in incoming" :key="m.id">
                <div class="flex" :class="m.mine || m.sender_id === me ? 'justify-end' : 'justify-start'">
                    <div class="max-w-[75%] px-3.5 py-2 rounded-2xl text-sm leading-relaxed"
                         :class="(m.mine || m.sender_id === me)
                            ? 'bg-[#0070f3] dark:bg-[#3291ff] text-white dark:text-[#000000] rounded-br-md'
                            : 'bg-[#eeeeef] dark:bg-[#171717] text-[#18181b] dark:text-[#fafafa] rounded-bl-md'">
                        <p class="whitespace-pre-line" x-text="m.body"></p>
                        <p class="text-[10px] mt-1 font-mono"
                           :class="(m.mine || m.sender_id === me) ? 'text-white/70 dark:text-black/60' : 'text-[#8f8f8f] dark:text-[#666666]'"
                           x-text="m.time"></p>
                    </div>
                </div>
            </template>
        </div>

        <!-- Composer -->
        <form @submit.prevent="send()" class="flex items-center gap-2 p-3 border-t border-[#e4e4e7] dark:border-[#1f1f1f] shrink-0">
            <input type="text" x-model="draft" maxlength="2000" placeholder="Tulis pesan..." class="ny-input text-sm flex-1" :disabled="sending">
            <button type="submit" class="btn-primary text-xs py-2.5 px-4 shrink-0" :disabled="sending || !draft.trim()">
                <span x-show="!sending">Kirim</span>
                <span x-show="sending" x-cloak>...</span>
            </button>
        </form>
    </div>

</div>
@endsection
