import Alpine from 'alpinejs';

/**
 * Scroll position restore.
 * When navigating away from a page, remember the scroll offset keyed by path
 * (marked "pending"), then restore it when the browser returns to that path
 * (e.g. pressing back from a project detail page). This keeps the feed at the
 * same position instead of jumping to the top.
 */
(function () {
    const KEY = 'nyScrollPositions';

    function readStore() {
        try {
            return JSON.parse(sessionStorage.getItem(KEY) || '{}');
        } catch (e) {
            return {};
        }
    }

    function writeStore(store) {
        try {
            sessionStorage.setItem(KEY, JSON.stringify(store));
        } catch (e) {
            // ignore
        }
    }

    const path = () => window.location.pathname + window.location.search;

    // Take manual control so the browser doesn't fight our restore.
    if ('scrollRestoration' in history) {
        history.scrollRestoration = 'manual';
    }

    // Save the current scroll position right before navigating to another page.
    document.addEventListener('click', (e) => {
        const link = e.target.closest('a[href]');
        if (!link) return;
        const href = link.getAttribute('href');
        if (!href || href.startsWith('#') || link.target === '_blank' || e.metaKey || e.ctrlKey || e.shiftKey) return;
        if (link.hasAttribute('data-no-scroll-save')) return;

        const url = new URL(link.href, window.location.origin);
        // Only remember when leaving the current page for a different path.
        if (url.pathname + url.search === path()) return;

        const store = readStore();
        store[path()] = { y: window.scrollY, pending: true };
        writeStore(store);
    });

    // Restore on load if we came back to a page we saved.
    window.addEventListener('load', () => {
        const store = readStore();
        const entry = store[path()];
        if (entry && entry.pending && typeof entry.y === 'number') {
            // Defer so layout/images settle enough for an accurate restore.
            requestAnimationFrame(() => {
                window.scrollTo({ top: entry.y, behavior: 'auto' });
                setTimeout(() => window.scrollTo({ top: entry.y, behavior: 'auto' }), 60);
            });
            entry.pending = false;
            writeStore(store);
        }
    });
})();

document.addEventListener('alpine:init', () => {
    /**
     * Infinite scroll feed.
     * Usage: x-data="infiniteFeed({ nextPageUrl: '...' })" on a wrapper that
     * contains the item container ([data-feed-items]) and a sentinel element.
     * Auto-loads the next page when the sentinel becomes visible; also exposes
     * a manual "load more" button as fallback.
     */
    Alpine.data('infiniteFeed', ({ nextPageUrl = null, lastPage = 1 } = {}) => ({
        nextUrl: nextPageUrl,
        lastPage,
        loading: false,
        done: false,
        observer: null,

        init() {
            this.done = ! this.nextUrl;
            if (this.done) return;

            const sentinel = this.$refs.sentinel;
            if (sentinel && 'IntersectionObserver' in window) {
                this.observer = new IntersectionObserver((entries) => {
                    if (entries.some((e) => e.isIntersecting)) this.loadMore();
                }, { rootMargin: '400px 0px' });
                this.observer.observe(sentinel);
            }
        },

        async loadMore() {
            if (this.loading || this.done || ! this.nextUrl) return;
            this.loading = true;
            try {
                const res = await fetch(this.nextUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                if (! res.ok) throw new Error('failed');

                const page = parseInt(res.headers.get('X-Page') || '1', 10);
                const last = parseInt(res.headers.get('X-Last-Page') || String(this.lastPage), 10);
                const html = await res.text();

                const container = this.$refs.items;
                if (container) {
                    const temp = document.createElement('div');
                    temp.innerHTML = html.trim();
                    // Execute nothing; cards are Alpine-initialized by Alpine automatically.
                    while (temp.firstChild) container.appendChild(temp.firstChild);
                }

                if (page >= last) {
                    this.done = true;
                    if (this.observer) this.observer.disconnect();
                } else {
                    // Build the next URL by replacing the page param.
                    const url = new URL(this.nextUrl, window.location.origin);
                    url.searchParams.set('page', String(page + 1));
                    this.nextUrl = url.toString();
                }
            } catch (e) {
                // On error, keep the manual button available.
            } finally {
                this.loading = false;
            }
        },
    }));

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
     * Image cropper — client-side canvas crop with drag + zoom, no library.
     * Usage: x-data="imageCropper({ aspect: 1, outputName: 'avatar' })"
     * Exposes: open(file), close(), apply(), plus drag/zoom state and a hidden
     * file input that receives the cropped blob for form submission.
     */
    Alpine.data('imageCropper', ({ aspect = 1, outputName = 'image' } = {}) => ({
        open: false,
        imgSrc: null,
        imgEl: null,
        // transform state
        scale: 1,
        offsetX: 0,
        offsetY: 0,
        dragging: false,
        startX: 0,
        startY: 0,
        startOffsetX: 0,
        startOffsetY: 0,
        frameW: 0,
        frameH: 0,
        natW: 0,
        natH: 0,

        init() {
            // measure the crop frame after it renders
            this.$watch('open', (v) => {
                if (v) this.$nextTick(() => this.measure());
            });
        },

        measure() {
            const frame = this.$refs.frame;
            if (frame) {
                this.frameW = frame.clientWidth;
                this.frameH = frame.clientHeight;
            }
        },

        openWith(file) {
            if (!file) return;
            this.imgSrc = URL.createObjectURL(file);
            this.scale = 1;
            this.offsetX = 0;
            this.offsetY = 0;
            this.open = true;
            this.$nextTick(() => {
                this.measure();
                const i = this.$refs.image;
                if (i) {
                    i.onload = () => {
                        this.natW = i.naturalWidth;
                        this.natH = i.naturalHeight;
                        // fit so image covers the frame
                        const coverScale = Math.max(this.frameW / this.natW, this.frameH / this.natH);
                        this.scale = coverScale;
                        this.clampOffsets();
                    };
                    if (i.complete) i.onload();
                }
            });
        },

        clampOffsets() {
            const dispW = this.natW * this.scale;
            const dispH = this.natH * this.scale;
            const maxX = Math.max(0, (dispW - this.frameW) / 2);
            const maxY = Math.max(0, (dispH - this.frameH) / 2);
            this.offsetX = Math.max(-maxX, Math.min(maxX, this.offsetX));
            this.offsetY = Math.max(-maxY, Math.min(maxY, this.offsetY));
        },

        onDown(e) {
            this.dragging = true;
            const p = e.touches ? e.touches[0] : e;
            this.startX = p.clientX;
            this.startY = p.clientY;
            this.startOffsetX = this.offsetX;
            this.startOffsetY = this.offsetY;
        },
        onMove(e) {
            if (!this.dragging) return;
            const p = e.touches ? e.touches[0] : e;
            this.offsetX = this.startOffsetX + (p.clientX - this.startX);
            this.offsetY = this.startOffsetY + (p.clientY - this.startY);
            this.clampOffsets();
        },
        onUp() {
            this.dragging = false;
        },

        zoomBy(delta) {
            this.scale = Math.max(0.1, this.scale + delta);
            this.clampOffsets();
        },

        close() {
            this.open = false;
            if (this.imgSrc) URL.revokeObjectURL(this.imgSrc);
            this.imgSrc = null;
        },

        async apply() {
            if (!this.natW || !this.natH) return;
            const outW = aspect >= 1 ? 900 : 900 * aspect;
            const outH = 900 / aspect;
            const canvas = document.createElement('canvas');
            canvas.width = Math.round(outW);
            canvas.height = Math.round(outH);
            const ctx = canvas.getContext('2d');

            // Map: displayed image top-left relative to frame center
            const scaleRatio = canvas.width / this.frameW;
            const drawW = this.natW * this.scale * scaleRatio;
            const drawH = this.natH * this.scale * scaleRatio;
            const drawX = (canvas.width - drawW) / 2 + this.offsetX * scaleRatio;
            const drawY = (canvas.height - drawH) / 2 + this.offsetY * scaleRatio;

            ctx.fillStyle = '#000';
            ctx.fillRect(0, 0, canvas.width, canvas.height);
            ctx.drawImage(this.imgEl, drawX, drawY, drawW, drawH);

            const blob = await new Promise((res) => canvas.toBlob(res, 'image/jpeg', 0.92));
            if (!blob) return;
            const file = new File([blob], outputName + '.jpg', { type: 'image/jpeg' });

            // Put the cropped file into the hidden input bound to the form.
            const input = this.$refs.hidden;
            const dt = new DataTransfer();
            dt.items.add(file);
            input.files = dt.files;

            // preview update (if provided via callback)
            if (typeof this.onCropped === 'function') {
                this.onCropped(URL.createObjectURL(blob));
            }
            this.close();
        },
    }));

    /**
     * Community post vote (up/down).
     */
    Alpine.data('communityPostVote', ({ score, userVote, voteUrl }) => ({
        score,
        userVote,
        voting: false,
        async vote(type) {
            if (this.voting) return;
            this.voting = true;
            try {
                const token = document.querySelector('meta[name=csrf-token]')?.content;
                const res = await fetch(voteUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': token },
                    body: JSON.stringify({ type }),
                });
                if (res.status === 401) { window.location.href = '/login'; return; }
                const data = await res.json();
                if (data.success) {
                    this.score = data.score;
                    this.userVote = data.user_vote;
                }
            } catch (e) {
                // silent
            } finally {
                this.voting = false;
            }
        },
    }));

    /**
     * Community join/leave toggle.
     */
    Alpine.data('communityJoin', ({ joined, url }) => ({
        joined,
        loading: false,
        async toggle() {
            if (this.loading) return;
            this.loading = true;
            const prev = this.joined;
            this.joined = !prev;
            try {
                const token = document.querySelector('meta[name=csrf-token]')?.content;
                const res = await fetch(url, {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': token },
                });
                if (res.status === 401) { window.location.href = '/login'; return; }
                const data = await res.json();
                if (data.success) {
                    this.joined = data.joined;
                    if (window.notify) window.notify(data.message);
                } else {
                    this.joined = prev;
                    if (window.notify) window.notify(data.message || 'Gagal memproses.');
                }
            } catch (e) {
                this.joined = prev;
                if (window.notify) window.notify('Gagal memproses, coba lagi.');
            } finally {
                this.loading = false;
            }
        },
    }));

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
