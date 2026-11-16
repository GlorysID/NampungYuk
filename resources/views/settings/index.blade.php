@extends('layouts.app')

@section('title', 'Pengaturan Akun — NampungYuk')
@section('header_title', 'Pengaturan')
@section('header_subtitle', '@' . $user->username)
@section('main_width', 'max-w-none')
@section('hide_right_rail', '1')

@section('content')
@php
    $hasPassword = filled($user->password);
    $privacy = $preferences['privacy'];
    $notifPrefs = $preferences['notifications'];
    $display = $preferences['display'];

    // Determine which tab holds any validation error so the user lands right on it.
    $initialTab = match (true) {
        $errors->has('current_password') || $errors->has('password') => 'keamanan',
        $errors->has('block_username') => 'privasi',
        $errors->has('email') || $errors->has('delete_password') || $errors->has('confirm_username') => 'akun',
        default => $activeTab,
    };

    $navItems = [
        ['id' => 'akun', 'label' => 'Akun Anda', 'desc' => 'Email, verifikasi, arsip & hapus akun', 'keywords' => 'akun email verifikasi ekspor data hapus'],
        ['id' => 'keamanan', 'label' => 'Keamanan & Sesi', 'desc' => 'Password, Google login & perangkat aktif', 'keywords' => 'keamanan password sandi sesi perangkat login'],
        ['id' => 'privasi', 'label' => 'Privasi & Blokir', 'desc' => 'Kontrol DM, repost, direktori & blokir', 'keywords' => 'privasi dm obrolan pesan blokir repost direktori'],
        ['id' => 'notifikasi', 'label' => 'Notifikasi', 'desc' => 'Filter aktivitas, komentar & pesan', 'keywords' => 'notifikasi lonceng pengikut komentar pesan repost'],
        ['id' => 'tampilan', 'label' => 'Preferensi & Pintasan', 'desc' => 'Autoplay & shortcut', 'keywords' => 'tampilan feed autoplay keyboard pintasan shortcut'],
    ];
@endphp

<div class="max-w-5xl mx-auto"
     x-data="{
         tab: '{{ $initialTab }}',
         search: '',
         deleteOpen: {{ ($errors->has('delete_password') || $errors->has('confirm_username')) ? 'true' : 'false' }},
         switchTab(next) {
             this.tab = next;
             const url = new URL(window.location.href);
             url.searchParams.set('tab', next);
             window.history.replaceState({}, '', url);
         },
         matchesFilter(keywords) {
             const q = this.search.trim().toLowerCase();
             return !q || keywords.toLowerCase().includes(q);
         },
         blockBusy: null,
         blockRemoved: [],
         blockToast: { show: false, message: '', type: 'success', timeout: null },
         flashBlock(message, type = 'success') {
             clearTimeout(this.blockToast.timeout);
             this.blockToast.message = message;
             this.blockToast.type = type;
             this.blockToast.show = true;
             this.blockToast.timeout = setTimeout(() => { this.blockToast.show = false; }, 4000);
         },
         async unblockUser(id, username) {
             if (this.blockBusy) { return; }
             this.blockBusy = id;
             try {
                 const res = await fetch('{{ url('/pengaturan/blokir') }}/' + id, {
                     method: 'POST',
                     headers: {
                         'Accept': 'application/json',
                         'X-Requested-With': 'XMLHttpRequest',
                         'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                     },
                     body: new URLSearchParams({ _method: 'DELETE', _token: '{{ csrf_token() }}' }),
                 });
                 const data = await res.json().catch(() => ({}));
                 if (!res.ok) { throw new Error(data.message || 'Gagal membuka blokir. Coba lagi.'); }
                 this.blockRemoved = [...this.blockRemoved, id];
                 this.flashBlock(data.message || ('Blokir untuk @' + username + ' telah dibuka.'));
             } catch (e) {
                 this.flashBlock(e.message || 'Gagal membuka blokir. Coba lagi.', 'error');
             } finally {
                 this.blockBusy = null;
             }
         }
     }">

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-[var(--border-color)]">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-[var(--primary)] tracking-tight">Pengaturan Akun</h1>
            <p class="text-xs sm:text-sm text-[var(--text-muted)] mt-1">Kelola akun, keamanan sesi, privasi interaksi, notifikasi, dan preferensi kerja di NampungYuk.</p>
        </div>
        <div class="flex items-center gap-2 shrink-0">
            <a href="{{ route('profile.show', $user->username) }}" class="btn-secondary text-xs py-2 px-3.5">
                <span>Lihat Profil</span>
            </a>
        </div>
    </div>

    <!-- 2-Pane Social Settings Shell -->
    <div class="grid grid-cols-1 lg:grid-cols-[260px_minmax(0,1fr)] gap-6 pt-6 items-start">

        <!-- Left Navigation Rail -->
        <aside class="lg:sticky lg:top-24 space-y-3">
            <!-- Quick settings search -->
            <div class="flex items-center gap-2 rounded-xl bg-[var(--surface)] border border-[var(--border-color)] focus-within:border-[var(--primary)] px-3 py-2 transition">
                <svg class="w-4 h-4 text-[var(--text-muted)] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z"/>
                </svg>
                <input type="text"
                       x-model="search"
                       placeholder="Cari pengaturan..."
                       aria-label="Cari pengaturan"
                       class="composer-seamless appearance-none w-full bg-transparent border-0 p-0 text-xs text-[var(--primary)] placeholder:text-[var(--text-muted)] focus:outline-none focus:ring-0">
            </div>

            <!-- Category Tabs (horizontal scroll on mobile, vertical menu on desktop) -->
            <nav aria-label="Kategori Pengaturan" class="flex lg:flex-col gap-1.5 overflow-x-auto pb-1 lg:pb-0 no-scrollbar">
                @foreach($navItems as $item)
                    <button type="button"
                            x-show="matchesFilter('{{ $item['keywords'] }}')"
                            @click="switchTab('{{ $item['id'] }}')"
                            :class="tab === '{{ $item['id'] }}'
                                ? 'bg-[var(--surface)] border-[var(--border-color)] text-[var(--primary)]'
                                : 'bg-transparent border-transparent text-[var(--text-muted)] hover:bg-[var(--surface-hover)] hover:text-[var(--primary)]'"
                            class="text-left px-3.5 py-2.5 rounded-xl border transition cursor-pointer shrink-0 lg:shrink lg:w-full flex items-center justify-between gap-2">
                        <div class="min-w-0">
                            <p class="text-xs sm:text-[13px] font-semibold truncate">{{ $item['label'] }}</p>
                            <p class="hidden lg:block text-[11px] text-[var(--text-muted)] truncate mt-0.5">{{ $item['desc'] }}</p>
                        </div>
                        <svg class="hidden lg:block w-3.5 h-3.5 shrink-0 transition"
                             :class="tab === '{{ $item['id'] }}' ? 'text-[var(--primary)] translate-x-0.5' : 'text-[var(--text-faint)]'"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>
                @endforeach
            </nav>
        </aside>

        <!-- Right Settings Panels -->
        <div class="space-y-5 min-w-0">

            <!-- ================= TAB 1: AKUN ANDA ================= -->
            <div x-show="tab === 'akun'" class="space-y-5">
                <!-- Account Snapshot -->
                <section class="rounded-2xl bg-[var(--surface)] border border-[var(--border-color)] overflow-hidden">
                    <div class="px-5 py-3.5 border-b border-[var(--border-color)] flex items-center justify-between gap-3">
                        <div>
                            <h2 class="text-sm font-bold text-[var(--primary)]">Informasi Akun</h2>
                            <p class="text-[11px] text-[var(--text-muted)] mt-0.5">Identitas dasar akun NampungYuk kamu.</p>
                        </div>
                        <span class="text-[11px] font-mono text-[var(--text-muted)]">&#64;{{ $user->username }}</span>
                    </div>

                    <div class="p-5 space-y-5">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div class="flex items-center gap-3.5 min-w-0">
                                <x-user-avatar :user="$user" size="md" class="shrink-0" />
                                <div class="min-w-0">
                                    <p class="text-sm font-bold text-[var(--primary)] truncate">{{ $user->name }}</p>
                                    <p class="text-xs text-[var(--text-muted)] truncate">{{ $user->email }}</p>
                                    <p class="text-[11px] font-mono text-[var(--text-faint)] mt-0.5">
                                        Bergabung {{ $user->created_at?->translatedFormat('d F Y') }}
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-center gap-2 shrink-0 flex-wrap">
                                @if($user->hasVerifiedEmail())
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                        Email Terverifikasi
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-medium bg-amber-500/10 text-amber-400 border border-amber-500/20">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                                        Belum Terverifikasi
                                    </span>
                                @endif
                                <a href="{{ route('profile.show', $user->username) }}" class="btn-secondary text-xs py-1.5 px-3">
                                    Edit Profil
                                </a>
                            </div>
                        </div>

                        <!-- Account Stats Grid -->
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-px rounded-xl overflow-hidden border border-[var(--border-color)] bg-[var(--border-color)]">
                            <div class="bg-[var(--surface-subtle)] px-3.5 py-2.5">
                                <p class="text-[10px] font-mono uppercase tracking-wider text-[var(--text-muted)]">Karya</p>
                                <p class="text-base font-bold font-mono text-[var(--primary)] mt-0.5">{{ number_format($user->projects_count ?? 0) }}</p>
                            </div>
                            <div class="bg-[var(--surface-subtle)] px-3.5 py-2.5">
                                <p class="text-[10px] font-mono uppercase tracking-wider text-[var(--text-muted)]">Diskusi</p>
                                <p class="text-base font-bold font-mono text-[var(--primary)] mt-0.5">{{ number_format($user->comments_count ?? 0) }}</p>
                            </div>
                            <div class="bg-[var(--surface-subtle)] px-3.5 py-2.5">
                                <p class="text-[10px] font-mono uppercase tracking-wider text-[var(--text-muted)]">Pengikut</p>
                                <p class="text-base font-bold font-mono text-[var(--primary)] mt-0.5">{{ number_format($user->followers_count ?? 0) }}</p>
                            </div>
                            <div class="bg-[var(--surface-subtle)] px-3.5 py-2.5">
                                <p class="text-[10px] font-mono uppercase tracking-wider text-[var(--text-muted)]">Mengikuti</p>
                                <p class="text-base font-bold font-mono text-[var(--primary)] mt-0.5">{{ number_format($user->following_count ?? 0) }}</p>
                            </div>
                        </div>

                        @if(! $user->hasVerifiedEmail())
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-3.5 rounded-xl bg-[var(--surface-subtle)] border border-[var(--border-color)]">
                                <p class="text-xs text-[var(--text-muted)]">
                                    Verifikasi email kamu untuk menjaga keamanan pemulihan akun.
                                </p>
                                <form method="POST" action="{{ route('settings.verification.resend') }}" class="shrink-0">
                                    @csrf
                                    <button type="submit" class="btn-secondary text-xs py-1.5 px-3 cursor-pointer">
                                        Kirim Ulang Verifikasi
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                </section>

                <!-- Email Address -->
                <section class="rounded-2xl bg-[var(--surface)] border border-[var(--border-color)] overflow-hidden">
                    <div class="px-5 py-3.5 border-b border-[var(--border-color)]">
                        <h2 class="text-sm font-bold text-[var(--primary)]">Alamat Email</h2>
                        <p class="text-xs text-[var(--text-muted)] mt-0.5">Mengubah alamat email akan mengirim kode verifikasi ke alamat baru.</p>
                    </div>

                    <div class="p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="min-w-0">
                            <p class="text-sm font-mono text-[var(--primary)] truncate">{{ $user->email }}</p>
                            <div class="mt-1.5">
                                @if($user->hasVerifiedEmail())
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                        Terverifikasi
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-medium bg-amber-500/10 text-amber-400 border border-amber-500/20">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                                        Belum Terverifikasi
                                    </span>
                                @endif
                            </div>
                        </div>
                        <a href="{{ route('settings.email.edit') }}" class="btn-secondary text-xs py-2 px-4 shrink-0 self-start sm:self-auto">
                            Ubah Email
                        </a>
                    </div>
                </section>

                <!-- Export Data Archive -->
                <section class="rounded-2xl bg-[var(--surface)] border border-[var(--border-color)] overflow-hidden">
                    <div class="p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="space-y-1">
                            <h2 class="text-sm font-bold text-[var(--primary)]">Unduh Arsip Data Anda</h2>
                            <p class="text-xs text-[var(--text-muted)] max-w-xl">
                                Ekspor salinan profil, tautan, daftar karya, komentar, dan statistik akunmu dalam format JSON portabel.
                            </p>
                        </div>
                        <a href="{{ route('settings.export') }}" class="btn-secondary text-xs py-2 px-4 shrink-0">
                            Unduh Arsip (.json)
                        </a>
                    </div>
                </section>

                <!-- Danger Zone: Delete Account -->
                <section class="rounded-2xl bg-[var(--surface)] border border-rose-500/30 overflow-hidden">
                    <div class="p-5 space-y-4">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div class="space-y-1">
                                <h2 class="text-sm font-bold text-rose-400">Hapus Akun Permanen</h2>
                                <p class="text-xs text-[var(--text-muted)] max-w-xl">
                                    Menghapus akun akan menghapus profil, karya, dan komentar kamu secara permanen serta tidak bisa dipulihkan.
                                </p>
                            </div>
                            <button type="button"
                                    @click="deleteOpen = !deleteOpen"
                                    class="px-4 py-2 rounded-full border border-rose-500/40 text-xs font-semibold text-rose-400 hover:bg-rose-500/10 transition cursor-pointer shrink-0">
                                <span x-text="deleteOpen ? 'Tutup Konfirmasi' : 'Hapus Akun'"></span>
                            </button>
                        </div>

                        <form x-show="deleteOpen"
                              x-cloak
                              method="POST"
                              action="{{ route('settings.account.destroy') }}"
                              class="pt-4 border-t border-rose-500/20 space-y-3">
                            @csrf
                            @method('DELETE')

                            @if($hasPassword)
                                <div class="space-y-1.5">
                                    <label for="delete_password" class="block text-xs font-semibold text-[var(--primary)]">
                                        Masukkan Password untuk Konfirmasi
                                    </label>
                                    <input type="password"
                                           id="delete_password"
                                           name="delete_password"
                                           required
                                           placeholder="Password akunmu"
                                           class="ny-input text-sm w-full sm:max-w-sm">
                                    @error('delete_password') <p class="text-rose-400 text-xs">{{ $message }}</p> @enderror
                                </div>
                            @else
                                <div class="space-y-1.5">
                                    <label for="confirm_username" class="block text-xs font-semibold text-[var(--primary)]">
                                        Ketik <span class="font-mono text-rose-400">{{ $user->username }}</span> untuk Konfirmasi
                                    </label>
                                    <input type="text"
                                           id="confirm_username"
                                           name="confirm_username"
                                           required
                                           placeholder="{{ $user->username }}"
                                           class="ny-input text-sm font-mono w-full sm:max-w-sm">
                                    @error('confirm_username') <p class="text-rose-400 text-xs">{{ $message }}</p> @enderror
                                </div>
                            @endif

                            <div class="flex items-center justify-end gap-2">
                                <button type="button" @click="deleteOpen = false" class="btn-secondary text-xs py-2 px-4 cursor-pointer">Batal</button>
                                <button type="submit" class="px-4 py-2 rounded-full bg-rose-600 hover:bg-rose-500 text-white text-xs font-bold transition cursor-pointer">
                                    Hapus Permanen Sekarang
                                </button>
                            </div>
                        </form>
                    </div>
                </section>
            </div>

            <!-- ================= TAB 2: KEAMANAN & SESI ================= -->
            <div x-show="tab === 'keamanan'" x-cloak class="space-y-5">
                <!-- Connected Sign-in Methods -->
                <section class="rounded-2xl bg-[var(--surface)] border border-[var(--border-color)] overflow-hidden">
                    <div class="px-5 py-3.5 border-b border-[var(--border-color)]">
                        <h2 class="text-sm font-bold text-[var(--primary)]">Metode Masuk Terhubung</h2>
                        <p class="text-xs text-[var(--text-muted)] mt-0.5">Opsi otentikasi yang aktif pada akun ini.</p>
                    </div>

                    <div class="divide-y divide-[var(--border-color)]">
                        <div class="px-5 py-3.5 flex items-center justify-between gap-4">
                            <div>
                                <p class="text-xs font-semibold text-[var(--primary)]">Kata Sandi &amp; Email</p>
                                <p class="text-[11px] text-[var(--text-muted)] mt-0.5">Masuk menggunakan username atau email serta password.</p>
                            </div>
                            <span class="px-2.5 py-1 rounded-full text-[11px] font-medium border {{ $hasPassword ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : 'bg-[var(--surface-subtle)] text-[var(--text-muted)] border-[var(--border-color)]' }}">
                                {{ $hasPassword ? 'Aktif' : 'Belum Disetel' }}
                            </span>
                        </div>

                        @php
                            $googleConnected = filled($user->google_id) || $user->hasVerifiedEmail();
                        @endphp
                        <div class="px-5 py-3.5 flex items-center justify-between gap-4">
                            <div>
                                <p class="text-xs font-semibold text-[var(--primary)]">Google Sign-In</p>
                                <p class="text-[11px] text-[var(--text-muted)] mt-0.5">
                                    @if ($user->google_id)
                                        Masuk instan satu klik dengan akun Google terverifikasi.
                                    @elseif ($googleConnected)
                                        Email akun ini sudah terverifikasi, jadi tersambung otomatis begitu kamu masuk dengan Google.
                                    @else
                                        Verifikasi email akunmu dulu supaya bisa tersambung otomatis dengan Google.
                                    @endif
                                </p>
                            </div>
                            <span class="px-2.5 py-1 rounded-full text-[11px] font-medium border {{ $googleConnected ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : 'bg-[var(--surface-subtle)] text-[var(--text-muted)] border-[var(--border-color)]' }}">
                                {{ $googleConnected ? 'Terhubung' : 'Tidak Terhubung' }}
                            </span>
                        </div>
                    </div>
                </section>

                <!-- Security & Password Card -->
                <section id="keamanan" class="rounded-2xl bg-[var(--surface)] border border-[var(--border-color)] overflow-hidden">
                    <div class="px-5 py-3.5 border-b border-[var(--border-color)]">
                        <h2 class="text-sm font-bold text-[var(--primary)]">Keamanan &amp; Password</h2>
                        <p class="text-xs text-[var(--text-muted)] mt-0.5">
                            @if($hasPassword)
                                Perbarui password secara berkala untuk menjaga keamanan akunmu.
                            @else
                                Akun ini masuk melalui Google. Kamu bisa membuat password supaya juga dapat masuk memakai username atau email.
                            @endif
                        </p>
                    </div>

                    <div class="p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="min-w-0">
                            <p class="text-xs font-semibold text-[var(--primary)]">
                                {{ $hasPassword ? 'Password akun sudah aktif' : 'Password belum disetel' }}
                            </p>
                            <p class="text-[11px] text-[var(--text-muted)] mt-0.5">
                                {{ $hasPassword ? 'Masuk memakai username atau email serta password.' : 'Saat ini akun hanya bisa masuk lewat Google.' }}
                            </p>
                            <div class="mt-2">
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-medium border {{ $hasPassword ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : 'bg-[var(--surface-subtle)] text-[var(--text-muted)] border-[var(--border-color)]' }}">
                                    {{ $hasPassword ? 'Aktif' : 'Belum Disetel' }}
                                </span>
                            </div>
                        </div>
                        <a href="{{ route('settings.password.edit') }}" class="btn-primary text-xs py-2.5 px-5 shrink-0 self-start sm:self-auto">
                            {{ $hasPassword ? 'Ubah Password' : 'Buat Password' }}
                        </a>
                    </div>
                </section>

                <!-- Active Browser & Device Sessions -->
                <section class="rounded-2xl bg-[var(--surface)] border border-[var(--border-color)] overflow-hidden">
                    <div class="px-5 py-3.5 border-b border-[var(--border-color)] flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div>
                            <h2 class="text-sm font-bold text-[var(--primary)]">Sesi &amp; Perangkat Aktif</h2>
                            <p class="text-xs text-[var(--text-muted)] mt-0.5">Kelola perangkat yang saat ini masuk menggunakan akunmu.</p>
                        </div>
                        @if($sessions->where('is_current', false)->isNotEmpty())
                            <form method="POST" action="{{ route('settings.sessions.destroyOthers') }}" class="shrink-0">
                                @csrf
                                <button type="submit" class="btn-secondary text-xs py-1.5 px-3 cursor-pointer">
                                    Keluar Semua Perangkat Lain
                                </button>
                            </form>
                        @endif
                    </div>

                    <div class="divide-y divide-[var(--border-color)]">
                        @forelse($sessions as $session)
                            <div class="px-5 py-3.5 flex items-center justify-between gap-4">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-9 h-9 rounded-xl bg-[var(--surface-subtle)] border border-[var(--border-color)] flex items-center justify-center text-[var(--text-muted)] shrink-0">
                                        @if($session->device === 'mobile')
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                        @else
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <p class="text-xs font-semibold text-[var(--primary)] truncate">
                                                {{ $session->platform }} · {{ $session->browser }}
                                            </p>
                                            @if($session->is_current)
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                                    Perangkat ini
                                                </span>
                                            @endif
                                        </div>
                                        <p class="text-[11px] font-mono text-[var(--text-muted)] mt-0.5 truncate">
                                            {{ $session->ip_address }} · Aktif {{ $session->last_active }}
                                        </p>
                                    </div>
                                </div>

                                @unless($session->is_current)
                                    <form method="POST" action="{{ route('settings.sessions.destroy', $session->id) }}" class="shrink-0">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs font-semibold text-rose-400 hover:underline cursor-pointer">
                                            Keluar
                                        </button>
                                    </form>
                                @endunless
                            </div>
                        @empty
                            <div class="px-5 py-6 text-xs text-[var(--text-muted)]">
                                Informasi sesi database belum tersedia pada driver sesi aktif.
                            </div>
                        @endforelse
                    </div>
                </section>
            </div>

            <!-- ================= TAB 3: PRIVASI & BLOKIR ================= -->
            <div x-show="tab === 'privasi'" x-cloak class="space-y-5">
                <section class="rounded-2xl bg-[var(--surface)] border border-[var(--border-color)] overflow-hidden">
                    <div class="px-5 py-3.5 border-b border-[var(--border-color)]">
                        <h2 class="text-sm font-bold text-[var(--primary)]">Privasi &amp; Keamanan Sosial</h2>
                        <p class="text-xs text-[var(--text-muted)] mt-0.5">Atur siapa yang dapat mengirim obrolan, me-repost karya, dan menemukan akunmu.</p>
                    </div>

                    <form method="POST" action="{{ route('settings.privacy.update') }}" class="p-5 space-y-5">
                        @csrf
                        @method('PUT')

                        <!-- DM Policy -->
                        <div class="space-y-2.5">
                            <p class="text-xs font-semibold text-[var(--primary)]">Siapa yang boleh mengirim pesan langsung (Obrolan)?</p>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <label class="flex items-start gap-3 p-3.5 rounded-xl border border-[var(--border-color)] bg-[var(--surface-subtle)] cursor-pointer hover:border-[var(--border-strong)] has-[:checked]:border-emerald-500/50 has-[:checked]:bg-[var(--surface)] transition">
                                    <input type="radio" name="dm_policy" value="everyone"
                                           @checked($privacy['dm_policy'] === 'everyone')
                                           onchange="nySettingSave(this)"
                                           class="mt-0.5 accent-emerald-500">
                                    <div>
                                        <p class="text-xs font-semibold text-[var(--primary)]">Semua Orang</p>
                                        <p class="text-[11px] text-[var(--text-muted)] mt-0.5">Siapa pun bisa memulai obrolan. Akun yang belum kamu ikuti masuk sebagai permintaan pesan.</p>
                                    </div>
                                </label>
                                <label class="flex items-start gap-3 p-3.5 rounded-xl border border-[var(--border-color)] bg-[var(--surface-subtle)] cursor-pointer hover:border-[var(--border-strong)] has-[:checked]:border-emerald-500/50 has-[:checked]:bg-[var(--surface)] transition">
                                    <input type="radio" name="dm_policy" value="following_only"
                                           @checked($privacy['dm_policy'] === 'following_only')
                                           onchange="nySettingSave(this)"
                                           class="mt-0.5 accent-emerald-500">
                                    <div>
                                        <p class="text-xs font-semibold text-[var(--primary)]">Hanya yang Saya Ikuti</p>
                                        <p class="text-[11px] text-[var(--text-muted)] mt-0.5">Hanya akun yang kamu ikuti yang bisa memulai obrolan. Akun lain ditolak, bukan jadi permintaan pesan.</p>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Toggle Switches -->
                        <div class="divide-y divide-[var(--border-color)] border-t border-[var(--border-color)]">
                            @foreach([
                                ['name' => 'allow_reposts', 'checked' => $privacy['allow_reposts'], 'title' => 'Izinkan repost karya', 'desc' => 'Developer lain dapat membagikan ulang proyek publikmu ke profil mereka.'],
                                ['name' => 'discoverable', 'checked' => $privacy['discoverable'], 'title' => 'Tampilkan di Direktori Kreator & pencarian', 'desc' => 'Memungkinkan akunmu ditemukan di daftar kreator publik.'],
                                ['name' => 'show_read_receipts', 'checked' => $privacy['show_read_receipts'], 'title' => 'Tanda dibaca di obrolan', 'desc' => 'Tampilkan status bahwa pesan sudah kamu baca di ruang obrolan.'],
                            ] as $toggle)
                                <label class="py-3.5 flex items-center justify-between gap-4 cursor-pointer group">
                                    <div class="min-w-0">
                                        <p class="text-xs font-semibold text-[var(--primary)]">{{ $toggle['title'] }}</p>
                                        <p class="text-[11px] text-[var(--text-muted)] mt-0.5">{{ $toggle['desc'] }}</p>
                                    </div>
                                    <span class="relative inline-flex items-center shrink-0">
                                        <input type="checkbox" name="{{ $toggle['name'] }}" value="1" @checked($toggle['checked']) onchange="nySettingSave(this)" class="sr-only peer">
                                        <span class="w-10 h-[22px] rounded-full bg-[var(--surface-subtle)] border border-[var(--border-strong)] peer-checked:bg-emerald-500 peer-checked:border-emerald-500 transition-colors duration-150 peer-focus-visible:ring-2 peer-focus-visible:ring-emerald-400/50"></span>
                                        <span class="absolute left-[3px] top-1/2 -translate-y-1/2 w-4 h-4 rounded-full bg-[var(--text-muted)] peer-checked:bg-[#0B0B0D] peer-checked:translate-x-[18px] transition-all duration-150 pointer-events-none shadow-xs"></span>
                                    </span>
                                </label>
                            @endforeach
                        </div>

                        <div class="pt-2 flex justify-end border-t border-[var(--border-color)]">
                            <button type="submit" class="btn-primary text-xs py-2.5 px-5 cursor-pointer">
                                Simpan Privasi
                            </button>
                        </div>
                    </form>
                </section>

                <!-- Blocked Accounts -->
                <section class="rounded-2xl bg-[var(--surface)] border border-[var(--border-color)] overflow-hidden">
                    <div class="px-5 py-3.5 border-b border-[var(--border-color)]">
                        <h2 class="text-sm font-bold text-[var(--primary)]">Akun yang Diblokir</h2>
                        <p class="text-xs text-[var(--text-muted)] mt-0.5">Akun yang diblokir tidak dapat mengikuti atau mengirim obrolan kepadamu.</p>
                    </div>

                    <div class="p-5 space-y-4">
                        <form method="POST" action="{{ route('settings.blocks.store') }}" class="flex flex-col sm:flex-row gap-2.5">
                            @csrf
                            <div class="flex-1 flex items-center gap-1.5 rounded-xl bg-[var(--surface-subtle)] border border-[var(--border-color)] focus-within:border-[var(--primary)] px-3.5 py-2 transition">
                                <span class="text-xs font-mono text-[var(--text-muted)] select-none">@</span>
                                <input type="text"
                                       name="username"
                                       required
                                       placeholder="Masukkan username untuk diblokir"
                                       class="composer-seamless appearance-none w-full bg-transparent border-0 p-0 text-xs font-mono text-[var(--primary)] placeholder:text-[var(--text-muted)] focus:outline-none focus:ring-0">
                            </div>
                            <button type="submit" class="btn-secondary text-xs py-2 px-4 cursor-pointer shrink-0">
                                Blokir Akun
                            </button>
                        </form>
                        @error('block_username') <p class="text-rose-400 text-xs">{{ $message }}</p> @enderror

                        <div class="divide-y divide-[var(--border-color)] border-t border-[var(--border-color)]">
                            @foreach($blockedUsers as $blocked)
                                <div x-show="!blockRemoved.includes({{ $blocked->id }})"
                                     x-transition:leave="transition ease-in duration-150"
                                     x-transition:leave-start="opacity-100"
                                     x-transition:leave-end="opacity-0"
                                     class="py-3 flex items-center justify-between gap-3">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <x-user-avatar :user="$blocked" size="sm" class="shrink-0" />
                                        <div class="min-w-0">
                                            <p class="text-xs font-bold text-[var(--primary)] truncate">{{ $blocked->name }}</p>
                                            <p class="text-[11px] font-mono text-[var(--text-muted)] truncate">&#64;{{ $blocked->username }}</p>
                                        </div>
                                    </div>
                                    <form method="POST"
                                          action="{{ route('settings.blocks.destroy', $blocked) }}"
                                          class="shrink-0"
                                          @submit.prevent="unblockUser({{ $blocked->id }}, '{{ addslashes($blocked->username) }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                :disabled="blockBusy === {{ $blocked->id }}"
                                                class="btn-secondary text-xs py-1.5 px-3 cursor-pointer disabled:opacity-60 disabled:cursor-wait">
                                            <span x-text="blockBusy === {{ $blocked->id }} ? 'Membuka...' : 'Buka Blokir'">Buka Blokir</span>
                                        </button>
                                    </form>
                                </div>
                            @endforeach
                            <p x-show="blockRemoved.length >= {{ $blockedUsers->count() }}"
                               x-cloak
                               class="pt-3 text-xs text-[var(--text-muted)]">Belum ada akun yang kamu blokir.</p>
                        </div>
                    </div>
                </section>
            </div>

            <!-- ================= TAB 4: NOTIFIKASI ================= -->
            <div x-show="tab === 'notifikasi'" x-cloak class="space-y-5">
                <section class="rounded-2xl bg-[var(--surface)] border border-[var(--border-color)] overflow-hidden">
                    <div class="px-5 py-3.5 border-b border-[var(--border-color)]">
                        <h2 class="text-sm font-bold text-[var(--primary)]">Preferensi Notifikasi</h2>
                        <p class="text-xs text-[var(--text-muted)] mt-0.5">Pilih jenis aktivitas yang ingin memunculkan notifikasi di lonceng dan halaman notifikasi.</p>
                    </div>

                    <form method="POST" action="{{ route('settings.notifications.update') }}" class="p-5 space-y-4">
                        @csrf
                        @method('PUT')

                        @php
                            $notificationItems = [
                                ['key' => 'new_follower', 'title' => 'Pengikut Baru', 'desc' => 'Beritahu ketika ada developer lain yang mulai mengikuti profilmu.'],
                                ['key' => 'project_comment', 'title' => 'Komentar & Balasan Diskusi', 'desc' => 'Beritahu saat ada komentar pada karya atau balasan diskusi.'],
                                ['key' => 'project_repost', 'title' => 'Repost Karya', 'desc' => 'Beritahu ketika seseorang membagikan ulang karyamu ke profil mereka.'],
                                ['key' => 'new_message', 'title' => 'Pesan Obrolan Baru', 'desc' => 'Tampilkan notifikasi untuk pesan masuk atau permintaan obrolan baru.'],
                                ['key' => 'project_uploaded', 'title' => 'Karya Baru dari Kreator Diikuti', 'desc' => 'Kabar saat kreator yang kamu ikuti merilis project baru.'],
                                ['key' => 'project_trending', 'title' => 'Karya Masuk Trending', 'desc' => 'Pemberitahuan khusus saat karyamu berhasil masuk daftar Trending.'],
                            ];
                        @endphp

                        <div class="divide-y divide-[var(--border-color)]">
                            @foreach($notificationItems as $item)
                                <label class="py-3.5 first:pt-0 flex items-center justify-between gap-4 cursor-pointer group">
                                    <div class="min-w-0">
                                        <p class="text-xs font-semibold text-[var(--primary)]">{{ $item['title'] }}</p>
                                        <p class="text-[11px] text-[var(--text-muted)] mt-0.5">{{ $item['desc'] }}</p>
                                    </div>
                                    <span class="relative inline-flex items-center shrink-0">
                                        <input type="checkbox"
                                               name="{{ $item['key'] }}"
                                               value="1"
                                               @checked($notifPrefs[$item['key']])
                                               onchange="nySettingSave(this)"
                                               class="sr-only peer">
                                        <span class="w-10 h-[22px] rounded-full bg-[var(--surface-subtle)] border border-[var(--border-strong)] peer-checked:bg-emerald-500 peer-checked:border-emerald-500 transition-colors duration-150 peer-focus-visible:ring-2 peer-focus-visible:ring-emerald-400/50"></span>
                                        <span class="absolute left-[3px] top-1/2 -translate-y-1/2 w-4 h-4 rounded-full bg-[var(--text-muted)] peer-checked:bg-[#0B0B0D] peer-checked:translate-x-[18px] transition-all duration-150 pointer-events-none shadow-xs"></span>
                                    </span>
                                </label>
                            @endforeach
                        </div>

                        <div class="pt-3 flex justify-end border-t border-[var(--border-color)]">
                            <button type="submit" class="btn-primary text-xs py-2.5 px-5 cursor-pointer">
                                Simpan Notifikasi
                            </button>
                        </div>
                    </form>
                </section>
            </div>

            <!-- ================= TAB 5: PREFERENSI & PINTASAN ================= -->
            <div x-show="tab === 'tampilan'" x-cloak class="space-y-5">
                <section class="rounded-2xl bg-[var(--surface)] border border-[var(--border-color)] overflow-hidden">
                    <div class="px-5 py-3.5 border-b border-[var(--border-color)]">
                        <h2 class="text-sm font-bold text-[var(--primary)]">Preferensi Feed &amp; Navigasi</h2>
                        <p class="text-xs text-[var(--text-muted)] mt-0.5">Atur pemutaran media dan pintasan keyboard di ruang kerja.</p>
                    </div>

                    <form method="POST" action="{{ route('settings.preferences.update') }}" class="p-5 space-y-5">
                        @csrf
                        @method('PUT')

                        <div class="divide-y divide-[var(--border-color)] border-t border-[var(--border-color)]">
                            @foreach([
                                ['name' => 'autoplay_media', 'checked' => $display['autoplay_media'], 'title' => 'Putar otomatis demo video', 'desc' => 'Memutar video pratinjau tanpa suara secara otomatis saat berada di halaman detail.'],
                                ['name' => 'keyboard_shortcuts', 'checked' => $display['keyboard_shortcuts'], 'title' => 'Aktifkan pintasan keyboard', 'desc' => 'Gunakan tombol navigasi cepat seperti J/K, U untuk upvote, dan / untuk mencari.'],
                            ] as $toggle)
                                <label class="py-3.5 flex items-center justify-between gap-4 cursor-pointer group">
                                    <div class="min-w-0">
                                        <p class="text-xs font-semibold text-[var(--primary)]">{{ $toggle['title'] }}</p>
                                        <p class="text-[11px] text-[var(--text-muted)] mt-0.5">{{ $toggle['desc'] }}</p>
                                    </div>
                                    <span class="relative inline-flex items-center shrink-0">
                                        <input type="checkbox" name="{{ $toggle['name'] }}" value="1" @checked($toggle['checked']) onchange="nySettingSave(this)" class="sr-only peer">
                                        <span class="w-10 h-[22px] rounded-full bg-[var(--surface-subtle)] border border-[var(--border-strong)] peer-checked:bg-emerald-500 peer-checked:border-emerald-500 transition-colors duration-150 peer-focus-visible:ring-2 peer-focus-visible:ring-emerald-400/50"></span>
                                        <span class="absolute left-[3px] top-1/2 -translate-y-1/2 w-4 h-4 rounded-full bg-[var(--text-muted)] peer-checked:bg-[#0B0B0D] peer-checked:translate-x-[18px] transition-all duration-150 pointer-events-none shadow-xs"></span>
                                    </span>
                                </label>
                            @endforeach
                        </div>

                        <div class="pt-2 flex justify-end border-t border-[var(--border-color)]">
                            <button type="submit" class="btn-primary text-xs py-2.5 px-5 cursor-pointer">
                                Simpan Preferensi
                            </button>
                        </div>
                    </form>
                </section>

                <!-- Keyboard Shortcut Reference Card -->
                <section class="rounded-2xl bg-[var(--surface)] border border-[var(--border-color)] overflow-hidden">
                    <div class="px-5 py-3.5 border-b border-[var(--border-color)]">
                        <h2 class="text-sm font-bold text-[var(--primary)]">Referensi Pintasan Keyboard</h2>
                    </div>
                    <div class="p-5 grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                        @foreach([
                            ['keys' => '/', 'label' => 'Fokus ke kotak pencarian'],
                            ['keys' => 'J / K', 'label' => 'Pindah antar kartu feed'],
                            ['keys' => 'Enter', 'label' => 'Buka detail karya terpilih'],
                            ['keys' => 'U', 'label' => 'Upvote karya terpilih'],
                            ['keys' => 'B', 'label' => 'Simpan karya ke koleksi'],
                            ['keys' => '?', 'label' => 'Tampilkan dialog pintasan'],
                        ] as $shortcut)
                            <div class="flex items-center justify-between gap-3 px-3.5 py-2.5 rounded-xl bg-[var(--surface-subtle)] border border-[var(--border-color)]">
                                <span class="text-[var(--text-muted)]">{{ $shortcut['label'] }}</span>
                                <kbd class="px-2 py-0.5 rounded-md bg-[var(--surface)] border border-[var(--border-color)] font-mono text-[11px] text-[var(--primary)] shrink-0">{{ $shortcut['keys'] }}</kbd>
                            </div>
                        @endforeach
                    </div>
                </section>
            </div>
        </div>
    </div>

    <!-- Unblock toast: in-place feedback, no reload / no scroll jump -->
    <div class="fixed left-1/2 -translate-x-1/2 bottom-20 lg:bottom-6 z-[45] w-max max-w-[92vw] pointer-events-none"
         role="status"
         aria-live="polite">
        <div x-show="blockToast.show"
             x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-3"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 translate-y-3"
             :class="blockToast.type === 'error'
                 ? 'border-rose-500/40 text-rose-400'
                 : 'border-[var(--border-strong)] text-[var(--primary)]'"
             class="pointer-events-auto flex items-center gap-2 rounded-xl bg-[var(--surface-hover)] backdrop-blur-md border px-4 py-2.5 text-xs font-semibold shadow-[var(--shadow-lift)]">
            <span x-text="blockToast.message"></span>
        </div>
    </div>
</div>
@endsection
