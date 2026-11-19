import Alpine from 'alpinejs';

/**
 * Scroll position restore.
 * When navigating away from a page, remember the scroll offset keyed by path
 * (marked "pending"), then restore it when the browser returns to that path
 * (e.g. pressing back from a project detail page). This keeps the feed at the
 * same position instead of jumping to the top.
 *
 * IMPORTANT: filter/search navigation (q, tab, kategori, tech, view) must NOT
 * restore an old scroll offset — a fresh result set should start from the top.
 * Otherwise, typing in the search bar while scrolled down would snap the page
 * back to a stale offset.
 */
(function () {
    const KEY = 'nyScrollPositions';

    // Query params that indicate a fresh result/navigation context.
    const FILTER_KEYS = ['q', 'search', 'tab', 'kategori', 'tech', 'view', 'page'];

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

    // A path is "filterable" when it carries any filter/search query param.
    function isFilterable(url) {
        return FILTER_KEYS.some((k) => url.searchParams.has(k));
    }

    // Take manual control so the browser doesn't fight our restore.
    if ('scrollRestoration' in history) {
        history.scrollRestoration = 'manual';
    }

    // Save the current scroll position right before the page goes away.
    const savePosition = () => {
        if (window.__nySkipSave) return;

        const currentUrl = new URL(window.location.href);
        if (isFilterable(currentUrl)) return;

        const store = readStore();
        store[path()] = { y: window.scrollY, pages: window.__nyFeedPageCount?.() ?? 1, pending: true };
        writeStore(store);
    };

    // pagehide covers link navigation, back/forward, reload, and tab close.
    window.addEventListener('pagehide', savePosition);

    // Clicking a link to the page you are already on means "start over": drop the
    // remembered feed state so the reload lands on a fresh first page.
    document.addEventListener('click', (e) => {
        const link = e.target.closest('a[href]');
        if (!link) return;
        const href = link.getAttribute('href');
        if (!href || href.startsWith('#') || link.target === '_blank' || e.metaKey || e.ctrlKey || e.shiftKey) return;
        if (link.hasAttribute('data-no-scroll-save')) return;

        const url = new URL(link.href, window.location.origin);
        if (url.pathname + url.search !== path()) return;

        const store = readStore();
        delete store[path()];
        writeStore(store);

        // pagehide still fires for this navigation; keep it from writing the
        // old position straight back.
        window.__nySkipSave = true;
    });

    // Feed restore reads the saved entry synchronously (before window.load).
    window.__nyFeedState = () => readStore()[path()] ?? null;

    // Back/forward cache restores the live DOM without a load event, so the stored
    // offset is the only reliable way back to where the visitor was.
    window.addEventListener('pageshow', (e) => {
        if (! e.persisted) return;

        const entry = window.__nyFeedState?.();
        if (entry && typeof entry.y === 'number') {
            window.scrollTo({ top: entry.y, behavior: 'instant' });
        }
    });

    // Restore on load only for the *same* non-filtered context we left.
    window.addEventListener('load', () => {
        const currentUrl = new URL(window.location.href);

        // Fresh filter/search navigation → always start from the top.
        if (isFilterable(currentUrl)) {
            return;
        }

        // The infinite feed rebuilds its pages itself and restores the offset last.
        if (window.__nyFeedRestoring) {
            return;
        }

        const store = readStore();
        const entry = store[path()];
        if (entry && entry.pending && typeof entry.y === 'number') {
            // Defer so layout/images settle enough for an accurate restore.
            // "instant" bypasses html { scroll-behavior: smooth } so returning to a
            // page doesn't animate-scroll from the top (that read as a glitch).
            requestAnimationFrame(() => {
                window.scrollTo({ top: entry.y, behavior: 'instant' });
                setTimeout(() => window.scrollTo({ top: entry.y, behavior: 'instant' }), 60);
            });
            entry.pending = false;
            writeStore(store);
        }
    });
})();

let echoPromise = null;
function ensureEcho() {
    if (window.Echo) return Promise.resolve(window.Echo);
    if (!echoPromise) {
        echoPromise = import('./echo')
            .then(() => window.Echo || null)
            .catch(() => null);
    }
    return echoPromise;
}

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
        pages: 1,

        init() {
            this.done = ! this.nextUrl;
            window.__nyFeedPageCount = () => this.pages;

            // Coming back to the feed: rebuild the pages the visitor had already
            // loaded, then return to the same offset. Restoring the offset on a
            // single-page feed lands mid-list and reads as "reset to the top".
            const saved = window.__nyFeedState?.();
            if (saved && saved.pending && saved.pages > 1) {
                window.__nyFeedRestoring = true;
                this.restore(saved);
                return;
            }

            this.observe();
        },

        observe() {
            const sentinel = this.$refs.sentinel;
            if (sentinel && 'IntersectionObserver' in window) {
                this.observer = new IntersectionObserver((entries) => {
                    if (entries.some((e) => e.isIntersecting)) this.loadMore();
                }, { rootMargin: '400px 0px' });
                this.observer.observe(sentinel);
            }
        },

        async restore(saved) {
            this.observer?.disconnect();

            while (this.pages < saved.pages && ! this.done) {
                await this.loadMore();
            }

            requestAnimationFrame(() => {
                window.scrollTo({ top: saved.y, behavior: 'instant' });
                setTimeout(() => window.scrollTo({ top: saved.y, behavior: 'instant' }), 60);
            });

            this.observe();
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

                this.pages = Math.max(this.pages, page);

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

    /**
     * Space room: WebRTC mesh with Reverb signaling.
     * Each participant connects directly to every other participant.
     */
    Alpine.data('spaceRoom', (config) => {
        const spaceId = config.spaceId;
        const routeKey = config.spaceSlug || config.spaceId;
        const me = config.me;
        const myName = config.myName || 'Kamu';
        const type = config.type;
        const signalUrl = config.signalUrl;
        const isHost = !!config.isHost;
        const isJoined = !!(config.isJoined || isHost);

        return {
            joined: isJoined,
            micOn: false,
            camOn: false,
            peers: [],
            localStream: null,
            pcs: {},
            pendingIce: {},
            channel: null,
            _subscribing: false,
            chatOpen: true,
            spaceMsgs: [],
            draft: '',
            ended: false,

            async init() {
                if (isJoined) {
                    this.subscribeChannel();
                }
            },

            async sendChat() {
                const text = (this.draft || '').trim();
                if (! text) return;
                this.spaceMsgs.push({ name: myName, text, mine: true });
                this.draft = '';
                await this.send('chat', null, { text });
            },

            async subscribeChannel() {
                if (this.channel || this._subscribing) return;
                this._subscribing = true;
                try {
                    const echo = await ensureEcho();
                    if (! echo || this.channel || ! this.joined) return;
                    this.channel = echo.private('space.' + spaceId);
                    this.channel.listen('.space.signal', (e) => this.onSignal(e));
                } finally {
                    this._subscribing = false;
                }
            },

            async join() {
                try {
                    this.localStream = await navigator.mediaDevices.getUserMedia({
                        audio: true,
                        video: type === 'video',
                    });
                    this.micOn = true;
                    this.camOn = type === 'video';
                    if (this.$refs.localVideo) this.$refs.localVideo.srcObject = this.localStream;

                    const token = document.querySelector('meta[name=csrf-token]')?.content;
                    const res = await fetch('/spaces/' + routeKey + '/join', {
                        method: 'POST',
                        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': token },
                    });

                    if (! res.ok) {
                        const err = await res.json().catch(() => ({}));
                        if (window.notify) window.notify(err.message || 'Gagal bergabung ke space.');
                        return;
                    }

                    this.joined = true;
                    this.subscribeChannel();
                    if (window.notify) window.notify('Bergabung ke space.');
                } catch (e) {
                    if (window.notify) window.notify('Tidak bisa akses kamera/mikrofon.');
                }
            },

            async leave() {
                try { this.localStream?.getTracks().forEach((t) => t.stop()); } catch (e) {}
                Object.values(this.pcs).forEach((pc) => pc.close());
                this.pcs = {};
                this.peers = [];
                this.joined = false;

                if (this.channel) {
                    window.Echo?.leave('space.' + spaceId);
                    this.channel = null;
                }

                const token = document.querySelector('meta[name=csrf-token]')?.content;
                await fetch('/spaces/' + routeKey + '/leave', {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': token },
                });
            },

        toggleMic() {
            const track = this.localStream?.getAudioTracks()[0];
            if (! track) return;
            track.enabled = ! track.enabled;
            this.micOn = track.enabled;
        },

        toggleCam() {
            const track = this.localStream?.getVideoTracks()[0];
            if (! track) return;
            track.enabled = ! track.enabled;
            this.camOn = track.enabled;
        },

        createPeer(peerId, name) {
            if (this.pcs[peerId]) return this.pcs[peerId];
            const pc = new RTCPeerConnection({
                iceServers: [{ urls: 'stun:stun.l.google.com:19302' }],
            });
            this.pcs[peerId] = pc;

            this.localStream?.getTracks().forEach((t) => pc.addTrack(t, this.localStream));

            pc.onicecandidate = (ev) => {
                if (ev.candidate) this.send('ice', peerId, ev.candidate);
            };
            pc.ontrack = (ev) => {
                if (! this.peers.find((p) => p.id === peerId)) {
                    this.peers.push({ id: peerId, name: name || 'Peserta' });
                }
                this.$nextTick(() => {
                    const el = document.getElementById('peer-' + peerId);
                    if (el && el.srcObject !== ev.streams[0]) el.srcObject = ev.streams[0];
                });
            };
            return pc;
        },

        async onSignal(e) {
            const from = e.from;
            if (from === me) return;

            if (e.kind === 'join') {
                const pc = this.createPeer(from, e.payload?.name);
                const offer = await pc.createOffer();
                await pc.setLocalDescription(offer);
                this.send('offer', from, offer);
            } else if (e.kind === 'offer') {
                const pc = this.createPeer(from, e.payload?.name);
                await pc.setRemoteDescription(new RTCSessionDescription(e.payload));
                await this.flushIce(from, pc);
                const answer = await pc.createAnswer();
                await pc.setLocalDescription(answer);
                this.send('answer', from, answer);
            } else if (e.kind === 'answer') {
                const pc = this.pcs[from];
                if (pc) {
                    await pc.setRemoteDescription(new RTCSessionDescription(e.payload));
                    await this.flushIce(from, pc);
                }
            } else if (e.kind === 'ice') {
                const pc = this.pcs[from];
                if (pc && pc.remoteDescription) {
                    try { await pc.addIceCandidate(new RTCIceCandidate(e.payload)); } catch (err) {}
                } else {
                    (this.pendingIce[from] = this.pendingIce[from] || []).push(e.payload);
                }
            } else if (e.kind === 'chat') {
                this.spaceMsgs.push({
                    name: e.payload?.name || 'Peserta',
                    text: e.payload?.text || '',
                    mine: false,
                });
            } else if (e.kind === 'leave') {
                if (e.payload?.ended) {
                    try { this.localStream?.getTracks().forEach((t) => t.stop()); } catch (err) {}
                    Object.values(this.pcs).forEach((pc) => pc.close());
                    this.pcs = {};
                    this.peers = [];
                    this.joined = false;
                    this.ended = true;
                    if (this.channel) {
                        window.Echo?.leave('space.' + spaceId);
                        this.channel = null;
                    }
                    if (window.notify) window.notify('Space telah diakhiri oleh host.');
                    return;
                }
                this.pcs[from]?.close();
                delete this.pcs[from];
                this.peers = this.peers.filter((p) => p.id !== from);
            }
        },

        async flushIce(peerId, pc) {
            const queued = this.pendingIce[peerId] || [];
            for (const c of queued) {
                try { await pc.addIceCandidate(new RTCIceCandidate(c)); } catch (e) {}
            }
            this.pendingIce[peerId] = [];
        },

        async send(kind, to, payload) {
            const token = document.querySelector('meta[name=csrf-token]')?.content;
            try {
                await fetch(signalUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': token },
                    body: JSON.stringify({ to, kind, payload }),
                });
            } catch (e) {}
        },
    };
});

    /**
     * Chat room: send messages and receive them live via Echo (Reverb).
     */
    Alpine.data('chatRoom', ({ conversationId, me, otherId = null, sendUrl, typingUrl, syncUrl = '', readUrl = '', olderUrl, deleteUrl, editUrl, otherName, isPending = false, canSend = true, canRespond = false, blockedByPolicy = false }) => ({
        conversationId,
        otherId,
        draft: '',
        sending: false,
        // Gate state (mirrors the server-rendered flags; updated live after a send).
        isPending,
        canSend,
        canRespond,
        blockedByPolicy,
        otherName,
        // Unified, chronologically-ordered message list (server + live, deduped by id).
        messages: window.__chatSeed || [],
        typing: false,
        remoteTypingTimer: null,
        localTypingTimer: null,
        lastTypingPing: 0,
        localTypingSent: false,
        loadingOlder: false,
        hasMore: Boolean(window.__chatHasMore),
        echo: null,
        subscribedConvId: null,
        syncTimer: null,
        syncing: false,
        // Inline edit state.
        editingId: null,
        editDraft: '',
        savingEdit: false,

        init() {
            // Seed the list from the server-rendered payload.
            this.messages = (window.__chatSeed || []).map((m) => this.normalize(m));
            window.__chatSeed = null;

            this.$nextTick(() => this.scrollBottom());

            ensureEcho().then((echo) => {
                if (this.conversationId) {
                    this.subscribeConversation(this.conversationId);
                }

                if (echo && me) {
                    const userChannel = echo.private('App.Models.User.' + me);

                    // Catch first-ever message or messages arriving before conversationId existed.
                    userChannel.listen('.inbox.updated', (e) => {
                        if (!e || Number(e.sender_id) === Number(me)) return;
                        if (this.otherId && Number(e.sender_id) !== Number(this.otherId)) return;

                        if (e.conversation_id) {
                            this.subscribeConversation(e.conversation_id);
                        }
                        if (e.id) {
                            this.setRemoteTyping(false);
                            this.upsert(e);
                            this.$nextTick(() => this.scrollBottom());
                            this.markReadNow();
                        }
                    });

                    // Catch live typing even before the first message creates a conversation row.
                    userChannel.listen('.user.typing', (e) => {
                        if (!e || Number(e.user_id) === Number(me)) return;
                        if (this.otherId && Number(e.user_id) !== Number(this.otherId)) return;
                        this.setRemoteTyping(Boolean(e.typing));
                    });

                    const conn = echo.connector?.pusher?.connection;
                    if (conn && typeof conn.bind === 'function') {
                        conn.bind('connected', () => this.stopSyncFallback());
                        conn.bind('disconnected', () => this.startSyncFallback());
                        conn.bind('unavailable', () => this.startSyncFallback());
                        conn.bind('failed', () => this.startSyncFallback());

                        if (conn.state !== 'connected') {
                            this.startSyncFallback();
                        }
                    } else {
                        this.startSyncFallback();
                    }
                } else {
                    this.startSyncFallback();
                }
            });

            window.addEventListener('beforeunload', () => this.stopLocalTyping());
            document.addEventListener('visibilitychange', () => {
                if (!document.hidden && this.messages.some((m) => !m.mine && !m.read_at)) {
                    this.markReadNow();
                }
            });
        },

        subscribeConversation(convId) {
            if (!convId || Number(this.subscribedConvId) === Number(convId)) return;
            if (!window.Echo) {
                ensureEcho().then((echo) => {
                    if (echo) this.subscribeConversation(convId);
                });
                return;
            }

            this.conversationId = Number(convId);
            this.subscribedConvId = Number(convId);
            window.__activeChatConvId = Number(convId);

            this.echo = window.Echo.private('conversation.' + convId);

            this.echo.listen('.message.sent', (e) => {
                if (!e || Number(e.sender_id) === Number(me)) return;
                this.setRemoteTyping(false);
                this.upsert(e);
                this.$nextTick(() => this.scrollBottom());
                this.markReadNow();
            });

            // Read receipts: the other party read our messages up to a given id.
            this.echo.listen('.message.read', (e) => {
                if (!e || Number(e.reader_id) === Number(me)) return;
                this.markMineRead(e.up_to_message_id);
            });

            // Typing indicator.
            this.echo.listen('.user.typing', (e) => {
                if (!e || Number(e.user_id) === Number(me)) return;
                this.setRemoteTyping(Boolean(e.typing));
            });
        },

        setRemoteTyping(isTyping) {
            const wasTyping = this.typing;
            this.typing = Boolean(isTyping);
            clearTimeout(this.remoteTypingTimer);
            if (this.typing) {
                this.remoteTypingTimer = setTimeout(() => { this.typing = false; }, 4500);
                if (!wasTyping) {
                    this.$nextTick(() => this.scrollBottom());
                }
            }
        },

        startSyncFallback() {
            if (!syncUrl || this.syncTimer) return;
            this.syncTimer = setInterval(() => {
                if (!document.hidden) {
                    this.syncOnce();
                }
            }, 1800);
        },

        stopSyncFallback() {
            if (this.syncTimer) {
                clearInterval(this.syncTimer);
                this.syncTimer = null;
            }
        },

        async syncOnce() {
            if (!syncUrl || this.syncing) return;
            this.syncing = true;
            try {
                const lastId = this.messages.length > 0 ? this.messages[this.messages.length - 1].id : 0;
                const url = new URL(syncUrl, window.location.origin);
                url.searchParams.set('after', String(lastId));

                const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
                if (!res.ok) return;
                const data = await res.json();
                if (!data.success) return;

                if (data.conversation) {
                    if (data.conversation.id) {
                        this.subscribeConversation(data.conversation.id);
                    }
                    this.isPending = data.conversation.status === 'pending';
                    this.canSend = data.conversation.can_send !== false;
                    this.canRespond = Boolean(data.conversation.can_respond);
                }

                const incoming = Array.isArray(data.messages) ? data.messages : [];
                let added = false;
                incoming.forEach((m) => {
                    const exists = this.messages.some((x) => x.id === m.id);
                    this.upsert(m);
                    if (!exists) added = true;
                });

                if (data.read_up_to_id) {
                    this.markMineRead(data.read_up_to_id);
                }

                this.setRemoteTyping(Boolean(data.typing));

                if (added) {
                    this.$nextTick(() => this.scrollBottom());
                }
            } catch (e) {
                // silent
            } finally {
                this.syncing = false;
            }
        },

        markReadNow() {
            if (!readUrl || document.hidden || this.isPending) return;
            this.post(readUrl, {});
        },

        normalize(m) {
            return {
                id: m.id,
                sender_id: m.sender_id,
                body: m.body,
                time: m.time,
                created_at: m.created_at,
                read_at: m.read_at ?? null,
                edited_at: m.edited_at ?? null,
                mine: m.sender_id === me,
            };
        },

        upsert(m) {
            const norm = this.normalize(m);
            const idx = this.messages.findIndex((x) => x.id === norm.id);
            if (idx >= 0) {
                this.messages[idx] = norm;
            } else {
                this.messages.push(norm);
                this.messages.sort((a, b) => a.id - b.id);
            }
        },

        markMineRead(upToId) {
            if (!upToId) return;
            const now = new Date().toISOString();
            this.messages.forEach((m) => {
                if (m.mine && m.id <= upToId && !m.read_at) m.read_at = now;
            });
        },

        removeLocal(id) {
            this.messages = this.messages.filter((m) => m.id !== id);
        },

        scrollBottom() {
            const el = this.$refs.scroll;
            if (el) el.scrollTop = el.scrollHeight;
        },

        // Group label: "Hari ini", "Kemarin", or a full date.
        dayLabel(m) {
            if (!m.created_at) return '';
            const d = new Date(m.created_at);
            if (isNaN(d)) return '';
            const today = new Date();
            const startOf = (x) => new Date(x.getFullYear(), x.getMonth(), x.getDate()).getTime();
            const diff = (startOf(today) - startOf(d)) / 86400000;
            if (diff === 0) return 'Hari ini';
            if (diff === 1) return 'Kemarin';
            return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
        },

        // True when a date separator should precede message at index i.
        showDay(i) {
            if (i === 0) return true;
            return this.dayLabel(this.messages[i]) !== this.dayLabel(this.messages[i - 1]);
        },

        // Show a read receipt row under the last own message that is read.
        isLastMine(i) {
            for (let j = this.messages.length - 1; j >= 0; j--) {
                if (this.messages[j].mine) return j === i;
            }
            return false;
        },

        pingTyping() {
            if (!this.draft.trim()) {
                this.stopLocalTyping();
                return;
            }
            const now = Date.now();
            if (!this.localTypingSent || now - this.lastTypingPing >= 1800) {
                this.lastTypingPing = now;
                this.localTypingSent = true;
                this.post(typingUrl, { typing: true });
            }
            clearTimeout(this.localTypingTimer);
            this.localTypingTimer = setTimeout(() => this.stopLocalTyping(), 2600);
        },

        stopLocalTyping() {
            clearTimeout(this.localTypingTimer);
            if (!this.localTypingSent) return;
            this.localTypingSent = false;
            this.lastTypingPing = 0;
            this.post(typingUrl, { typing: false });
        },

        async loadOlder() {
            if (this.loadingOlder || !this.hasMore || this.messages.length === 0) return;
            this.loadingOlder = true;
            const el = this.$refs.scroll;
            const prevHeight = el ? el.scrollHeight : 0;
            try {
                const url = new URL(olderUrl, window.location.origin);
                url.searchParams.set('before', this.messages[0].id);
                const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
                const data = await res.json();
                if (data.success) {
                    const older = (data.messages || []).map((m) => this.normalize(m));
                    const ids = new Set(this.messages.map((m) => m.id));
                    const merged = [...older.filter((m) => !ids.has(m.id)), ...this.messages];
                    this.messages = merged.sort((a, b) => a.id - b.id);
                    this.hasMore = Boolean(data.has_more);
                    // Preserve scroll anchor after prepending.
                    this.$nextTick(() => {
                        if (el) el.scrollTop = el.scrollHeight - prevHeight;
                    });
                }
            } catch (e) {
                // silent
            } finally {
                this.loadingOlder = false;
            }
        },

        async send() {
            const body = this.draft.trim();
            if (!body || this.sending) return;
            this.sending = true;
            const prev = this.draft;
            this.draft = '';
            try {
                const res = await fetch(sendUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content,
                    },
                    body: JSON.stringify({ body }),
                });
                if (res.status === 401) { window.location.href = '/login'; return; }
                const data = await res.json();
                if (data.success) {
                    this.stopLocalTyping();
                    this.upsert(data.message);

                    // Reflect the gate immediately: the first message to someone
                    // who doesn't follow us turns the thread into a pending request.
                    if (data.conversation) {
                        if (data.conversation.id) {
                            this.subscribeConversation(data.conversation.id);
                        }
                        this.isPending = data.conversation.status === 'pending';
                        this.canSend = data.conversation.can_send !== false;
                        if (this.isPending) this.canRespond = false;
                    }

                    this.$nextTick(() => this.scrollBottom());
                } else {
                    this.draft = prev;
                    if (window.notify) notify(data.message || 'Gagal mengirim pesan.');
                }
            } catch (e) {
                this.draft = prev;
            } finally {
                this.sending = false;
            }
        },

        async removeMessage(id) {
            if (!window.confirm('Hapus pesan ini?')) return;
            try {
                const res = await fetch(deleteUrl.replace('__ID__', id), {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content,
                    },
                });
                const data = await res.json();
                if (data.success) {
                    this.removeLocal(id);
                    if (window.notify) notify('Pesan dihapus.');
                } else if (window.notify) {
                    notify(data.message || 'Gagal menghapus pesan.');
                }
            } catch (e) {
                if (window.notify) notify('Gagal menghapus pesan.');
            }
        },

        startEdit(m) {
            this.editingId = m.id;
            this.editDraft = m.body;
            this.$nextTick(() => this.$refs.editInput?.focus());
        },

        cancelEdit() {
            this.editingId = null;
            this.editDraft = '';
        },

        async saveEdit(id) {
            const body = this.editDraft.trim();
            if (!body || this.savingEdit) return;
            this.savingEdit = true;
            try {
                const res = await fetch(editUrl.replace('__ID__', id), {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content,
                    },
                    body: JSON.stringify({ body }),
                });
                const data = await res.json();
                if (data.success) {
                    this.upsert(data.message);
                    this.cancelEdit();
                    if (window.notify) notify('Pesan diubah.');
                } else if (window.notify) {
                    notify(data.message || 'Gagal mengubah pesan.');
                }
            } catch (e) {
                if (window.notify) notify('Gagal mengubah pesan.');
            } finally {
                this.savingEdit = false;
            }
        },

        async post(url, payload) {
            try {
                await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content,
                    },
                    body: JSON.stringify(payload),
                });
            } catch (e) {
                // silent
            }
        },
    }));

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
    Alpine.data('communityJoin', ({ joined = false, pending = false, url }) => ({
        joined,
        pending,
        loading: false,
        async toggle() {
            if (this.loading) return;
            this.loading = true;
            const prevJoined = this.joined;
            const prevPending = this.pending;
            if (!this.pending) {
                this.joined = !prevJoined;
            }
            try {
                const token = document.querySelector('meta[name=csrf-token]')?.content;
                const res = await fetch(url, {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': token },
                });
                if (res.status === 401) { window.location.href = '/login'; return; }
                const data = await res.json();
                if (data.success) {
                    this.joined = Boolean(data.joined);
                    this.pending = Boolean(data.pending);
                    if (window.notify) window.notify(data.message);
                } else {
                    this.joined = prevJoined;
                    this.pending = prevPending;
                    if (window.notify) window.notify(data.message || 'Gagal memproses.');
                }
            } catch (e) {
                this.joined = prevJoined;
                this.pending = prevPending;
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
        _echoBound: false,

        init() {
            this.lastSeen = new Date().toISOString();
            this.bindEcho();
            window.addEventListener('ny:echo-ready', () => this.bindEcho(), { once: true });
        },

        bindEcho() {
            if (this._echoBound || !window.Echo) return;
            this._echoBound = true;
            window.Echo.channel('feed')
                .listen('.project.published', () => {
                    this.newCount += 1;
                });
        },

        revealNew() {
            // Simple hard refresh keeps server-rendered order intact and
            // guarantees the newest items land on page 1.
            window.location.href = window.location.pathname + '?tab=terbaru';
        },
    }));

    /**
     * Inbox live: listens on the user's private channel and updates the
     * conversation list (preview, time, unread badge) without a refresh.
     */
    Alpine.data('inboxLive', ({ me }) => ({
        init() {
            ensureEcho().then((echo) => {
                if (!echo) return;

                echo.private('App.Models.User.' + me).listen('.inbox.updated', (e) => {
                    const row = document.querySelector('[data-conv="' + e.conversation_id + '"]');
                    if (!row) {
                        // New conversation not yet in the list — refresh to create it.
                        if (window.notify) notify(e.sender_name + ' mengirim pesan.');
                        return;
                    }

                    const preview = row.querySelector('[data-conv-preview]');
                    if (preview) preview.textContent = e.body;

                    const time = row.querySelector('[data-conv-time]');
                    if (time) time.textContent = 'baru saja';

                    row.classList.add('bg-emerald-500/[0.06]', 'dark:bg-emerald-500/[0.08]');

                    const badge = row.querySelector('[data-conv-unread]');
                    if (badge) {
                        const current = parseInt(badge.textContent.trim(), 10) || 0;
                        badge.textContent = String(current + 1);
                        badge.classList.remove('hidden');
                    }

                    if (window.notify) notify('Pesan baru dari ' + e.sender_name + '.');
                });
            });
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
                    this.count = typeof data.followers_count === 'number' ? data.followers_count : this.count;
                    window.dispatchEvent(new CustomEvent('ny:follow-changed', {
                        detail: {
                            username: this.username,
                            following: data.following,
                            followers_count: data.followers_count,
                        },
                    }));
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
    Alpine.data('notificationBell', ({ me = null, initialUnread = 0 } = {}) => ({
        open: false,
        unreadCount: Number(initialUnread) || 0,
        notifications: [],
        loading: false,
        _echoBound: false,
        endpoint: '/notifikasi/latest',

        init() {
            this.bindEcho();
            window.addEventListener('ny:echo-ready', () => this.bindEcho(), { once: true });
        },

        bindEcho() {
            if (this._echoBound || !window.Echo) return;
            this._echoBound = true;

            // New project published → some notifications may be fan-out.
            window.Echo.channel('feed')
                .listen('.project.published', () => {
                    if (this.open) this.refresh();
                });

            // Personal channel: new messages / inbox changes update the bell badge.
            if (me) {
                window.Echo.private('App.Models.User.' + me)
                    .listen('.inbox.updated', (e) => {
                        this.unreadCount += 1;
                        if (this.open) this.refresh();
                        if (!window.__activeChatConvId || Number(window.__activeChatConvId) !== Number(e?.conversation_id)) {
                            window.dispatchEvent(new CustomEvent('ny:chat-unread-inc'));
                        }
                    });
            }
        },

        async refresh() {
            this.loading = true;
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
            } finally {
                this.loading = false;
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

document.addEventListener('alpine:init', () => {
    /**
     * Firebase Auth Google Sign-In button controller (lazy-loads Firebase SDK only on demand).
     */
    Alpine.data('firebaseGoogleAuth', ({ endpoint = '', config = {} } = {}) => ({
        loading: false,
        error: '',

        async init() {
            let pending = false;
            try {
                pending = sessionStorage.getItem('ny_fb_redirect_pending') === '1';
            } catch (_) {}
            if (!pending) return;

            try {
                const { completeFirebaseRedirectIfNeeded } = await import('./firebase');
                await completeFirebaseRedirectIfNeeded({ endpoint, config });
            } catch (e) {
                this.error = e?.message || 'Gagal menyelesaikan login Google.';
            }
        },

        async login() {
            if (this.loading) return;
            this.loading = true;
            this.error = '';

            try {
                const { signInWithFirebaseGoogle } = await import('./firebase');
                const res = await signInWithFirebaseGoogle({ endpoint, config });
                if (res?.redirect) {
                    return;
                }
            } catch (e) {
                this.error = e?.message || 'Gagal masuk dengan Google. Silakan coba lagi.';
                this.loading = false;
            }
        },
    }));

    /**
     * Global project lightbox controller (single modal shared across all feed cards).
     */
    Alpine.data('projectLightbox', () => ({
        open: false,
        card: null,
        index: 0,
        sidebar: true,

        get gallery() {
            return this.card?.gallery || [];
        },

        init() {
            window.openProjectLightbox = (card, index = 0) => {
                this.card = card;
                this.index = index;
                this.sidebar = true;
                this.open = true;
            };
        },

        close() {
            this.open = false;
        },

        prev() {
            if (this.gallery.length < 2) return;
            this.index = (this.index - 1 + this.gallery.length) % this.gallery.length;
        },

        next() {
            if (this.gallery.length < 2) return;
            this.index = (this.index + 1) % this.gallery.length;
        },
    }));

    /**
     * Project showcase card state & lightbox interactions.
     */
    Alpine.data('projectCard', (config) => ({
        score: config.score ?? 0,
        userVote: config.userVote ?? '',
        isBookmarked: !!config.isBookmarked,
        isReposted: !!config.isReposted,
        repostsCount: config.repostsCount ?? 0,
        isVoting: false,
        isBookmarking: false,
        isReposting: false,
        commentsCount: config.commentsCount ?? 0,
        comments: [],
        commentsLoaded: false,
        commentsLoading: false,
        newComment: '',
        isSubmittingComment: false,
        gallery: Array.isArray(config.gallery) ? config.gallery : [],
        meta: config.meta || {},

        requireAuth() {
            if (! config.isGuest) return false;
            if (window.openAuthModal) {
                window.openAuthModal('login');
            } else if (config.loginUrl) {
                window.location.href = config.loginUrl;
            }
            return true;
        },

        handleUnauthorized() {
            if (window.openAuthModal) {
                window.openAuthModal('login');
            } else if (config.loginUrl) {
                window.location.href = config.loginUrl;
            }
        },

        openLightbox(i) {
            this.loadComments();
            if (window.openProjectLightbox) {
                window.openProjectLightbox(this, i);
            }
        },

        async loadComments() {
            if (this.commentsLoaded || this.commentsLoading) return;
            this.commentsLoading = true;
            try {
                const res = await fetch(config.commentsUrl, {
                    headers: { 'Accept': 'application/json' },
                });
                if (res.ok) {
                    const data = await res.json();
                    this.comments = data.comments || [];
                    if (typeof data.comments_count === 'number') {
                        this.commentsCount = data.comments_count;
                    }
                    this.commentsLoaded = true;
                }
            } catch (e) {
                // Silent fallback: user can still open full project page
            } finally {
                this.commentsLoading = false;
            }
        },

        async submitComment() {
            if (this.requireAuth()) return;
            const content = this.newComment.trim();
            if (! content || this.isSubmittingComment) return;
            this.isSubmittingComment = true;
            try {
                const res = await fetch(config.commentUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content ?? '',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ content }),
                });
                if (res.status === 401) {
                    this.handleUnauthorized();
                    return;
                }
                const data = await res.json();
                if (data.success && data.comment) {
                    this.comments.unshift(data.comment);
                    this.commentsCount = data.comments_count ?? (this.commentsCount + 1);
                    this.newComment = '';
                    this.commentsLoaded = true;
                    if (window.notify) notify('Komentar terkirim!');
                } else if (window.notify) {
                    notify(data.message || 'Gagal mengirim komentar.');
                }
            } catch (e) {
                if (window.notify) notify('Gagal mengirim komentar.');
            } finally {
                this.isSubmittingComment = false;
            }
        },

        async repost() {
            if (this.requireAuth()) return;
            if (this.isReposting) return;
            this.isReposting = true;
            try {
                const res = await fetch(config.repostUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content ?? '',
                        'Accept': 'application/json',
                    },
                });
                if (res.status === 401) {
                    this.handleUnauthorized();
                    return;
                }
                const data = await res.json();
                if (data.success) {
                    this.isReposted = data.reposted;
                    this.repostsCount = data.reposts_count;
                    if (window.notify) notify(data.message);
                } else if (window.notify) {
                    notify(data.message || 'Gagal repost.');
                }
            } catch (e) {
                if (window.notify) notify('Gagal repost.');
            } finally {
                this.isReposting = false;
            }
        },

        async vote(type) {
            if (this.requireAuth()) return;
            if (this.isVoting) return;
            this.isVoting = true;
            try {
                const res = await fetch(config.voteUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content ?? '',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ type }),
                });
                if (res.status === 401) {
                    this.handleUnauthorized();
                    return;
                }
                const data = await res.json();
                if (data.success) {
                    this.score = data.score;
                    this.userVote = data.user_vote;
                    if (window.notify) {
                        notify(type === 'up' ? 'Upvoted! Terima kasih atas apresiasimu.' : 'Feedback tercatat.');
                    }
                }
            } catch (e) {
                if (window.notify) notify('Gagal memberikan vote');
            } finally {
                this.isVoting = false;
            }
        },

        async toggleBookmark() {
            if (this.requireAuth()) return;
            if (this.isBookmarking) return;
            this.isBookmarking = true;
            try {
                const res = await fetch(config.bookmarkUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content ?? '',
                        'Accept': 'application/json',
                    },
                });
                if (res.status === 401) {
                    this.handleUnauthorized();
                    return;
                }
                const data = await res.json();
                if (data.success) {
                    this.isBookmarked = data.bookmarked;
                    if (window.notify) {
                        notify(this.isBookmarked ? 'Proyek disimpan ke Koleksi!' : 'Dihapus dari Koleksi.');
                    }
                }
            } catch (e) {
                if (window.notify) notify('Gagal menyimpan bookmark');
            } finally {
                this.isBookmarking = false;
            }
        },

        copyLink() {
            navigator.clipboard.writeText(config.showUrl);
            if (window.notify) notify('Tautan project berhasil disalin!');
        },
    }));
});

window.Alpine = Alpine;
Alpine.start();

/**
 * Save a settings form in place (no page reload) and surface the result as a toast.
 * Used by the toggles/radios in /pengaturan so flipping a switch never reloads the page.
 * Saves per form are queued so a fast double toggle can't land out of order.
 */
const settingsSaveQueue = new WeakMap();

/**
 * Display preferences are baked into the page on render (Alpine root state, autoplay
 * attributes), so a saved toggle must also be applied live or the user sees no change.
 */
const applyDisplaySetting = (input) => {
    if (input.name === 'keyboard_shortcuts') {
        const root = window.Alpine?.$data?.(document.body) ?? document.body._x_dataStack?.[0];
        if (root) {
            root.shortcutsEnabled = input.checked;
        }
    }
};

window.nySettingSave = (input) => {
    const form = input.form;

    const send = async () => {
        try {
            const res = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                body: new FormData(form),
            });

            const data = await res.json().catch(() => ({}));

            if (!res.ok) {
                input.checked = !input.checked;
                window.notify?.(data.message || 'Gagal menyimpan pengaturan.', 'error');
                return;
            }

            applyDisplaySetting(input);
            window.notify?.(data.message || 'Pengaturan disimpan.', 'success');
        } catch (error) {
            input.checked = !input.checked;
            window.notify?.('Gagal menyimpan pengaturan. Cek koneksi kamu.', 'error');
        }
    };

    const queued = (settingsSaveQueue.get(form) ?? Promise.resolve()).then(send);
    settingsSaveQueue.set(form, queued);

    return queued;
};
