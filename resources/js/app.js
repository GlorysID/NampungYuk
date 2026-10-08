import Alpine from 'alpinejs';

document.addEventListener('alpine:init', () => {
    Alpine.store('theme', {
        dark: document.documentElement.classList.contains('dark'),
        toggle() {
            this.dark = !this.dark;
            if (this.dark) {
                document.documentElement.classList.add('dark');
                localStorage.setItem('theme', 'dark');
            } else {
                document.documentElement.classList.remove('dark');
                localStorage.setItem('theme', 'light');
            }
        }
    });

    /**
     * Live feed: listens to the "feed" broadcast channel and shows a
     * "N project baru" pulse so users can pull fresh posts without a manual refresh.
     */
    Alpine.data('liveFeed', () => ({
        newCount: 0,
        lastSeen: null,

        init() {
            this.lastSeen = new Date().toISOString();

            if (window.Echo) {
                window.Echo.channel('feed')
                    .listen('.project.published', () => {
                        this.newCount += 1;
                    });
            }
        },

        revealNew() {
            // Simple hard refresh keeps server-rendered order intact and
            // guarantees the newest items land on page 1.
            window.location.href = window.location.pathname + '?tab=terbaru';
        },
    }));

    /**
     * Follow button: optimistic toggle with server sync.
     */
    Alpine.data('followButton', ({ following, count, url, username = '' }) => ({
        following,
        count,
        url,
        username,
        loading: false,
        menuOpen: false,
        hoverUnfollow: false,

        async toggle() {
            if (this.loading) return;
            this.loading = true;

            const previous = this.following;
            this.following = ! previous;
            this.menuOpen = false;
            this.hoverUnfollow = false;

            try {
                const token = document.querySelector('meta[name=csrf-token]')?.content;
                const res = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': token,
                    },
                });

                if (res.status === 401) {
                    window.location.href = '/login';
                    return;
                }

                const data = await res.json();
                if (data.success) {
                    this.following = data.following;
                    if (window.notify) window.notify(data.message);
                } else {
                    this.following = previous;
                }
            } catch (e) {
                this.following = previous;
                if (window.notify) window.notify('Gagal memproses, coba lagi.');
            } finally {
                this.loading = false;
            }
        },
    }));

    /**
     * Notification bell: fetches latest notifications + it re-fetches
     * whenever a broadcast event arrives on the live channel.
     */
    Alpine.data('notificationBell', () => ({
        open: false,
        unreadCount: 0,
        notifications: [],
        endpoint: '/notifikasi/latest',

        init() {
            this.refresh();

            // Re-fetch when server broadcasts a new project or comment.
            if (window.Echo) {
                window.Echo.channel('feed')
                    .listen('.project.published', () => this.refresh());
            }
        },

        async refresh() {
            try {
                const res = await fetch(this.endpoint, {
                    headers: { 'Accept': 'application/json' },
                });
                if (! res.ok) return;
                const data = await res.json();
                this.unreadCount = data.unread_count ?? 0;
                this.notifications = data.notifications ?? [];
            } catch (e) {
                // silent fail — bell is non-critical
            }
        },

        async markAll() {
            try {
                const token = document.querySelector('meta[name=csrf-token]')?.content;
                await fetch('/notifikasi/read-all', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': token,
                    },
                });
                this.unreadCount = 0;
                this.notifications = this.notifications.map((n) => ({ ...n, read: true }));
            } catch (e) {
                // ignore
            }
        },
    }));
});

window.Alpine = Alpine;
Alpine.start();

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allow your team to quickly build robust real-time web applications.
 */

import './echo';
