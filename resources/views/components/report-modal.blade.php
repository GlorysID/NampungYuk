<div x-data="{
        open: false,
        type: 'project', // 'project', 'user', 'community_post'
        id: null,
        title: '',
        reason: 'spam',
        notes: '',
        init() {
            window.addEventListener('open-report-modal', (e) => {
                this.type = e.detail?.type || 'project';
                this.id = e.detail?.id || null;
                this.title = e.detail?.title || (this.type === 'user' ? 'Akun Pengguna' : 'Konten');
                this.reason = 'spam';
                this.notes = '';
                this.open = true;
            });
            window.openReportModal = (detail) => {
                window.dispatchEvent(new CustomEvent('open-report-modal', { detail }));
            };
        }
     }"
     x-show="open"
     x-cloak
     @keydown.escape.window="open = false"
     class="fixed inset-0 z-50 overflow-y-auto"
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

            <!-- Close Button -->
            <button @click="open = false" 
                    type="button" 
                    class="absolute top-4 right-4 w-8 h-8 rounded-full bg-black/5 hover:bg-black/10 dark:bg-white/5 dark:hover:bg-white/10 text-[#71717a] hover:text-[#18181b] dark:hover:text-white flex items-center justify-center transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>

            <!-- Header -->
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-500 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <div>
                    <h3 class="text-lg font-bold tracking-tight text-[#18181b] dark:text-white">Laporkan Konten</h3>
                    <p class="text-xs text-[#71717a] truncate max-w-[320px]">
                        Target: <span class="font-semibold text-[#18181b] dark:text-[#fafafa]" x-text="title"></span>
                    </p>
                </div>
            </div>

            <!-- Form -->
            <form method="POST" action="{{ route('reports.store') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="reportable_type" :value="type">
                <input type="hidden" name="reportable_id" :value="id">

                <!-- Reason Radio Selection -->
                <div>
                    <label class="block text-xs font-semibold text-[#63636b] dark:text-[#a1a1aa] mb-2">Pilih Alasan Pelanggaran</label>
                    <div class="space-y-2">
                        <label class="flex items-center gap-3 p-3 rounded-xl border border-[#e4e4e7] dark:border-[#27272a] hover:bg-black/5 dark:hover:bg-white/5 cursor-pointer transition text-xs select-none">
                            <input type="radio" name="reason" value="spam" x-model="reason" class="text-[#0070f3] focus:ring-[#0070f3]">
                            <div>
                                <span class="font-semibold block text-[#18181b] dark:text-white">Spam / Iklan Berlebihan</span>
                                <span class="text-[11px] text-[#71717a]">Promosi tidak relevan, bot, atau repost berulang.</span>
                            </div>
                        </label>

                        <label class="flex items-center gap-3 p-3 rounded-xl border border-[#e4e4e7] dark:border-[#27272a] hover:bg-black/5 dark:hover:bg-white/5 cursor-pointer transition text-xs select-none">
                            <input type="radio" name="reason" value="illegal_gambling" x-model="reason" class="text-[#0070f3] focus:ring-[#0070f3]">
                            <div>
                                <span class="font-semibold block text-[#18181b] dark:text-white">Konten Ilegal / Judi / Malware</span>
                                <span class="text-[11px] text-[#71717a]">Tautan judi online, file berbahaya, atau skema penipuan.</span>
                            </div>
                        </label>

                        <label class="flex items-center gap-3 p-3 rounded-xl border border-[#e4e4e7] dark:border-[#27272a] hover:bg-black/5 dark:hover:bg-white/5 cursor-pointer transition text-xs select-none">
                            <input type="radio" name="reason" value="copyright" x-model="reason" class="text-[#0070f3] focus:ring-[#0070f3]">
                            <div>
                                <span class="font-semibold block text-[#18181b] dark:text-white">Plagiasi / Pelanggaran Hak Cipta</span>
                                <span class="text-[11px] text-[#71717a]">Mengklaim karya orang lain tanpa izin atau atribusi.</span>
                            </div>
                        </label>

                        <label class="flex items-center gap-3 p-3 rounded-xl border border-[#e4e4e7] dark:border-[#27272a] hover:bg-black/5 dark:hover:bg-white/5 cursor-pointer transition text-xs select-none">
                            <input type="radio" name="reason" value="harassment" x-model="reason" class="text-[#0070f3] focus:ring-[#0070f3]">
                            <div>
                                <span class="font-semibold block text-[#18181b] dark:text-white">Pelecehan / Ujaran Kebencian</span>
                                <span class="text-[11px] text-[#71717a]">Menyerang individu, SARA, atau bahasa yang kasar.</span>
                            </div>
                        </label>

                        <label class="flex items-center gap-3 p-3 rounded-xl border border-[#e4e4e7] dark:border-[#27272a] hover:bg-black/5 dark:hover:bg-white/5 cursor-pointer transition text-xs select-none">
                            <input type="radio" name="reason" value="broken_demo" x-model="reason" class="text-[#0070f3] focus:ring-[#0070f3]">
                            <div>
                                <span class="font-semibold block text-[#18181b] dark:text-white">Tautan / Demo Palsu</span>
                                <span class="text-[11px] text-[#71717a]">Demo diarahkan ke situs jebakan atau tidak berfungsi sama sekali.</span>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Additional Notes -->
                <div>
                    <label for="report-notes" class="block text-xs font-semibold text-[#63636b] dark:text-[#a1a1aa] mb-1">Catatan Tambahan (Opsional)</label>
                    <textarea id="report-notes" 
                              name="notes" 
                              x-model="notes" 
                              rows="2" 
                              placeholder="Berikan detail atau bukti pendukung untuk memudahkan admin..." 
                              class="w-full px-3 py-2 rounded-xl bg-[#f8f9fa] dark:bg-[#16171d] border border-[#e4e4e7] dark:border-[#27272a] text-xs text-[#18181b] dark:text-white placeholder-[#a1a1aa] dark:placeholder-[#52525b] outline-none focus:border-[#0070f3] transition resize-none"></textarea>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center justify-end gap-2.5 pt-2">
                    <button type="button" 
                            @click="open = false" 
                            class="px-4 py-2 rounded-full border border-[#e4e4e7] dark:border-[#27272a] hover:bg-black/5 dark:hover:bg-white/5 text-xs font-semibold text-[#71717a] dark:text-[#a1a1aa] transition">
                        Batal
                    </button>
                    <button type="submit" 
                            class="px-5 py-2 rounded-full bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold transition shadow-sm flex items-center gap-1.5 active:scale-95">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                        <span>Kirim Laporan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
