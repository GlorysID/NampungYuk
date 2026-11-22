@extends('layouts.app')

@section('title', $user->name . ' (@' . $user->username . ') — NampungYuk')
@section('meta_description', $user->bio ?: 'Lihat karya project codingan dan kontribusi ' . $user->name . ' di NampungYuk.')
@section('header_title', '@' . $user->username)
@section('header_subtitle', number_format($totalProjects) . ' postingan')
@section('hide_header_notifications', true)

@section('content')
@php
    $search = $search ?? '';
    $hasSearch = $search !== '';
    $requestedTab = request('tab');
    $initialTab = match ($requestedTab) {
        'likes' => $isOwner ? 'likes' : 'projects',
        'comments' => 'comments',
        'reposts' => 'reposts',
        default => 'projects',
    };
    $initialProjectsSubTab = ($isOwner && ($requestedTab === 'drafts' || request('sub') === 'drafts'))
        ? 'drafts'
        : 'published';
    $clearSearchUrl = route('profile.show', array_filter([
        'username' => $user->username,
        'tab' => $initialProjectsSubTab === 'drafts'
            ? 'drafts'
            : ($initialTab !== 'projects' ? $initialTab : null),
    ]));
@endphp

<div class="space-y-5" x-data="{ activeTab: '{{ $initialTab }}', projectsSubTab: '{{ $initialProjectsSubTab }}', editOpen: {{ $errors->any() && $isOwner ? 'true' : 'false' }}, deleteDraftModalOpen: false, deleteDraftUrl: '', deleteDraftTitle: '' }">

    <!-- Banned Alert Banner (if user is banned) -->
    @if($user->is_banned)
        <div class="p-4 rounded-xl border border-rose-200 dark:border-rose-900/60 bg-rose-50 dark:bg-rose-950/30 text-rose-700 dark:text-rose-400 flex items-start gap-3">
            <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
            </svg>
            <div>
                <p class="font-bold text-sm">Akun Ini Sedang Dibekukan / Diblokir</p>
                <p class="text-xs mt-1 text-rose-600 dark:text-rose-300/80">
                    Alasan: {{ $user->ban_reason ?: 'Pelanggaran syarat dan ketentuan komunitas.' }}
                    @if($user->banned_at)
                        <span class="opacity-75">({{ $user->banned_at->diffForHumans() }})</span>
                    @endif
                </p>
            </div>
        </div>
    @endif

    <!-- Warning Alert Banner (if user has active warning and is viewed by owner or admin) -->
    @if($user->hasWarning() && ($isOwner || (auth()->check() && auth()->user()->is_admin)))
        <div class="p-4 rounded-xl border border-amber-300 dark:border-amber-500/30 bg-amber-50 dark:bg-amber-950/30 text-amber-900 dark:text-amber-200 flex items-start gap-3">
            <svg class="w-5 h-5 shrink-0 mt-0.5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <div class="space-y-0.5">
                <div class="flex flex-wrap items-center gap-2">
                    <p class="font-bold text-sm text-amber-950 dark:text-amber-100">
                        Status Peringatan Akun dari Admin
                        <span class="font-normal text-xs text-amber-300/90">
                            {{ $isOwner ? '— Hanya Kamu dan Admin yang Melihat Ini' : '— Hanya Terlihat oleh Pemilik Akun & Admin' }}
                        </span>
                    </p>
                    @if($user->warned_at)
                        <span class="text-[11px] opacity-75 font-mono">({{ $user->warned_at->format('d M Y, H:i') }})</span>
                    @endif
                </div>
                <p class="text-xs leading-relaxed">{{ $user->warning_notes }}</p>
                <p class="text-[11px] opacity-75">Catatan: Harap periksa karya dan aktivitas Anda agar mematuhi aturan pedoman komunitas NampungYuk.</p>
            </div>
        </div>
    @endif

    <!-- ===================== PROFILE HERO ===================== -->
    <div class="rounded-2xl bg-[var(--surface)] border border-[var(--border-color)] shadow-xs overflow-hidden">

        <!-- Cover banner (uploaded image, or Developer Showcase gradient mesh fallback, 3:1 X-style ratio) -->
        <div class="relative w-full aspect-[3/1] bg-[var(--bg-app)] overflow-hidden">
            @if($user->banner)
                <img src="{{ $user->banner }}" alt="Banner {{ $user->name }}" class="absolute inset-0 w-full h-full object-cover">
                <div class="absolute inset-0 bg-gradient-to-t from-black/30 via-transparent to-transparent"></div>
            @else
                <div class="absolute inset-0 opacity-80"
                     style="background: radial-gradient(120% 140% at 0% 0%, rgba(16, 185, 129, 0.22) 0%, transparent 60%), radial-gradient(120% 140% at 100% 0%, rgba(39, 39, 42, 0.95) 0%, transparent 60%), linear-gradient(180deg, rgba(24, 24, 27, 0.4) 0%, rgba(9, 9, 11, 0.95) 100%);"></div>
                <div class="absolute inset-0 opacity-[0.04]"
                     style="background-image: linear-gradient(rgba(255,255,255,0.7) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.7) 1px, transparent 1px); background-size: 24px 24px;"></div>
            @endif
        </div>

        <div class="relative z-10 px-5 sm:px-6 pb-5">
            <!-- Row 1: Avatar (overlapping banner) + Action Buttons -->
            <div class="flex items-start justify-between gap-4 mb-3">
                <!-- Avatar -->
                <div class="relative shrink-0 -mt-12 sm:-mt-14">
                    <x-user-avatar :user="$user" size="2xl" class="ring-4 ring-[var(--surface)] shadow-md" />
                    @if($isOwner)
                        <span class="absolute -bottom-1 -right-1 w-6 h-6 rounded-full bg-[var(--primary)] text-[var(--primary-text)] flex items-center justify-center ring-2 ring-[var(--surface)] z-10">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        </span>
                    @endif
                </div>

                <!-- Actions: Button Ikuti / Edit Profil / Overflow / Admin Menu -->
                <div class="flex items-center gap-2 shrink-0 pt-3 sm:pt-4 flex-wrap justify-end">
                    @if($isOwner)
                        <button type="button" @click="editOpen = true" class="btn-secondary h-10 text-sm px-4 shadow-xs cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            <span>Edit Profil</span>
                        </button>
                    @else
                        <x-follow-button :user="$user" />

                        <!-- Message button -->
                        @auth
                            @php
                                $chatThreadExists = (bool) \App\Models\Conversation::findBetween(auth()->id(), $user->id);

                                $messageHint = (! $chatThreadExists && $user->requiresMessageRequestFrom(auth()->user()))
                                    ? $user->name.' mewajibkan izin — pesanmu akan masuk sebagai permintaan pesan.'
                                    : 'Kirim pesan ke '.$user->name;
                            @endphp
                            <a href="{{ route('chat.show', $user->username) }}"
                               class="btn-secondary h-10 text-sm px-4 shadow-xs"
                               title="{{ $messageHint }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                                <span>Pesan</span>
                            </a>
                        @else
                            <button type="button"
                                    @click="if (window.openAuthModal) { window.openAuthModal('login'); } else { window.location.href = '{{ route('login') }}'; }"
                                    class="btn-secondary h-10 text-sm px-4 shadow-xs cursor-pointer"
                                    title="Masuk untuk mengirim pesan">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                                <span>Pesan</span>
                            </button>
                        @endauth
                    @endif

                    <!-- Overflow menu (⋯): Bagikan + Laporkan -->
                    <div class="relative"
                         x-data="{
                             menuOpen: false,
                             async shareProfile() {
                                 const url = '{{ route('profile.show', $user->username) }}';
                                 const shareData = { title: '{{ addslashes($user->name) }} ({{ '@' . $user->username }}) di NampungYuk', url };
                                 try {
                                     if (navigator.share) {
                                         await navigator.share(shareData);
                                     } else {
                                         await navigator.clipboard.writeText(url);
                                         if (window.notify) notify('Tautan profil berhasil disalin!');
                                     }
                                 } catch (e) {
                                     if (e && e.name === 'AbortError') return;
                                     if (window.notify) notify('Gagal membagikan profil.');
                                 }
                                 this.menuOpen = false;
                             }
                         }"
                         @click.outside="menuOpen = false">
                        <button @click="menuOpen = !menuOpen"
                                type="button"
                                aria-label="Menu lainnya"
                                aria-haspopup="true"
                                :aria-expanded="menuOpen"
                                class="btn-secondary text-xs py-2 px-2.5 shadow-xs cursor-pointer"
                                title="Aksi lainnya">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                <circle cx="5" cy="12" r="2"/><circle cx="12" cy="12" r="2"/><circle cx="19" cy="12" r="2"/>
                            </svg>
                        </button>

                        <div x-show="menuOpen" x-cloak
                             x-transition:enter="transition ease-out duration-120"
                             x-transition:enter-start="opacity-0 -translate-y-1"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             class="absolute right-0 top-full mt-1.5 z-40 min-w-[190px] rounded-xl bg-[var(--surface)] border border-[var(--border-color)] shadow-lg overflow-hidden text-xs">
                            <!-- Share -->
                            <button @click.stop="shareProfile()"
                                    type="button"
                                    class="w-full flex items-center gap-2.5 px-3.5 py-2.5 text-left text-[var(--primary)] hover:bg-[var(--surface-muted)] transition cursor-pointer">
                                <svg class="w-4 h-4 text-[#908F88] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/></svg>
                                <span>Bagikan Profil</span>
                            </button>

                            <!-- Report (never for your own profile) -->
                            @if(! $isOwner)
                                <div class="border-t border-[var(--border-color)]"></div>
                                @auth
                                    <button @click.stop="window.openReportModal({ type: 'user', id: {{ $user->id }}, title: '{{ addslashes($user->name) }} ({{ '@' . $user->username }})' }); menuOpen = false"
                                            type="button"
                                            class="w-full flex items-center gap-2.5 px-3.5 py-2.5 text-left text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/30 transition cursor-pointer">
                                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                        <span>Laporkan Akun</span>
                                    </button>
                                @else
                                    <button @click.stop="if (window.openAuthModal) { window.openAuthModal('login'); } else { window.location.href = '{{ route('login') }}'; }"
                                            type="button"
                                            class="w-full flex items-center gap-2.5 px-3.5 py-2.5 text-left text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/30 transition cursor-pointer">
                                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                        <span>Laporkan Akun</span>
                                    </button>
                                @endauth
                            @endif
                        </div>
                    </div>

                    <!-- Admin Moderator Controls on User -->
                    @if(auth()->check() && auth()->user()->is_admin && auth()->id() !== $user->id)
                        <div x-data="{ adminMenuOpen: false, banModalOpen: false, warnModalOpen: false, warnNotes: '{{ addslashes($user->warning_notes ?? '') }}', deleteModalOpen: false, deleteConfirm: '' }"
                             @keydown.escape.window="warnModalOpen = false; banModalOpen = false; deleteModalOpen = false"
                             class="relative">
                            <button @click="adminMenuOpen = !adminMenuOpen" type="button" class="px-2.5 py-2 rounded-xl border border-purple-200 dark:border-purple-800/60 bg-purple-50 dark:bg-purple-950/40 text-purple-700 dark:text-purple-300 text-xs font-semibold hover:bg-purple-100 dark:hover:bg-purple-900/40 flex items-center gap-1.5 shadow-xs cursor-pointer">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                                <span>Admin</span>
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>

                            <div x-show="adminMenuOpen" @click.outside="adminMenuOpen = false" x-cloak class="absolute right-0 mt-2 w-48 rounded-xl bg-[var(--surface)] border border-[var(--border-color)] shadow-xl p-1 z-30 text-xs">
                                <!-- Warning Action -->
                                @if($user->hasWarning())
                                    <form action="{{ route('admin.users.warn', $user) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="action" value="remove">
                                        <button type="submit" class="w-full text-left px-3 py-2 rounded-lg text-amber-600 dark:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-950/30 flex items-center gap-2 cursor-pointer">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            Cabut Peringatan
                                        </button>
                                    </form>
                                @else
                                    <button @click="warnModalOpen = true; adminMenuOpen = false" type="button" class="w-full text-left px-3 py-2 rounded-lg text-amber-600 dark:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-950/30 flex items-center gap-2 cursor-pointer">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                        Beri Peringatan Akun
                                    </button>
                                @endif

                                <!-- Ban / Unban Action -->
                                @if($user->is_banned)
                                    <form action="{{ route('admin.users.unban', $user) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="w-full text-left px-3 py-2 rounded-lg text-emerald-600 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-950/30 flex items-center gap-2 cursor-pointer">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            Buka Blokir (Unban)
                                        </button>
                                    </form>
                                @else
                                    <button @click="banModalOpen = true; adminMenuOpen = false" type="button" class="w-full text-left px-3 py-2 rounded-lg text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/30 flex items-center gap-2 cursor-pointer">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                        Blokir Akun (Ban)
                                    </button>
                                @endif

                                <button @click="deleteModalOpen = true; deleteConfirm = ''; adminMenuOpen = false" type="button" class="w-full text-left px-3 py-2 rounded-lg text-rose-400 hover:bg-rose-950/30 flex items-center gap-2 cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    Hapus Total Akun
                                </button>
                            </div>

                            <!-- User Warning Modal -->
                            <div x-show="warnModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
                                <div @click.outside="warnModalOpen = false" class="bg-[var(--surface)] border border-[var(--border-color)] rounded-2xl p-5 max-w-md w-full shadow-2xl space-y-4">
                                    <h3 class="text-base font-bold text-amber-600 dark:text-amber-400">Beri Peringatan ke Akun {{ $user->name }}</h3>
                                    <p class="text-xs text-[var(--text-muted)]">Catatan peringatan ini akan ditampilkan secara khusus pada akun yang bersangkutan.</p>
                                    <form action="{{ route('admin.users.warn', $user) }}" method="POST" class="space-y-3">
                                        @csrf
                                        <input type="hidden" name="action" value="set">
                                        <div>
                                            <label class="block text-xs font-semibold mb-1 text-[var(--primary)]">Catatan Peringatan:</label>
                                            <textarea name="warning_notes" x-model="warnNotes" rows="3" required placeholder="Contoh: Aktivitas akun melanggar pedoman komunitas NampungYuk." class="w-full text-xs p-2.5 rounded-lg border border-[var(--border-color)] bg-[var(--surface-subtle)] text-[var(--primary)] focus:ring-1 focus:ring-amber-500"></textarea>
                                        </div>
                                        <div class="flex justify-end gap-2 pt-2">
                                            <button @click="warnModalOpen = false" type="button" class="btn-secondary text-xs py-2 px-3">Batal</button>
                                            <button type="submit" class="px-4 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-semibold text-xs shadow-xs">Kirim Peringatan</button>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <!-- Ban Modal -->
                            <div x-show="banModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
                                <div @click.outside="banModalOpen = false" class="bg-[var(--surface)] border border-[var(--border-color)] rounded-2xl p-5 max-w-md w-full shadow-2xl space-y-4">
                                    <h3 class="text-base font-bold text-[var(--primary)]">Blokir Akun {{ $user->name }}</h3>
                                    <p class="text-xs text-[var(--text-muted)]">Pengguna ini tidak akan bisa login lagi ke sistem sampai admin membuka blokirnya.</p>
                                    <form action="{{ route('admin.users.ban', $user) }}" method="POST" class="space-y-3">
                                        @csrf
                                        <div>
                                            <label class="block text-xs font-semibold mb-1 text-[var(--primary)]">Alasan Pemblokiran:</label>
                                            <textarea name="reason" rows="3" required placeholder="Contoh: Terdeteksi spam berulang / bot." class="w-full text-xs p-2.5 rounded-lg border border-[var(--border-color)] bg-[var(--surface-subtle)] text-[var(--primary)] focus:ring-1 focus:ring-rose-500"></textarea>
                                        </div>
                                        <div class="flex justify-end gap-2 pt-2">
                                            <button @click="banModalOpen = false" type="button" class="btn-secondary text-xs py-2 px-3">Batal</button>
                                            <button type="submit" class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-semibold text-xs shadow-xs">Blokir Akun</button>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <!-- Delete User Permanently Danger Modal -->
                            <div x-show="deleteModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-xs" role="dialog" aria-modal="true">
                                <div @click.outside="deleteModalOpen = false" class="bg-[var(--surface)] border border-[var(--border-color)] rounded-2xl p-6 max-w-md w-full shadow-2xl space-y-4 text-[var(--primary)]">
                                    <div class="flex items-start gap-3">
                                        <span class="w-10 h-10 rounded-xl bg-rose-500/10 border border-rose-500/25 text-rose-400 flex items-center justify-center shrink-0">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                        </span>
                                        <div class="min-w-0 space-y-1">
                                            <h3 class="text-base font-bold text-rose-400">Hapus Akun Pengguna Permanen</h3>
                                            <p class="text-xs text-[var(--text-muted)]">
                                                {{ $user->name }} (<span class="font-mono text-[var(--primary)]">{{ '@' . $user->username }}</span>)
                                            </p>
                                        </div>
                                    </div>

                                    <div class="px-3.5 py-2.5 rounded-xl bg-rose-500/10 border border-rose-500/25 text-xs text-rose-300 space-y-1">
                                        <p class="font-semibold">Tindakan ini tidak dapat dibatalkan.</p>
                                        <p class="text-rose-200/80">Akun beserta seluruh karya, komentar, postingan komunitas, dan media profilnya akan dihapus permanen.</p>
                                    </div>

                                    <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="space-y-4">
                                        @csrf
                                        @method('DELETE')
                                        <div class="space-y-1.5">
                                            <label class="block text-xs font-semibold text-[var(--primary)]">
                                                Ketik username <code class="px-1.5 py-0.5 rounded bg-[var(--surface-muted)] border border-[var(--border-color)] font-mono text-rose-300">{{ $user->username }}</code> untuk konfirmasi:
                                            </label>
                                            <input type="text"
                                                   x-model="deleteConfirm"
                                                   placeholder="{{ $user->username }}"
                                                   autocomplete="off"
                                                   class="w-full h-10 px-3 rounded-xl bg-[var(--surface-input)] border border-[var(--border-color)] text-xs font-mono text-[var(--primary)] placeholder:text-[var(--text-placeholder)] outline-none focus:border-rose-500 transition">
                                        </div>
                                        <div class="flex items-center justify-end gap-2">
                                            <button @click="deleteModalOpen = false" type="button" class="btn-secondary text-xs py-2 px-4 cursor-pointer">Batal</button>
                                            <button type="submit"
                                                    :disabled="deleteConfirm.trim() !== @js($user->username)"
                                                    class="px-5 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 disabled:opacity-40 disabled:cursor-not-allowed text-white font-bold text-xs cursor-pointer transition">
                                                Hapus Akun Permanen
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Row 2: Name & Username -->
            <div class="space-y-0.5 mt-2">
                <div class="flex items-center gap-2 min-w-0 flex-wrap">
                    <h1 class="text-2xl sm:text-3xl font-bold leading-tight text-[var(--primary)] tracking-tight">
                        {{ $user->name }}
                    </h1>
                </div>
                <div class="flex items-center flex-wrap gap-2">
                    <p class="text-sm text-[var(--text-muted)] font-mono">&#64;{{ $user->username }}</p>
                    @if($followsViewer ?? false)
                        <span x-data="{ mutual: {{ ($viewerFollowsUser ?? false) ? 'true' : 'false' }} }"
                              @ny:follow-changed.window="if ($event.detail?.username === @js($user->username)) { mutual = Boolean($event.detail.following); }"
                              :class="mutual ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/25' : 'bg-[var(--surface-subtle)] text-[var(--text-muted)] border-[var(--border-color)]'"
                              class="text-xs font-medium px-2.5 py-0.5 rounded-full border transition"
                              x-text="mutual ? 'Teman' : 'Mengikutimu'">
                            {{ ($viewerFollowsUser ?? false) ? 'Teman' : 'Mengikutimu' }}
                        </span>
                    @endif
                </div>
            </div>

            @if($user->bio)
                <p class="text-sm sm:text-[15px] text-[var(--primary-hover)] leading-relaxed mt-3.5 max-w-2xl">{{ $user->bio }}</p>
            @endif

            <!-- ===== PROFILE LINKS (link-in-bio list) ===== -->
            @if($user->links->count() > 0)
                <div class="mt-4 flex flex-wrap gap-2 max-w-2xl">
                    @foreach($user->links as $link)
                        <a href="{{ $link->url }}" target="_blank" rel="noopener noreferrer"
                           class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full border border-[var(--border-color)] bg-[var(--surface-subtle)] hover:border-[var(--border-strong)] hover:bg-[var(--surface-hover)] transition group"
                           title="{{ $link->url }}">
                            <svg class="w-3.5 h-3.5 text-[var(--text-muted)] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                            <span class="text-xs sm:text-sm font-semibold text-[var(--primary)] transition">{{ $link->label }}</span>
                            <span class="text-xs text-[var(--text-muted)] hidden sm:inline">{{ $link->domain() }}</span>
                        </a>
                    @endforeach
                </div>
            @endif

            <!-- ===== STAT TERMINAL BAR ===== -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-px mt-5 rounded-xl overflow-hidden border border-[var(--border-color)] bg-[var(--border-color)]">

                {{-- Statis: angka saja, sengaja tanpa affordance klik --}}
                <div class="bg-[var(--surface)] px-4 py-3.5">
                    <p class="flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-wider text-[var(--text-muted)] font-mono">
                        <svg class="w-3 h-3 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7"/></svg>
                        <span>Reputasi</span>
                    </p>
                    <p class="mt-1.5 text-xl sm:text-2xl font-bold text-[var(--primary)] font-mono leading-none">
                        {{ number_format($user->reputation_points) }}
                        <span class="sr-only">{{ number_format($user->reputation_points) }} Poin Reputasi</span>
                    </p>
                </div>
                <div class="bg-[var(--surface)] px-4 py-3.5">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-[var(--text-muted)] font-mono">Karya</p>
                    <p class="mt-1.5 text-xl sm:text-2xl font-bold text-[var(--primary)] font-mono leading-none">{{ $totalProjects }}</p>
                </div>

                {{-- Dapat diklik: chevron selalu tampak (affordance mobile), hover + aksen emerald --}}
                <a href="{{ route('profile.followers', $user->username) }}"
                   class="group relative block bg-[var(--surface)] px-4 py-3.5 hover:bg-[var(--surface-hover)] transition-colors focus-visible:outline-offset-0">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-[var(--text-muted)] font-mono">Pengikut</p>
                    <p class="mt-1.5 flex items-center justify-between gap-2">
                        <span x-data="{ followersCount: {{ (int) $followersCount }} }"
                              @ny:follow-changed.window="if ($event.detail?.username === @js($user->username) && typeof $event.detail.followers_count === 'number') { followersCount = $event.detail.followers_count; }"
                              x-text="followersCount.toLocaleString('id-ID')"
                              class="text-xl sm:text-2xl font-bold text-[var(--primary)] font-mono leading-none">{{ number_format($followersCount) }}</span>
                        <svg class="w-3.5 h-3.5 shrink-0 text-[var(--text-muted)] transition group-hover:translate-x-0.5 group-hover:text-[var(--primary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                    </p>
                    <span class="absolute inset-x-0 bottom-0 h-0.5 bg-emerald-500 origin-left scale-x-0 group-hover:scale-x-100 transition-transform"></span>
                </a>
                <a href="{{ route('profile.following', $user->username) }}"
                   class="group relative block bg-[var(--surface)] px-4 py-3.5 hover:bg-[var(--surface-hover)] transition-colors focus-visible:outline-offset-0">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-[var(--text-muted)] font-mono">Mengikuti</p>
                    <p class="mt-1.5 flex items-center justify-between gap-2">
                        <span class="text-xl sm:text-2xl font-bold text-[var(--primary)] font-mono leading-none">{{ number_format($followingCount) }}</span>
                        <svg class="w-3.5 h-3.5 shrink-0 text-[var(--text-muted)] transition group-hover:translate-x-0.5 group-hover:text-[var(--primary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                    </p>
                    <span class="absolute inset-x-0 bottom-0 h-0.5 bg-emerald-500 origin-left scale-x-0 group-hover:scale-x-100 transition-transform"></span>
                </a>
            </div>

            <p class="text-xs font-mono text-[var(--text-muted)] mt-3.5">
                Bergabung {{ $user->created_at->translatedFormat('F Y') }} · {{ number_format($totalUpvotesReceived) }} total upvote diterima
            </p>
        </div>
    </div>

    <!-- ===================== PINNED PROJECTS ===================== -->
    @if($pinnedProjects->count() > 0)
        <div class="space-y-3">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-500" fill="currentColor" viewBox="0 0 24 24"><path d="M16 3a1 1 0 00-1 1v1.586l-4.293 4.293a1 1 0 00-.29.546l-.585 3.51-2.125 2.124a1 1 0 00.046 1.418l.001.001-1.045 1.045a1 1 0 001.414 1.414L9.16 20.9l.001.001a1 1 0 001.418.046l2.124-2.125 3.51-.585a1 1 0 00.546-.29L21 13.657H22a1 1 0 000-2h-1V7a1 1 0 00-1-1h-4zM7 17l-3.293 3.293a1 1 0 01-1.414-1.414L5.586 15.586 7 17z"/></svg>
                <h2 class="text-xs font-bold uppercase tracking-wider text-[var(--primary)] font-mono">Disematkan</h2>
                <span class="text-[10px] font-mono text-[var(--text-muted)]">{{ $pinnedProjects->count() }}/3</span>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-3">
                @foreach($pinnedProjects as $project)
                    <x-pinned-card :project="$project" :pinned="true" :is-owner="$isOwner" />
                @endforeach
            </div>
        </div>
    @endif

    <!-- ===================== TABS ===================== -->
    <div class="rounded-xl bg-[var(--surface)] border border-[var(--border-color)] p-1 flex items-center gap-1.5 overflow-x-auto shadow-xs">
        <button @click="activeTab = 'projects'"
                :class="activeTab === 'projects' ? 'bg-[var(--primary)] text-[var(--primary-text)] font-semibold shadow-xs' : 'text-[var(--text-muted)] hover:text-[var(--primary)] hover:bg-[var(--surface-muted)]'"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium transition cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
            <span>Karya</span>
            <span class="text-xs font-mono px-2 py-0.5 rounded" :class="activeTab === 'projects' ? 'bg-white/20 dark:bg-black/20 text-[var(--primary-text)]' : 'bg-[var(--surface-muted)] text-[var(--text-muted)]'">{{ $hasSearch ? $projects->total() : $totalProjects }}</span>
        </button>

        <button @click="activeTab = 'comments'"
                :class="activeTab === 'comments' ? 'bg-[var(--primary)] text-[var(--primary-text)] font-semibold shadow-xs' : 'text-[var(--text-muted)] hover:text-[var(--primary)] hover:bg-[var(--surface-muted)]'"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium transition cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
            <span>Diskusi</span>
            <span class="text-xs font-mono px-2 py-0.5 rounded" :class="activeTab === 'comments' ? 'bg-white/20 dark:bg-black/20 text-[var(--primary-text)]' : 'bg-[var(--surface-muted)] text-[var(--text-muted)]'">{{ $comments->count() }}</span>
        </button>

        <button @click="activeTab = 'reposts'"
                :class="activeTab === 'reposts' ? 'bg-[var(--primary)] text-[var(--primary-text)] font-semibold shadow-xs' : 'text-[var(--text-muted)] hover:text-[var(--primary)] hover:bg-[var(--surface-muted)]'"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium transition cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
            <span>Repost</span>
            <span class="text-xs font-mono px-2 py-0.5 rounded" :class="activeTab === 'reposts' ? 'bg-white/20 dark:bg-black/20 text-[var(--primary-text)]' : 'bg-[var(--surface-muted)] text-[var(--text-muted)]'">{{ $hasSearch ? $repostedProjects->total() : $totalReposts }}</span>
        </button>

        @if($isOwner)
            <button @click="activeTab = 'likes'"
                    :class="activeTab === 'likes' ? 'bg-[var(--primary)] text-[var(--primary-text)] font-semibold shadow-xs' : 'text-[var(--text-muted)] hover:text-[var(--primary)] hover:bg-[var(--surface-muted)]'"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium transition cursor-pointer shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                <span>Disukai</span>
                <span class="text-xs font-mono px-2 py-0.5 rounded" :class="activeTab === 'likes' ? 'bg-white/20 dark:bg-black/20 text-[var(--primary-text)]' : 'bg-[var(--surface-muted)] text-[var(--text-muted)]'">{{ $hasSearch && method_exists($likedProjects, 'total') ? $likedProjects->total() : $totalLikes }}</span>
            </button>
        @endif
    </div>

    <!-- ===================== PROFILE POST SEARCH BAR ===================== -->
    <form method="GET"
          action="{{ route('profile.show', $user->username) }}"
          id="profile-search-form"
          class="space-y-2">
        <input type="hidden" name="tab" :value="activeTab === 'projects' && projectsSubTab === 'drafts' ? 'drafts' : activeTab">
        <div class="flex items-center gap-2 rounded-xl bg-[var(--surface)] border border-[var(--border-color)] focus-within:border-[var(--border-strong)] px-3.5 py-2 shadow-xs transition">
            <svg class="w-4 h-4 text-[var(--text-muted)] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z"/>
            </svg>
            <input type="text"
                   id="profile-search-input"
                   name="q"
                   value="{{ $search }}"
                   placeholder="{{ $isOwner ? 'Cari postingan atau karya kamu (judul, deskripsi, tech stack)...' : 'Cari postingan @'.$user->username.'...' }}"
                   aria-label="{{ $isOwner ? 'Cari postingan sendiri' : 'Cari postingan @'.$user->username }}"
                   class="composer-seamless appearance-none flex-1 min-w-0 bg-transparent border-0 text-sm text-[var(--primary)] placeholder:text-[var(--text-muted)] focus:outline-none focus:ring-0">
            @if($hasSearch)
                <a href="{{ $clearSearchUrl }}"
                   class="text-xs font-medium text-[var(--text-muted)] hover:text-[var(--primary)] px-2 py-1 rounded-lg hover:bg-[var(--surface-muted)] transition shrink-0">
                    Reset
                </a>
            @endif
            <button type="submit"
                    class="px-3 py-1.5 rounded-lg bg-[var(--primary)] text-[var(--primary-text)] text-xs font-semibold hover:opacity-90 transition cursor-pointer shrink-0">
                Cari
            </button>
        </div>

        @if($hasSearch)
            <p class="text-xs text-[var(--text-muted)] px-1">
                Menampilkan hasil pencarian untuk <span class="font-semibold text-[var(--primary)]">"{{ $search }}"</span> di profil ini.
            </p>
        @endif
    </form>

    <!-- ===================== TAB: PROJECTS ===================== -->
    <div x-show="activeTab === 'projects'" class="space-y-4">
        @if($isOwner)
            <div class="flex flex-wrap items-center justify-between gap-2.5">
                <div class="inline-flex items-center gap-1 p-1 rounded-xl bg-[var(--surface)] border border-[var(--border-color)]">
                    <button type="button"
                            @click="projectsSubTab = 'published'"
                            :class="projectsSubTab === 'published' ? 'bg-[var(--surface-hover)] text-[var(--primary)] font-semibold border border-[var(--border-strong)]' : 'text-[var(--text-muted)] hover:text-[var(--primary)] border border-transparent'"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs transition cursor-pointer">
                        <span>Terpublikasi</span>
                        <span class="font-mono text-[11px] opacity-80">{{ $hasSearch ? $projects->total() : $totalProjects }}</span>
                    </button>
                    <button type="button"
                            @click="projectsSubTab = 'drafts'"
                            :class="projectsSubTab === 'drafts' ? 'bg-[var(--surface-hover)] text-[var(--primary)] font-semibold border border-[var(--border-strong)]' : 'text-[var(--text-muted)] hover:text-[var(--primary)] border border-transparent'"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs transition cursor-pointer">
                        <span>Draft</span>
                        <span class="font-mono text-[11px] opacity-80">{{ $hasSearch ? $draftProjects->count() : ($totalDrafts ?? 0) }}</span>
                    </button>
                </div>

                <div x-show="projectsSubTab === 'drafts'" x-cloak class="flex items-center gap-3 text-xs">
                    <a href="{{ route('projects.drafts') }}" class="font-semibold text-[var(--text-muted)] hover:text-[var(--primary)] transition">
                        Kelola Semua Draft
                    </a>
                    <a href="{{ route('projects.create') }}" class="btn-secondary text-xs py-1.5 px-3">
                        + Tulis Karya
                    </a>
                </div>
            </div>
        @endif

        <div x-show="!{{ $isOwner ? 'true' : 'false' }} || projectsSubTab === 'published'" class="space-y-4">
            @forelse($projects as $project)
                @php
                    $isBookmarked = in_array($project->id, $userBookmarkedIds ?? []);
                    $initialVote = $userVotes[$project->id] ?? null;
                @endphp
                <x-project-card :project="$project" :initial-vote="$initialVote" :is-bookmarked="$isBookmarked" :is-owner="$isOwner" :is-reposted="in_array($project->id, $userRepostedIds ?? [])" />
            @empty
                @if($hasSearch)
                    <x-empty-state title="Postingan tidak ditemukan" :description="'Tidak ada karya yang cocok dengan kata kunci &quot;' . e($search) . '&quot;.'" />
                @else
                    <x-empty-state title="Belum ada karya codingan" description="Developer ini belum mempublikasikan project ke NampungYuk." />
                @endif
            @endforelse

            @if($projects->hasPages())
                <div class="pt-2">{{ $projects->links() }}</div>
            @endif
        </div>

        @if($isOwner)
            <div x-show="projectsSubTab === 'drafts'" x-cloak class="space-y-3">
                <p class="text-[11.5px] text-[var(--text-muted)] px-1">
                    Draft hanya terlihat oleh kamu. Lanjutkan edit atau langsung post ke feed kapan saja.
                </p>

                @forelse(($draftProjects ?? collect()) as $draftItem)
                    <div class="bg-[var(--surface)] border border-[var(--border-color)] rounded-2xl p-4 sm:p-5 space-y-3">
                        <div class="flex items-start gap-3.5 min-w-0">
                            @if($draftItem->thumbnail)
                                <img src="{{ $draftItem->thumbnail }}" alt="{{ $draftItem->title ?: 'Draft' }}" class="w-16 h-12 rounded-xl object-cover border border-[var(--border-color)] shrink-0 bg-[var(--surface-muted)]">
                            @endif

                            <div class="min-w-0 flex-1 space-y-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="text-[10px] font-mono font-semibold uppercase tracking-wider px-2 py-0.5 rounded-full bg-amber-500/10 text-amber-300 border border-amber-500/25">Draft</span>
                                    @if($draftItem->category)
                                        <span class="text-[11px] font-mono text-[var(--text-muted)]">{{ $draftItem->category->name }}</span>
                                    @endif
                                    <span class="text-[11px] font-mono text-[var(--text-faint)]">Diperbarui {{ $draftItem->updated_at?->diffForHumans() }}</span>
                                </div>
                                <p class="font-bold text-sm sm:text-[15px] text-[var(--primary)] truncate">{{ $draftItem->title ?: 'Draft Tanpa Judul' }}</p>
                                @if($draftItem->tagline)
                                    <p class="text-xs text-[var(--text-muted)] line-clamp-2">{{ $draftItem->tagline }}</p>
                                @else
                                    <p class="text-xs italic text-[var(--text-faint)]">Belum ada tagline.</p>
                                @endif
                            </div>
                        </div>

                        @if(! empty($draftItem->tech_stacks))
                            <div class="flex flex-wrap items-center gap-1.5">
                                @foreach(array_slice($draftItem->tech_stacks, 0, 5) as $stack)
                                    <span class="text-[11px] font-mono px-2 py-0.5 rounded-md bg-[var(--surface-muted)] text-[var(--text-muted)]">#{{ $stack }}</span>
                                @endforeach
                            </div>
                        @endif

                        <div class="flex flex-wrap items-center justify-between gap-2 pt-3 border-t border-[var(--border-color)]">
                            <div class="flex flex-wrap items-center gap-2">
                                <a href="{{ route('projects.create', ['draft' => $draftItem->slug]) }}" class="btn-secondary text-xs py-1.5 px-3.5">
                                    Edit Draft
                                </a>
                                <form method="POST" action="{{ route('projects.publish', $draftItem->slug) }}">
                                    @csrf
                                    <button type="submit" class="btn-primary text-xs py-1.5 px-3.5 cursor-pointer">
                                        Post Sekarang
                                    </button>
                                </form>
                            </div>

                            <button type="button"
                                    @click="deleteDraftUrl = @js(route('projects.destroy', $draftItem->slug)); deleteDraftTitle = @js($draftItem->title ?: 'Draft Tanpa Judul'); deleteDraftModalOpen = true"
                                    class="px-2.5 py-1.5 rounded-lg text-xs font-semibold text-rose-400 hover:bg-rose-500/10 transition cursor-pointer">
                                Hapus
                            </button>
                        </div>
                    </div>
                @empty
                    @if($hasSearch)
                        <x-empty-state title="Draft tidak ditemukan" :description="'Tidak ada draft yang cocok dengan kata kunci &quot;' . e($search) . '&quot;.'" />
                    @else
                        <x-empty-state title="Belum ada draft" description="Karya yang disimpan ke draft akan muncul di sini." action-label="Mulai Tulis Karya" :action-url="route('projects.create')" />
                    @endif
                @endforelse
            </div>
        @endif
    </div>

    <!-- ===================== TAB: REPOSTS ===================== -->
    <div x-show="activeTab === 'reposts'" x-cloak class="space-y-4">
        <p class="text-[11px] text-[var(--text-muted)] px-1">
            Project yang dibagikan ulang oleh {{ $isOwner ? 'kamu' : $user->name }}.
        </p>
        @forelse($repostedProjects as $project)
            @php
                $isBookmarked = in_array($project->id, $userBookmarkedIds ?? []);
                $initialVote = $userVotes[$project->id] ?? null;
            @endphp
            <x-project-card :project="$project" :initial-vote="$initialVote" :is-bookmarked="$isBookmarked" :is-reposted="true" uid="repost" />
        @empty
            @if($hasSearch)
                <x-empty-state title="Repost tidak ditemukan" :description="'Tidak ada repost yang cocok dengan kata kunci &quot;' . e($search) . '&quot;.'" />
            @else
                <x-empty-state title="Belum ada repost" description="Project yang dibagikan ulang akan muncul di sini." />
            @endif
        @endforelse

        @if($repostedProjects->hasPages())
            <div class="pt-2">{{ $repostedProjects->links() }}</div>
        @endif
    </div>

    <!-- ===================== TAB: LIKES (owner only) ===================== -->
    @if($isOwner)
        <div x-show="activeTab === 'likes'" x-cloak class="space-y-4">
            @forelse($likedProjects as $project)
                <x-project-card :project="$project" :initial-vote="'up'" uid="like" />
            @empty
                @if($hasSearch)
                    <x-empty-state title="Tidak ada hasil" :description="'Tidak ada karya yang disukai yang cocok dengan kata kunci &quot;' . e($search) . '&quot;.'" />
                @else
                    <x-empty-state title="Belum ada yang disukai" description="Project yang kamu upvote akan muncul di sini." />
                @endif
            @endforelse

            @if(method_exists($likedProjects, 'hasPages') && $likedProjects->hasPages())
                <div class="pt-2">{{ $likedProjects->links() }}</div>
            @endif
        </div>
    @endif

    <!-- ===================== TAB: COMMENTS ===================== -->
    <div x-show="activeTab === 'comments'" x-cloak class="divide-y divide-[var(--border-color)]">
        @forelse($comments as $comment)
            <div class="py-3 space-y-1">
                <div class="flex items-center justify-between text-xs">
                    @if($comment->project)
                        <span class="text-[var(--text-muted)]">
                            Memberikan masukan di
                            <a href="{{ route('projects.show', $comment->project->slug) }}#komentar" class="font-bold text-[var(--primary)] hover:underline">
                                {{ $comment->project->title }}
                            </a>
                        </span>
                    @endif
                    <span class="text-[var(--text-faint)] text-[11px] font-mono">{{ $comment->created_at->diffForHumans() }}</span>
                </div>
                <p class="text-xs sm:text-sm text-[var(--primary-hover)] leading-relaxed">"{{ $comment->content }}"</p>
            </div>
        @empty
            @if($hasSearch)
                <x-empty-state title="Diskusi tidak ditemukan" :description="'Tidak ada komentar yang cocok dengan kata kunci &quot;' . e($search) . '&quot;.'" />
            @else
                <x-empty-state title="Belum ada ulasan" description="Developer ini belum memberikan komentar pada project lain." />
            @endif
        @endforelse
    </div>

    <!-- ===================== EDIT PROFILE MODAL (X-STYLE) ===================== -->
    @if($isOwner)
        <div x-show="editOpen" x-cloak
             class="fixed inset-0 z-50 flex items-start sm:items-center justify-center p-0 sm:p-4 overflow-y-auto bg-black/65 backdrop-blur-xs"
             @click.self="if (!crop.open) editOpen = false"
             @keydown.escape.window="if (crop.open) { cancelCrop(); } else { editOpen = false; }"
             role="dialog" aria-modal="true" aria-label="Edit profil"
             x-data="{
                preview: null,
                bannerPreview: null,
                bioLen: {{ mb_strlen((string) old('bio', $user->bio ?? '')) }},
                crop: { open: false, tag: 'avatar', file: null, url: null, scale: 1, x: 0, y: 0, drag: false, sx: 0, sy: 0, ox: 0, oy: 0, natW: 0, natH: 0, fw: 0, fh: 0 },
                startCrop(file, tag) {
                    if (!file) return;
                    this.crop = Object.assign(this.crop, { open: true, tag, file, url: URL.createObjectURL(file), scale: 1, x: 0, y: 0, natW: 0, natH: 0, coverScale: 1 });
                    this.$nextTick(() => {
                        const f = this.$refs.cropFrame; this.crop.fw = f.clientWidth; this.crop.fh = f.clientHeight;
                        const img = this.$refs.cropImg;
                        const onload = () => {
                            this.crop.natW = img.naturalWidth; this.crop.natH = img.naturalHeight;
                            this.crop.coverScale = Math.max(this.crop.fw / this.crop.natW, this.crop.fh / this.crop.natH);
                            this.crop.scale = this.crop.coverScale;
                        };
                        if (img.complete) onload(); else img.onload = onload;
                    });
                },
                cropDown(e) { const p = e.touches ? e.touches[0] : e; this.crop.drag = true; this.crop.sx = p.clientX; this.crop.sy = p.clientY; this.crop.ox = this.crop.x; this.crop.oy = this.crop.y; },
                cropMove(e) {
                    if (!this.crop.drag) return;
                    const p = e.touches ? e.touches[0] : e;
                    this.crop.x = this.crop.ox + (p.clientX - this.crop.sx);
                    this.crop.y = this.crop.oy + (p.clientY - this.crop.sy);
                    this.clampCrop();
                },
                clampCrop() {
                    const dw = this.crop.natW * this.crop.scale, dh = this.crop.natH * this.crop.scale;
                    const mx = Math.max(0, (dw - this.crop.fw) / 2), my = Math.max(0, (dh - this.crop.fh) / 2);
                    this.crop.x = Math.max(-mx, Math.min(mx, this.crop.x));
                    this.crop.y = Math.max(-my, Math.min(my, this.crop.y));
                },
                cropUp() { this.crop.drag = false; },
                cropZoom(d) { this.crop.scale = Math.max(0.1, this.crop.scale + d); this.clampCrop(); },
                async applyCrop() {
                    const c = this.crop;
                    if (!c.natW) return;
                    const ratio = c.tag === 'banner' ? 3 : 1; // banner 3:1, avatar 1:1
                    const W = 900, H = Math.round(900 / ratio);
                    const cv = document.createElement('canvas'); cv.width = W; cv.height = H;
                    const ctx = cv.getContext('2d'); ctx.fillStyle = '#000'; ctx.fillRect(0, 0, W, H);
                    const sr = W / c.fw;
                    const dw = c.natW * c.scale * sr, dh = c.natH * c.scale * sr;
                    const dx = (W - dw) / 2 + c.x * sr, dy = (H - dh) / 2 + c.y * sr;
                    ctx.drawImage(this.$refs.cropImg, dx, dy, dw, dh);
                    const blob = await new Promise(r => cv.toBlob(r, 'image/jpeg', 0.92));
                    const file = new File([blob], c.tag + '.jpg', { type: 'image/jpeg' });
                    const input = c.tag === 'avatar' ? this.$refs.avatarInput : this.$refs.bannerInput;
                    const dt = new DataTransfer(); dt.items.add(file); input.files = dt.files;
                    const previewUrl = URL.createObjectURL(blob);
                    if (c.tag === 'avatar') this.preview = previewUrl; else this.bannerPreview = previewUrl;
                    this.crop.open = false;
                },
                cancelCrop() { this.crop.open = false; }
             }">
            <div class="w-full sm:max-w-[600px] min-h-screen sm:min-h-0 sm:max-h-[90vh] sm:rounded-2xl bg-[var(--surface)] sm:border border-[var(--border-color)] shadow-2xl overflow-hidden flex flex-col"
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100">

                <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="flex flex-col flex-1 min-h-0">
                    @csrf
                    @method('PUT')

                    <!-- X-style sticky top bar: Close + Title on left, Simpan pill on right -->
                    <div class="flex items-center justify-between gap-4 px-4 py-3 border-b border-[var(--border-color)] bg-[var(--surface)]/95 backdrop-blur-sm shrink-0">
                        <div class="flex items-center gap-5 min-w-0">
                            <button type="button" @click="editOpen = false" aria-label="Tutup edit profil"
                                    class="w-8 h-8 rounded-full flex items-center justify-center text-[var(--primary)] hover:bg-[var(--surface-hover)] transition cursor-pointer shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                            <h2 class="text-base font-bold tracking-tight text-[var(--primary)] truncate">Edit profil</h2>
                        </div>
                        <button type="submit"
                                class="rounded-full px-4 py-1.5 text-xs font-bold bg-[var(--primary)] text-[var(--primary-text)] hover:opacity-90 transition cursor-pointer shrink-0">
                            Simpan
                        </button>
                    </div>

                    <!-- Scrollable X-style body -->
                    <div class="flex-1 overflow-y-auto">
                        <!-- Full-bleed banner with centered camera trigger (3:1 X-style ratio) -->
                        <div class="relative w-full aspect-[3/1] bg-[var(--surface-subtle)] overflow-hidden">
                            <img x-show="bannerPreview || {{ $user->banner ? 'true' : 'false' }}"
                                 :src="bannerPreview || '{{ $user->banner }}'"
                                 class="w-full h-full object-cover" alt="Banner profil">
                            <div class="absolute inset-0 bg-black/35 flex items-center justify-center">
                                <label for="banner-file-input"
                                       title="Ubah foto sampul"
                                       class="w-11 h-11 rounded-full bg-black/60 hover:bg-black/80 text-white flex items-center justify-center transition cursor-pointer backdrop-blur-xs">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                </label>
                                <input type="file" id="banner-file-input" accept="image/*" class="hidden"
                                       @change="startCrop($event.target.files[0], 'banner'); $event.target.value = ''">
                                <input type="file" x-ref="bannerInput" name="banner" class="hidden">
                            </div>
                        </div>

                        <!-- Overlapping avatar (bottom-left of banner) -->
                        <div class="px-4 -mt-12 mb-3 relative z-10 flex items-end justify-between gap-3">
                            <div class="relative w-24 h-24 rounded-full ring-4 ring-[var(--surface)] bg-[var(--surface-subtle)] overflow-hidden shrink-0">
                                <img x-show="preview || {{ $user->avatar ? 'true' : 'false' }}"
                                     :src="preview || '{{ $user->avatar }}'"
                                     alt="{{ $user->name }}"
                                     referrerpolicy="no-referrer"
                                     class="w-full h-full object-cover">
                                <span x-show="!(preview || {{ $user->avatar ? 'true' : 'false' }})"
                                      class="w-full h-full flex items-center justify-center text-2xl font-bold bg-[var(--primary)] text-[var(--primary-text)]">{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}</span>
                                <label for="avatar"
                                       title="Ubah foto profil"
                                       class="absolute inset-0 bg-black/40 hover:bg-black/55 flex items-center justify-center transition cursor-pointer">
                                    <span class="w-10 h-10 rounded-full bg-black/60 text-white flex items-center justify-center">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                    </span>
                                </label>
                                <input type="file" id="avatar" accept="image/*" class="hidden"
                                       @change="startCrop($event.target.files[0], 'avatar'); $event.target.value = ''">
                                <input type="file" x-ref="avatarInput" name="avatar" class="hidden">
                            </div>

                            <p class="text-[11px] text-[var(--text-muted)] pb-1">Maks 5MB · klik kamera untuk crop foto</p>
                        </div>

                        <div class="px-4 pb-6 space-y-4">
                            @error('banner') <p class="text-rose-400 text-xs">{{ $message }}</p> @enderror
                            @error('avatar') <p class="text-rose-400 text-xs">{{ $message }}</p> @enderror

                            <!-- Nama field (X-style outlined box) -->
                            <div class="rounded-xl border border-[var(--border-color)] focus-within:border-[var(--primary)] bg-[var(--surface)] px-3.5 py-2 transition">
                                <label for="name" class="block text-[11px] font-medium text-[var(--text-muted)]">Nama</label>
                                <input type="text" id="name" name="name" maxlength="255"
                                       value="{{ old('name', $user->name) }}"
                                       class="composer-seamless appearance-none w-full bg-transparent border-0 p-0 pt-0.5 text-sm text-[var(--primary)] focus:outline-none focus:ring-0">
                            </div>
                            @error('name') <p class="text-rose-400 text-xs -mt-2">{{ $message }}</p> @enderror

                            <!-- Username field -->
                            <div class="space-y-1">
                                <div class="rounded-xl border border-[var(--border-color)] focus-within:border-[var(--primary)] px-3.5 py-2 transition @if (! $user->canChangeUsername()) bg-[var(--surface-subtle)] opacity-75 @else bg-[var(--surface)] @endif">
                                    <label for="username" class="block text-[11px] font-medium text-[var(--text-muted)]">Username</label>
                                    <div class="flex items-center gap-1 pt-0.5">
                                        <span class="text-sm font-mono text-[var(--text-muted)] select-none">@</span>
                                        <input type="text"
                                               id="username"
                                               name="username"
                                               value="{{ old('username', $user->username) }}"
                                               placeholder="username"
                                               @if (! $user->canChangeUsername()) readonly @endif
                                               class="composer-seamless appearance-none w-full bg-transparent border-0 p-0 text-sm font-mono text-[var(--primary)] focus:outline-none focus:ring-0 @if (! $user->canChangeUsername()) text-[var(--text-muted)] cursor-not-allowed select-none @endif">
                                    </div>
                                </div>
                                @if (! $user->canChangeUsername())
                                    <p class="text-[11px] text-amber-400 flex items-center gap-1.5 px-1 leading-snug">
                                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <span>Hanya bisa diubah 7 hari sekali. Tersedia lagi dalam <strong>{{ $user->daysUntilNextUsernameChange() }} hari</strong> ({{ $user->nextAllowedUsernameChangeDate()?->translatedFormat('d F Y') }}).</span>
                                    </p>
                                @else
                                    <p class="text-[11px] text-[var(--text-muted)] px-1">Username dapat diubah maksimal 1 kali setiap 7 hari.</p>
                                @endif
                                @error('username') <p class="text-rose-400 text-xs px-1">{{ $message }}</p> @enderror
                            </div>

                            <!-- Bio field (X-style outlined box with live counter) -->
                            <div class="rounded-xl border border-[var(--border-color)] focus-within:border-[var(--primary)] bg-[var(--surface)] px-3.5 py-2 transition">
                                <div class="flex items-center justify-between">
                                    <label for="bio" class="text-[11px] font-medium text-[var(--text-muted)]">Bio</label>
                                    <span class="text-[11px] font-mono text-[var(--text-muted)]" x-text="bioLen + '/280'"></span>
                                </div>
                                <textarea id="bio" name="bio" rows="3" maxlength="280"
                                          @input="bioLen = $event.target.value.length"
                                          placeholder="Ceritakan apa yang sedang kamu bangun..."
                                          class="composer-seamless appearance-none w-full bg-transparent border-0 p-0 pt-1 text-sm text-[var(--primary)] placeholder:text-[var(--text-faint)] resize-none focus:outline-none focus:ring-0">{{ old('bio', $user->bio) }}</textarea>
                            </div>
                            @error('bio') <p class="text-rose-400 text-xs -mt-2">{{ $message }}</p> @enderror

                            <!-- Dynamic profile links (link-in-bio), max 6 -->
                            <div class="space-y-2.5 pt-2 border-t border-[var(--border-color)]"
                                 x-data="{ links: {{ Illuminate\Support\Js::from($user->links->map(fn($l) => ['label' => $l->label, 'url' => $l->url])->values()) }} }">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-semibold text-[var(--primary)]">Tautan Profil</span>
                                    <span class="text-[11px] font-mono text-[var(--text-muted)]" x-text="links.length + '/6'"></span>
                                </div>

                                <template x-for="(link, i) in links" :key="i">
                                    <div class="flex items-center gap-2">
                                        <input type="text" :name="'links[' + i + '][label]'" x-model="link.label"
                                               placeholder="Label (mis. GitHub)" maxlength="40"
                                               class="ny-input text-xs !w-32 shrink-0">
                                        <input type="url" :name="'links[' + i + '][url]'" x-model="link.url"
                                               placeholder="https://..." class="ny-input text-xs flex-1 min-w-0">
                                        <button type="button" @click="links.splice(i, 1)"
                                                class="shrink-0 w-8 h-8 rounded-full flex items-center justify-center text-[var(--text-muted)] hover:text-rose-400 hover:bg-[var(--surface-hover)] transition cursor-pointer"
                                                title="Hapus link">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </div>
                                </template>

                                <button type="button" @click="if (links.length < 6) links.push({ label: '', url: '' })"
                                        :disabled="links.length >= 6"
                                        class="btn-secondary text-xs py-1.5 px-3 disabled:opacity-40 cursor-pointer">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    <span>Tambah Link</span>
                                </button>
                                @error('links') <p class="text-rose-400 text-xs">{{ $message }}</p> @enderror
                                @error('links.*.url') <p class="text-rose-400 text-xs">Pastikan semua URL valid.</p> @enderror
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- CROP OVERLAY -->
            <div x-show="crop.open" x-cloak
                 class="fixed inset-0 z-[60] bg-black/85 backdrop-blur-sm flex flex-col items-center justify-center p-4">
                <div class="w-full max-w-md space-y-4">
                    <p class="text-center text-xs text-white/70">Seret gambar untuk mengatur posisi · gunakan tombol zoom untuk memperbesar</p>

                    <!-- Crop frame -->
                    <div class="mx-auto rounded-xl overflow-hidden bg-black relative touch-none select-none cursor-move"
                         :style="crop.tag === 'banner' ? 'width:100%;aspect-ratio:3/1' : 'width:260px;height:260px'"
                         x-ref="cropFrame"
                         @mousedown="cropDown($event)" @mousemove="cropMove($event)" @mouseup="cropUp()" @mouseleave="cropUp()"
                         @touchstart.prevent="cropDown($event)" @touchmove.prevent="cropMove($event)" @touchend="cropUp()">
                        <img x-ref="cropImg" :src="crop.url" alt=""
                             class="absolute pointer-events-none select-none"
                             :style="`
                                width: ${(crop.natW * crop.scale) || 0}px;
                                height: ${(crop.natH * crop.scale) || 0}px;
                                left: ${crop.fw / 2 - (crop.natW * crop.scale) / 2 + crop.x}px;
                                top: ${crop.fh / 2 - (crop.natH * crop.scale) / 2 + crop.y}px;
                             `">
                        <!-- guide overlay -->
                        <div class="absolute inset-0 pointer-events-none" :class="crop.tag === 'avatar' ? 'rounded-full' : ''"
                             style="box-shadow: 0 0 0 9999px rgba(0,0,0,0.35);"></div>
                    </div>

                    <!-- Zoom controls -->
                    <div class="flex items-center justify-center gap-3">
                        <button type="button" @click="cropZoom(-0.1)" class="w-9 h-9 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                        </button>
                        <input type="range" min="0.1" max="4" step="0.01" :value="crop.scale" @input="crop.scale = parseFloat($event.target.value); clampCrop()" class="w-40 accent-[var(--primary)]">
                        <button type="button" @click="cropZoom(0.1)" class="w-9 h-9 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        </button>
                    </div>

                    <div class="flex items-center justify-center gap-2 pt-1">
                        <button type="button" @click="cancelCrop()" class="btn-secondary text-xs py-2 px-4 cursor-pointer">Batal</button>
                        <button type="button" @click="applyCrop()" class="btn-primary text-xs py-2 px-4 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Terapkan</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ===================== DELETE DRAFT DANGER MODAL ===================== -->
        <div x-show="deleteDraftModalOpen"
             x-cloak
             @keydown.escape.window="deleteDraftModalOpen = false"
             class="fixed inset-0 z-50 overflow-y-auto"
             role="dialog"
             aria-modal="true">
            <div class="fixed inset-0 bg-black/80 backdrop-blur-xs" @click="deleteDraftModalOpen = false"></div>
            <div class="min-h-full flex items-center justify-center p-4">
                <div class="relative w-full max-w-md bg-[var(--surface)] border border-[var(--border-color)] rounded-2xl p-6 shadow-2xl space-y-4 text-[var(--primary)]">
                    <div class="flex items-start gap-3">
                        <span class="w-10 h-10 rounded-xl bg-rose-500/10 border border-rose-500/25 text-rose-400 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </span>
                        <div class="min-w-0 space-y-1">
                            <h3 class="text-base font-bold text-rose-400">Hapus Draft Permanen</h3>
                            <p class="text-xs text-[var(--text-muted)] break-words" x-text="deleteDraftTitle"></p>
                        </div>
                    </div>

                    <p class="text-xs text-rose-200/80 px-3.5 py-2.5 rounded-xl bg-rose-500/10 border border-rose-500/25">
                        Draft yang dihapus tidak dapat dipulihkan lagi.
                    </p>

                    <form method="POST" :action="deleteDraftUrl" class="space-y-4">
                        @csrf
                        @method('DELETE')
                        <div class="flex items-center justify-end gap-2">
                            <button type="button" @click="deleteDraftModalOpen = false" class="btn-secondary text-xs py-2 px-4 cursor-pointer">Batal</button>
                            <button type="submit" class="px-5 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs cursor-pointer transition">Hapus Permanen</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

</div>
@endsection
