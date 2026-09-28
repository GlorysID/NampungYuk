@props([
    'title' => 'Terjadi Kesalahan',
    'message' => 'Gagal memuat data. Silakan coba kembali beberapa saat lagi.',
    'retryUrl' => null,
])

<div class="ny-card p-6 sm:p-8 text-center space-y-3 border-rose-200 dark:border-rose-900/50 bg-rose-50/30 dark:bg-rose-950/20">
    <div class="w-10 h-10 rounded-xl bg-rose-100 dark:bg-rose-900/50 text-rose-600 dark:text-rose-400 mx-auto flex items-center justify-center">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
        </svg>
    </div>

    <div class="space-y-1">
        <h3 class="font-bold text-sm text-[#17211F] dark:text-[#F2F5F4]">{{ $title }}</h3>
        <p class="text-xs text-[#66736F] dark:text-[#8E9F9B] max-w-sm mx-auto">{{ $message }}</p>
    </div>

    @if($retryUrl)
        <div class="pt-2">
            <x-button :href="$retryUrl" variant="secondary" size="sm">
                Coba Lagi
            </x-button>
        </div>
    @endif
</div>
