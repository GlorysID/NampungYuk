# Progress Ledger: NampungYuk

Dokumen ini mencatat riwayat investigasi, keputusan arsitektur, overhaul UI/UX, perbaikan keamanan backend, dan status verifikasi pengujian pada proyek NampungYuk.

---

## 1. Status Terkini (Status: Selesai & Stabil)

- **Test Suite**: 136 Feature & Unit Tests Lulus 100% (483 Assertions, 0 Failure).
- **Frontend Build**: Vite v8.3.4 + Tailwind CSS v4.3.3 terkompilasi bersih (`public/build` ready).
- **Code Style**: Laravel Pint (`vendor/bin/pint --dirty`) lulus 100%.
- **Blade Compilation**: `php artisan view:cache` lulus tanpa syntax error.
- **Visual & UX Aesthetic**: Desain mengadopsi estetika **Token Harbor** (Matte Obsidian `#0B0B0D` + Warm Stone `#EAE8E1` + Warm Linen `#F6F5F0` di light mode + Hairline border `#242426`) yang diterapkan konsisten di seluruh platform.

---

## 2. Riwayat Milestone & Pekerjaan

### Milestone 1: Audit Komprehensif (Backend, Security & UI/UX)
- **Backend & Keamanan**:
  - Ditemukan celah IDOR pada `InfiniteFeedController::communityShowFeed` (konten komunitas privat bisa dibaca non-anggota lewat URL fragment).
  - Ditemukan kebocoran broadcast publik: `ProjectPublished` dan `CommentPosted` tetap disiarkan ke publik pada proyek berstatus privat atau bertanda peringatan.
  - Ditemukan WebRTC Spaces rusak total: route binding menggunakan `slug` sedangkan JS memanggil `id` numeric (404), serta pemanggilan channel Reverb sebelum izin keikutsertaan terbit (403).
  - Ditemukan counter drift pada penghapusan komentar berjenjang (hanya mengurangi 1 komentar meskipun ada balasan anak) dan penghapusan proyek di panel admin (tidak mengurangi hitungan kategori).
  - Ditemukan potensi race condition pada upvoting dan bookmarking karena absennya composite unique constraint `(project_id, user_id)`.
  - Ditemukan duplikasi query pada sidebar Blade (`partials/sidebar.blade.php`) dan duplikasi instance `notificationBell` di header serta mobile bottom bar.
- **Frontend & UI/UX**:
  - Ditemukan fenomena "micro-UI": container shell dibatasi di `1280px` (kolom tengah tertekan ke `680px`), tipografi mayoritas `10px–12px`, dan touch target di bawah standar 44px.
  - Ditemukan "Hostile Guest Wall": tamu diblokir modal login saat hanya ingin membaca detail proyek publik, profil developer, atau kategori.
  - Halaman obrolan di desktop terjepit di kolom sempit sehingga ruang percakapan hanya tersisa ~216px.
  - Ditemukan inkonsistensi voting: ikon Love (`♡`) di kartu feed vs ikon Upvote (`^`) di trending rail.

### Milestone 2: Perbaikan Keamanan Backend & Stabilitas Realtime
- **Arsitektur & Query Memoization**:
  - Implementasi request memoization pada `AppServiceProvider::boot()` via `View::composer(['layouts.app', 'partials.sidebar'])` untuk membagikan `sidebarCategories`, `sidebarCommunities`, dan `pendingReportsCount`.
  - Penambahan cache 5 menit untuk `rail:trending_projects_v2` dan `projects.hot_hashtags` dengan cache invalidation otomatis saat lifecycle proyek berubah.
- **Spaces WebRTC & Signaling**:
  - Penambahan route binding resolver pada `SpaceController` untuk mendukung pencarian entitas via slug maupun ID numerik.
  - Penambahan validasi partisipan aktif pada `SpaceController::signal()` untuk mencegah flooding sinyal dari akun luar.
  - Perbaikan `resources/js/app.js` (`spaceRoom`): penundaan langganan channel Echo hingga permintaan join berhasil, dan `leave()` membersihkan channel secara tuntas.
- **Privasi & Penjagaan Akses**:
  - Pengecekan status anggota privat pada `InfiniteFeedController` dan `CommunityPostController`.
  - Pengamanan mutasi (vote, bookmark, comment, repost) pada proyek privat agar memicu 403 Forbidden bagi pengguna yang tidak berhak.
  - Cascade counter delete yang akurat pada balasan komentar (`destroyComment`).
  - Penambahan migrasi database untuk composite unique index pada tabel `project_votes` dan `project_bookmarks`.
  - Pembatasan alias login pengembang (`budi`/`admin`) khusus untuk lingkungan `local` dan `testing`.

### Milestone 3: Overhaul Shell, Komponen & Desain Global
- **Skala & Proporsi (Anti-Shrinkage)**:
  - Container shell diperlebar ke `max-w-[1440px]`, sidebar kiri ke `w-[280px] xl:w-[300px]`, kolom tengah feed ke `max-w-[760px]`, dan right rail ke `w-[340px] xl:w-[370px]`.
  - Tipografi dinaikkan: judul proyek ke `21px`, teks bodi ke `15px`, avatar kreator ke `40px`, tombol dan input ke `h-10` hingga `h-12`.
  - Standarisasi font murni menggunakan **Geist** dan **Geist Mono** (menghilangkan Bunny Fonts dan font CDN redundan).
- **Pembersihan Navigasi & Shell**:
  - Menghilangkan duplikasi tombol login/register pada desktop sidebar.
  - Menyatukan navigasi utama sidebar tanpa menduplikasi filter feed beranda.
  - Menghapus pembajakan klik tamu (guest unblocked): tautan publik dapat dibuka langsung.
  - Menyeragamkan seluruh sistem voting ke panah **Upvote (`^`)**.
  - Mengintegrasikan navigasi Spaces ke menu utama dan mobile bottom bar.

### Milestone 4: Implementasi Estetika Token Harbor Platform-Wide
- **Palet Warna Arsitektural**:
  - Dark Mode: Matte Obsidian `#0B0B0D`, Elevated Surface `#121214`, Hairline border `#242426`, Teks Warm Stone `#EAE8E1`, Teks Muted `#8E8D86`.
  - Light Mode: Warm Linen `#F6F5F0`, Elevated Surface `#FFFFFF`, Hairline border `#E2DFD7`, Teks `#0B0B0D`.
- **Suite Autentikasi Baru**:
  - Halaman `/login`, `/register`, `/lupa-password`, `/reset-password`, dan `/email/verifikasi` menggunakan tata letak split 50/50 full-bleed.
  - Panel kiri: kanvas bernuansa editorial dengan mock code window terminal macOS (`nampungyuk.config.ts`), metrik platform (1.2K+ Karya, 40+ Komunitas, 100% Gratis), dan tagline `KARYA · DISKUSI · KOLABORASI`.
  - Panel kanan: form input berukuran penuh `h-12`, segmented tab switcher `[Masuk | Daftar]`, tombol Google OAuth, dan tombol submit solid `#EAE8E1` berkontras tinggi.
  - Modal autentikasi (`<x-auth-modal />`) mengadopsi arsitektur split yang sama dengan auto-open saat terjadi error validasi.
- **Halaman Sekunder**:
  - Penyelarasan penuh pada halaman Detail Proyek, Pembuatan/Pengeditan Karya (wizard 5 langkah), Koleksi Tersimpan, Komunitas, Obrolan (desktop 2-pane full width), Profil Pengguna, Notifikasi, dan Moderasi Admin.

---

## 3. Checklist Verifikasi & Quality Gates

- [x] `npm run build` selesai tanpa error kompilasi Vite/Tailwind.
- [x] `vendor/bin/pint --dirty --format agent` selesai dengan status passed.
- [x] `php artisan view:cache` sukses mengompilasi seluruh file Blade.
- [x] `php artisan test --compact` berhasil menjalankan 136 test dengan 0 kegagalan.
- [x] Celah keamanan IDOR pada feed komunitas privat telah ditutup dan diuji via feature test.
- [x] Alur guest tidak lagi dihadang modal login saat mengakses rute publik.
- [x] Tidak ada duplikasi logo atau duplikasi baris otentikasi pada desktop.
- [x] Layout obrolan dan moderasi admin melebar secara proporsional (`max-w-none`).
- [x] Integritas constraint database `project_votes` dan `project_bookmarks` terpasang.

---

## 4. Backlog Masa Depan (Rekomendasi Lanjutan)

1. **WebRTC Scaling**: Peningkatan arsitektur live audio Spaces dari WebSockets mesh ke Selective Forwarding Unit (SFU) seperti LiveKit bila peserta per ruangan melebihi 8 pembicara bersamaan.
2. **Animasi Terminal**: Penambahan efek ketik interaktif pada mock window `nampungyuk.config.ts` di halaman autentikasi.
3. **Optimasi Video Transcoding Queue**: Pemindahan proses `ffmpeg` synchronous di `MediaOptimizer` ke Laravel Queue background worker untuk mengantisipasi unggahan video berdurasi panjang.
