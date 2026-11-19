<!-- Notification Bell + Dropdown -->
<div class="relative" x-data="notificationBell({ me: {{ auth()->id() }}, initialUnread: {{ $unreadNotificationsCount ?? 0 }} })">
    <button @click="open = !open; if (open) refresh()"
            type="button"
            class="relative w-9 h-9 rounded-lg flex items-center justify-center text-[var(--text-muted)] hover:text-[var(--primary)] hover:bg-[var(--surface-hover)] transition cursor-pointer"
            aria-label="Notifikasi"
            :aria-expanded="open">
        <svg class="w-[19px] h-[19px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
        </svg>
        <span x-show="unreadCount > 0" x-cloak class="hl-badge-dot" x-text="unreadCount > 9 ? '9+' : unreadCount"></span>
    </button>

    <div x-show="open"
         @click.away="open = false"
         x-cloak
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 -translate-y-1"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="absolute right-0 mt-2 w-80 bg-[var(--surface)] rounded-2xl border border-[var(--border-color)] shadow-xl z-50 overflow-hidden">
        <div class="flex items-center justify-between px-4 py-3 border-b border-[var(--border-color)]">
            <span class="hl-label">Notifikasi</span>
            <button @click="markAll()" x-show="unreadCount > 0" type="button" class="text-[11px] font-semibold text-emerald-400 hover:underline cursor-pointer">
                Tandai dibaca
            </button>
        </div>

        <div class="max-h-96 overflow-y-auto divide-y divide-[var(--border-color)]">
            <template x-for="n in notifications" :key="n.id">
                <a :href="n.data.url || '#'"
                   class="flex items-start gap-3 px-4 py-3 transition hover:bg-[var(--surface-hover)]"
                   :class="!n.read ? 'bg-emerald-500/[0.06]' : ''">
                    <template x-if="n.data.actor_avatar">
                        <img :src="n.data.actor_avatar" alt="" referrerpolicy="no-referrer" class="w-8 h-8 rounded-full object-cover ring-1 ring-[var(--border-color)] shrink-0 mt-0.5">
                    </template>
                    <template x-if="!n.data.actor_avatar">
                        <span class="w-8 h-8 rounded-full bg-[var(--surface-muted)] border border-[var(--border-color)] text-[var(--primary)] flex items-center justify-center text-xs font-bold shrink-0 mt-0.5"
                              x-text="(n.data.actor_name || 'N').trim().charAt(0).toUpperCase()"></span>
                    </template>
                    <div class="min-w-0 flex-1">
                        <p class="text-[13px] text-[var(--primary)] leading-snug" x-text="n.data.message"></p>
                        <p class="text-[11px] font-mono text-[var(--text-muted)] mt-1" x-text="n.created_at"></p>
                    </div>
                    <span x-show="!n.read" class="w-1.5 h-1.5 rounded-full bg-emerald-400 mt-2 shrink-0"></span>
                </a>
            </template>

            <template x-if="loading && notifications.length === 0">
                <div class="px-4 py-8 text-center text-xs text-[var(--text-muted)]">
                    Memuat notifikasi...
                </div>
            </template>

            <template x-if="!loading && notifications.length === 0">
                <div class="px-4 py-8 text-center text-xs text-[var(--text-muted)]">
                    Belum ada notifikasi baru.
                </div>
            </template>
        </div>

        <a href="{{ route('notifications.index') }}" class="block px-4 py-2.5 text-center text-xs font-semibold text-emerald-600 dark:text-emerald-400 border-t border-[var(--border-color)] hover:bg-[var(--surface-hover)] transition">
            Lihat semua notifikasi
        </a>
    </div>
</div>
