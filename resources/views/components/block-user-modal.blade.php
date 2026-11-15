{{-- Block confirmation modal + inline undo snackbar. In-place: no page reload, no redirect to /pengaturan. --}}
<div x-data="{
        open: false,
        username: '',
        displayName: '',
        loading: false,
        error: '',
        undo: { show: false, id: null, username: '', message: '', busy: false, timeout: null },
        init() {
            window.addEventListener('open-block-user-modal', (e) => {
                this.username = e.detail?.username || '';
                this.displayName = e.detail?.name || '';
                this.error = '';
                this.loading = false;
                this.open = true;
                this.$nextTick(() => this.$refs.blockConfirm?.focus());
            });

            window.openBlockUserModal = (detail) => {
                @guest
                    if (window.openAuthModal) { window.openAuthModal('login'); } else { window.location.href = '{{ route('login') }}'; }
                    return;
                @endguest
                window.dispatchEvent(new CustomEvent('open-block-user-modal', { detail }));
            };
        },
        async send(url, params) {
            const res = await fetch(url, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                },
                body: new URLSearchParams({ ...params, _token: '{{ csrf_token() }}' }),
            });
            const data = await res.json().catch(() => ({}));
            if (!res.ok) { throw new Error(data.message || 'Permintaan gagal. Coba lagi.'); }
            return data;
        },
        async confirmBlock() {
            if (this.loading) { return; }
            this.loading = true;
            this.error = '';
            try {
                const data = await this.send('{{ route('settings.blocks.store') }}', { username: this.username });
                this.open = false;
                this.showUndo(data.user?.id ?? null, data.user?.username || this.username);
            } catch (e) {
                this.error = e.message;
            } finally {
                this.loading = false;
            }
        },
        showUndo(id, username) {
            clearTimeout(this.undo.timeout);
            this.undo = { ...this.undo, show: true, id, username, busy: false, message: 'Berhasil diblokir.' };
            this.undo.timeout = setTimeout(() => { this.undo.show = false; }, 6000);
            this.$nextTick(() => this.$refs.undoBtn?.focus());
        },
        holdUndo() {
            clearTimeout(this.undo.timeout);
        },
        resumeUndo() {
            clearTimeout(this.undo.timeout);
            this.undo.timeout = setTimeout(() => { this.undo.show = false; }, this.undo.id ? 6000 : 3000);
        },
        async undoBlock() {
            if (!this.undo.id || this.undo.busy) { return; }
            this.undo.busy = true;
            try {
                await this.send('{{ url('/pengaturan/blokir') }}/' + this.undo.id, { _method: 'DELETE' });
                this.undo.id = null;
                this.undo.username = '';
                this.undo.message = 'Blokir dibuka.';
                this.resumeUndo();
            } catch (e) {
                if (window.notify) { window.notify(e.message, 'error'); }
            } finally {
                this.undo.busy = false;
            }
        }
     }">

    {{-- Confirmation Modal --}}
    <div x-show="open"
         x-cloak
         @keydown.escape.window="open = false"
         class="fixed inset-0 z-50 overflow-y-auto"
         role="dialog"
         aria-modal="true"
         aria-labelledby="block-user-title">

        <div x-show="open"
             x-transition:enter="transition-opacity ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="open = false"
             class="fixed inset-0 bg-black/60 dark:bg-black/80 backdrop-blur-xs"></div>

        <div class="min-h-full flex items-center justify-center p-4">
            <div x-show="open"
                 x-transition:enter="transition ease-out duration-200 transform"
                 x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-150 transform"
                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                 x-transition:leave-end="opacity-0 scale-95 translate-y-2"
                 class="relative w-full max-w-sm bg-[var(--surface)] border border-[var(--border-color)] rounded-2xl overflow-hidden shadow-2xl p-6 text-[var(--primary)]">

                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-500 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <h3 id="block-user-title" class="text-base font-bold tracking-tight text-[var(--primary)] truncate">
                            Blokir <span class="font-mono" x-text="'@' + username"></span>?
                        </h3>
                        <p x-show="displayName" class="text-xs text-[var(--text-muted)] truncate">
                            <span x-text="displayName"></span> · <span class="font-mono" x-text="'@' + username"></span>
                        </p>
                    </div>
                </div>

                <p class="text-xs leading-relaxed text-[var(--text-muted)]">
                    Blokir memutus follow dua arah dan menutup obrolan. Karya mereka tetap tampil di feed kamu.
                </p>

                <p x-show="error" x-cloak class="mt-3 text-xs text-rose-400" role="alert" x-text="error"></p>

                <div class="flex items-center justify-end gap-2.5 mt-5">
                    <button type="button"
                            @click="open = false"
                            class="btn-secondary text-xs py-2 px-4 cursor-pointer">
                        Batal
                    </button>
                    <button type="button"
                            x-ref="blockConfirm"
                            @click="confirmBlock()"
                            :disabled="loading"
                            class="px-5 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold transition shadow-xs active:scale-95 disabled:opacity-60 cursor-pointer">
                        <span x-text="loading ? 'Memblokir...' : 'Blokir'">Blokir</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Undo Snackbar --}}
    <div class="fixed left-1/2 -translate-x-1/2 bottom-20 lg:bottom-6 z-[45] w-max max-w-[92vw] pointer-events-none"
         role="status"
         aria-live="polite">
        <div x-show="undo.show"
             x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-3"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 translate-y-3"
             @mouseenter="holdUndo()"
             @mouseleave="resumeUndo()"
             class="pointer-events-auto flex items-center gap-3 rounded-xl bg-[var(--surface-hover)] backdrop-blur-md border border-[var(--border-strong)] shadow-[var(--shadow-lift)] pl-4 pr-2 py-2 text-xs text-[var(--primary)]">
            <span class="truncate">
                <span x-text="undo.message">Berhasil diblokir.</span>
                <span x-show="undo.username" class="font-mono text-[var(--text-muted)]" x-text="'@' + undo.username"></span>
            </span>
            <button type="button"
                    x-show="undo.id"
                    x-ref="undoBtn"
                    @click="undoBlock()"
                    :disabled="undo.busy"
                    class="shrink-0 inline-flex items-center justify-center min-h-[40px] px-3 rounded-lg font-semibold text-[var(--primary)] hover:bg-[var(--surface)] border border-[var(--border-strong)] transition active:scale-95 disabled:opacity-60 cursor-pointer">
                <span x-text="undo.busy ? 'Membuka...' : 'Buka blokir'">Buka blokir</span>
            </button>
        </div>
    </div>
</div>