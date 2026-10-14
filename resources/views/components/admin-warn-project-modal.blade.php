@if(auth()->check() && auth()->user()->is_admin)
<div x-data="{
        open: false,
        projectId: null,
        title: '',
        action: 'set',
        notes: '',
        init() {
            window.addEventListener('open-warn-project-modal', (e) => {
                this.projectId = e.detail?.id || null;
                this.title = e.detail?.title || 'Project';
                this.action = e.detail?.action === 'remove' ? 'remove' : 'set';
                this.notes = e.detail?.notes || 'Project ini sedang dalam peninjauan karena terindikasi melanggar pedoman komunitas NampungYuk.';
                this.open = true;
            });
            window.openWarnProjectModal = (detail) => {
                window.dispatchEvent(new CustomEvent('open-warn-project-modal', { detail }));
            };
        }
     }"
     x-show="open"
     x-cloak
     @keydown.escape.window="open = false"
     class="fixed inset-0 z-[60] overflow-y-auto"
     role="dialog"
     aria-modal="true">

    <!-- Backdrop -->
    <div x-show="open"
         x-transition:enter="transition-opacity ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="open = false"
         class="fixed inset-0 bg-black/60 dark:bg-black/80 backdrop-blur-md"></div>

    <!-- Center Container -->
    <div class="min-h-full flex items-center justify-center p-4">
        <div x-show="open"
             x-transition:enter="transition ease-out duration-200 transform"
             x-transition:enter-start="opacity-0 scale-95 translate-y-2"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150 transform"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-95 translate-y-2"
             class="relative w-full max-w-lg bg-white dark:bg-[#0c0d12] border border-[#e4e4e7] dark:border-[#27272a] rounded-3xl overflow-hidden shadow-2xl p-6 sm:p-7 text-[#18181b] dark:text-[#fafafa]">

            <button @click="open = false" type="button"
                    class="absolute top-4 right-4 w-8 h-8 rounded-full bg-black/5 hover:bg-black/10 dark:bg-white/5 dark:hover:bg-white/10 text-[#71717a] hover:text-[#18181b] dark:hover:text-white flex items-center justify-center transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>

            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-amber-500 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <div class="min-w-0">
                    <h3 class="text-lg font-bold tracking-tight"
                        x-text="action === 'remove' ? 'Cabut Peringatan Project' : 'Beri Peringatan Project'"></h3>
                    <p class="text-xs text-[#71717a] truncate max-w-[320px]">
                        Target: <span class="font-semibold text-[#18181b] dark:text-[#fafafa]" x-text="title"></span>
                    </p>
                </div>
            </div>

            <form method="POST" :action="'/admin/project/' + projectId + '/warning'" class="space-y-4">
                @csrf
                <input type="hidden" name="action" :value="action">

                <template x-if="action === 'set'">
                    <div>
                        <label class="block text-xs font-semibold text-[#63636b] dark:text-[#a1a1aa] mb-2">
                            Keterangan Peringatan (tampil publik pada project)
                        </label>
                        <textarea name="warning_notes" x-model="notes" rows="3" maxlength="500" required
                                  placeholder="Contoh: Konten mengandung tautan yang melanggar pedoman komunitas. Mohon segera diperbaiki."
                                  class="w-full px-3 py-2.5 rounded-xl bg-[#f8f9fa] dark:bg-[#16171d] border border-[#e4e4e7] dark:border-[#27272a] text-xs outline-none focus:border-amber-500 transition resize-none"></textarea>
                        <p class="text-[11px] text-[#71717a] mt-1">Keterangan ini akan ditampilkan kepada pembuat project dan pengunjung halaman project.</p>
                    </div>
                </template>

                <template x-if="action === 'remove'">
                    <p class="text-xs text-[#71717a]">
                        Peringatan pada project ini akan dihapus dan project kembali tampil normal.
                    </p>
                </template>

                <div class="flex items-center justify-end gap-2">
                    <button type="button" @click="open = false"
                            class="px-4 py-2 rounded-full border border-[#e4e4e7] dark:border-[#27272a] text-xs font-semibold text-[#71717a] hover:bg-black/5 dark:hover:bg-white/5 transition">
                        Batal
                    </button>
                    <button type="submit"
                            class="px-5 py-2 rounded-full bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs shadow-sm transition"
                            x-text="action === 'remove' ? 'Cabut Peringatan' : 'Pasang Peringatan'"></button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
