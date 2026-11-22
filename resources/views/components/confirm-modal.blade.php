<div x-data="{
        open: false,
        submitting: false,
        title: 'Konfirmasi Hapus',
        target: '',
        message: 'Tindakan ini bersifat permanen dan tidak bisa dibatalkan.',
        confirmText: 'Hapus',
        cancelText: 'Batal',
        action: '',
        method: 'DELETE',
        onConfirm: null,
        openModal(detail = {}) {
            this.submitting = false;
            this.title = detail.title || 'Konfirmasi Hapus';
            this.target = detail.target || '';
            this.message = detail.message || 'Tindakan ini bersifat permanen dan tidak bisa dibatalkan.';
            this.confirmText = detail.confirmText || 'Hapus';
            this.cancelText = detail.cancelText || 'Batal';
            this.action = detail.action || '';
            this.method = (detail.method || 'DELETE').toUpperCase();
            this.onConfirm = typeof detail.onConfirm === 'function' ? detail.onConfirm : null;
            this.open = true;
            this.$nextTick(() => this.$refs.cancelBtn?.focus());
        },
        close() {
            if (this.submitting) {
                return;
            }
            this.open = false;
            this.onConfirm = null;
        },
        confirm() {
            if (this.submitting) {
                return;
            }
            if (this.action) {
                this.submitting = true;
                this.$nextTick(() => this.$refs.confirmForm.submit());
                return;
            }
            if (typeof this.onConfirm === 'function') {
                const callback = this.onConfirm;
                this.close();
                callback();
                return;
            }
            this.close();
        }
    }"
    @open-confirm-modal.window="openModal($event.detail)"
    @keydown.escape.window="if (open) close()"
    x-show="open"
    x-cloak
    role="dialog"
    aria-modal="true"
    aria-labelledby="confirm-modal-title"
    class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-[#0B0B0D]/80 backdrop-blur-sm"
    x-transition:enter="transition ease-out duration-150"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-100"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    @click="close()">

    <div @click.stop
         x-show="open"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 scale-95 translate-y-1"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
         x-transition:leave-end="opacity-0 scale-95 translate-y-1"
         class="w-full max-w-md bg-[var(--surface)] border border-[var(--border-color)] rounded-2xl shadow-2xl overflow-hidden">

        <div class="p-6">
            <div class="flex items-start gap-4">
                <div class="w-10 h-10 rounded-xl bg-rose-500/10 border border-rose-500/20 flex items-center justify-center text-rose-400 shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </div>

                <div class="min-w-0 flex-1">
                    <p class="font-mono text-[11px] uppercase tracking-wider text-rose-400/90 mb-1">
                        Konfirmasi Tindakan
                    </p>
                    <h3 id="confirm-modal-title"
                        x-text="title"
                        class="text-base font-semibold text-[var(--primary)] leading-snug">
                    </h3>

                    <template x-if="target">
                        <div class="mt-2.5 px-3 py-2 rounded-lg bg-[#161619] border border-[var(--border-color)]">
                            <p x-text="target"
                               class="text-xs font-mono text-[var(--primary)] truncate">
                            </p>
                        </div>
                    </template>

                    <p x-text="message"
                       class="mt-3 text-sm text-[var(--text-muted)] leading-relaxed">
                    </p>
                </div>
            </div>
        </div>

        <form x-ref="confirmForm"
              :action="action"
              :method="method === 'GET' ? 'GET' : 'POST'"
              class="hidden">
            @csrf
            <template x-if="method !== 'GET' && method !== 'POST'">
                <input type="hidden" name="_method" :value="method">
            </template>
        </form>

        <div class="px-6 py-4 bg-[#0B0B0D]/40 border-t border-[var(--border-color)] flex items-center justify-end gap-2.5">
            <button type="button"
                    x-ref="cancelBtn"
                    @click="close()"
                    :disabled="submitting"
                    x-text="cancelText"
                    class="px-4 py-2 rounded-xl border border-[var(--border-color)] bg-[var(--surface)] text-sm font-medium text-[var(--primary)] hover:bg-[var(--surface-hover)] disabled:opacity-50 transition cursor-pointer">
            </button>

            <button type="button"
                    @click="confirm()"
                    :disabled="submitting"
                    class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-500 text-sm font-semibold text-white disabled:opacity-60 transition cursor-pointer inline-flex items-center gap-2">
                <span x-text="submitting ? 'Memproses...' : confirmText"></span>
            </button>
        </div>
    </div>
</div>

<script>
    window.openConfirmModal = window.openConfirmModal || function (options = {}) {
        window.dispatchEvent(new CustomEvent('open-confirm-modal', {
            detail: options || {}
        }));
    };
</script>
