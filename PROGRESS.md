# Progress Ledger: NampungYuk

Dokumen ini mencatat riwayat investigasi, keputusan arsitektur, overhaul UI/UX, perbaikan keamanan backend, dan status verifikasi pengujian pada proyek NampungYuk.

---

## 1. Status Terkini (Status: Fungsional Stabil, Utang Desain Sistem Terbuka)

- **Test Suite**: 142 Feature & Unit Tests Lulus 100% (506 Assertions, 0 Failure) — diverifikasi ulang 9 November 2026.
- **Frontend Build**: Vite v8.3.4 + Tailwind CSS v4.3.3 terkompilasi bersih (`public/build` ready).
- **Code Style**: Laravel Pint (`vendor/bin/pint --dirty`) lulus 100%.
- **Blade Compilation**: `php artisan view:cache` lulus tanpa syntax error.
- **Visual & UX Aesthetic**: Desain mengadopsi estetika **Token Harbor** (Matte Obsidian `#0B0B0D` + Warm Stone `#EAE8E1` + Warm Linen `#F6F5F0` di light mode + Hairline border `#242426`) yang diterapkan konsisten di seluruh platform.
- **Utang Teknis Aktif**: token warna sudah jadi satu-satunya sumber warna untuk komponen (migrasi Milestone 5 selesai). Sisa 276 literal hex di view adalah "pulau selalu-gelap" yang disengaja (code block, panel video, wizard form) plus pasangan opacity yang beda antar mode.

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

### Milestone 5: Audit & Migrasi Design Token Drift (Selesai)
- **Keputusan Arsitektur**: Tema tetap **double (light + dark)**, tetapi token warna dijadikan **satu-satunya sumber kebenaran**. Toggle `html.dark` + `localStorage.theme` + `prefers-color-scheme` sudah berjalan benar (`resources/js/app.js`, init script di `layouts/app.blade.php`), jadi tidak ada alasan memangkas light mode.
- **Temuan Baseline** (audit 9 November 2026, 48 file Blade / 9.667 baris):
  - **4.843 literal hex** di `resources/views` (4.821 arbitrary-value class `bg-[#0B0B0D]`, dst), tersebar di 44 dari 48 file Blade.
  - **0 pemakaian `var(--token)`** di Blade: token di `app.css` praktis mati untuk komponen.
  - **902 class palette Tailwind**: `emerald` 334, `rose` 328, `amber` 164, `zinc` 34, `purple` 15, `violet` 12, `blue` 12, `stone` 3.
  - **23 nilai hex dipakai tapi tidak terdefinisi sebagai token** (`#141416` 49x, `#0E0E10` 36x, `#63636B` 7x, `#A8A7A0` 4x, dst) — drift permukaan gelap.
  - **77% beban hanya di 6 nilai**: `#EAE8E1` 888, `#0B0B0D` 840, `#8E8D86` 554, `#6E6D67` 545, `#242426` 463, `#E2DFD7` 432.
- **Akar Masalah**: `@theme` hanya mendaftarkan ramp `zinc` + `emerald` (sisa estetika lama), sedangkan token semantik tidak pernah dipakai komponen — sehingga setiap komponen menyalin hex dua kali (light + `dark:`).
- **Eksekusi Migrasi**:
  - Menambah **13 token** di `:root` dan `.dark`: `--surface-subtle`, `--surface-sunken`, `--surface-input`, `--surface-deep`, `--surface-chip-hover`, `--surface-muted-flat`, `--surface-invert`, `--action-invert`, `--border-soft`, `--text-soft`, `--text-dim`, `--text-strong`, `--text-placeholder`. Nilainya diambil dari pasangan hex yang benar-benar dipakai, jadi tidak ada pergeseran warna.
  - **Codemod** 2.348 pasangan `*-[#light] dark:*-[#dark]` menjadi satu utility `*-[var(--token)]` (4.696 class member dihapus). Pasangan dengan opacity sama ikut dikollaps (`/60`, `/40`, `/50`); pasangan dengan opacity berbeda sengaja dilewati.
  - Membersihkan `app.css`: seluruh aturan komponen (`.hl-new-badge`, `.hl-badge-dot`, `.project-nav-card:hover`, `.btn-primary`, `.btn-vercel-primary`) kini memakai `var(--token)`, dan blok `.dark` yang jadi redundan dihapus.
  - Inline `<style>` di `layouts/app.blade.php` dan ternary theme-toggle kini memakai token juga.
- **Hasil Terukur**:
  - Literal hex di view: **4.843 → 276 (-94,3%)**; hex di aturan komponen `app.css`: **0**.
  - Bundle CSS produksi: **133,68 kB → 105,90 kB (-20,8%)**, gzip 21,49 kB → 17,87 kB, karena ribuan utility `dark:` duplikat hilang.
  - Verifikasi runtime (Chrome, light + dark) di beranda, `/login`, dan detail proyek: `--bg-app`, `--surface`, `--border-color`, `--text-muted`, `--primary`, `--primary-text` semua flip sesuai token; **0 variabel tak terdefinisi**; **0 teks kontras rendah**.
- **Sisa yang Disengaja (276 hex)**:
  - "Pulau selalu-gelap" yang memang tidak boleh flip: code block `bg-[#0B0B0D]`, panel video Spaces, editor wizard `projects/create` & `projects/edit`, thumbnail preview.
  - Pasangan dengan opacity berbeda antar mode (mis. `divide-[#E2DFD7]/60 dark:divide-[#242426]`) dan satu `!important` (`dark:ring-[#121214]!`).
  - Palette `emerald`/`rose`/`amber` **tidak** diubah: itu skala status lengkap (`/10`, `/15`, `/30`, `-600`, `-700`), bukan satu warna; memetakannya ke token tunggal akan mengubah hue (mis. rose-500 `#F43F5E` vs `--danger` `#EF4444`).

### Milestone 6: Percobaan Normalisasi Radius (Dibatalkan) & Pelajaran Verifikasi
- **Temuan Awal**:
  - Blok `:root` di `app.css` meng-override `--radius-xl: 1rem` (16px), sehingga `rounded-xl` menghasilkan 16px — sama persis dengan `rounded-2xl`. Efeknya, "mengecilkan" radius dengan mengganti `rounded-2xl` → `rounded-xl` **tidak mengubah apa pun** (terverifikasi: computed `border-radius` kartu feed tetap `16px`).
  - `.project-nav-card` di `app.css` meng-hardcode `border-radius: var(--radius-xl)` dan `overflow: hidden`.
- **Yang Dicoba**: menurunkan radius kartu permukaan platform-wide ke 12px, menyamakan semua panel/rail/kartu, dan mengubah media feed jadi full-bleed (`-mx-5 sm:-mx-6 border-y`) tanpa radius sudut.
- **Hasil**: **ditolak user**. Tampilan dinilai berantakan/tidak teratur dan kehilangan rasa "utuh"; kartu 16px dengan gambar inset ber-border tipis terasa lebih kohesif (rujukan: timeline X/Twitter — gambar inset 16px, bukan strip melebar ke tepi).
- **Keputusan Final**: seluruh perubahan radius di-rollback. Token radius di 48 file Blade diverifikasi **identik dengan HEAD (621 token)**, `.project-nav-card` kembali memakai `border-radius: var(--radius-xl)` + `overflow: hidden`, dan container media kembali inset `rounded-xl` ber-border. Yang dipertahankan hanya migrasi token warna (Milestone 5).
- **Pelajaran (wajib diingat)**:
  - Skala radius proyek ini **sengaja** berbeda dari default Tailwind (`:root`: sm 6px, md 8px, lg 12px, xl 16px). Jangan "merapikan" ke default Tailwind tanpa persetujuan desain — sudah dicoba dan ditolak.
  - Verifikasi radius harus lewat px hasil komputasi (`getComputedStyle(el).borderRadius`) atau token efektif di CSS hasil build, bukan nama class. Hati-hati: grep CSS hasil build bisa menangkap definisi Tailwind lebih dulu, padahal override proyek yang menang di cascade.
- **Keputusan Radius Final** (pertahankan yang sudah ada, jangan diubah tanpa alasan desain):
  | Kelas | Nilai efektif | Pakai untuk |
  | :--- | :--- | :--- |
  | `rounded-md` | 8px | Thumbnail rail list, badge kecil, tombol kecil |
  | `rounded-lg` | 12px | Chip, baris list, elemen dalam kartu |
  | `rounded-xl` | 16px | Input/pencarian, container media inset di kartu feed |
  | `rounded-2xl` | 16px | Kartu permukaan (feed & rail) dan modal/dialog/sheet |
  | `rounded-full` | pill | Avatar, status pill |
  - Catatan penting: `rounded-xl` dan `rounded-2xl` **sama-sama 16px** di proyek ini karena override `:root`. Itu kondisi yang diterima apa adanya; jangan "diperbaiki" tanpa persetujuan.
  - Kartu feed (`components/project-card.blade.php` baris 10) dan container gallery-nya (baris 204) sama-sama radius 16px; gambar tetap inset (bukan full-bleed), hanya border hairline pada pembungkus media yang kemudian dihapus di Milestone 7.
- **Catatan**: `@source '../../storage/framework/views/*.php'` di `app.css` ikut memindai view terkompilasi, jadi ukuran bundle CSS bisa sedikit berbeda tergantung isi cache Blade saat build. Bandingkan ukuran hanya setelah `artisan view:clear`.

---

### Milestone 7: Polish Kartu Feed & Rail Kanan (Iteratif, Sesuai Arahan User)
- **Keluhan user**: kartu postingan + dua kartu rail kanan ("Trending Hari Ini", "Sedang Hangat Dibicarakan") dinilai "kurang" — terasa berantakan/tidak teratur dibanding timeline X/Twitter yang "simple tapi terasa utuh dan hidup".
- **Yang dikerjakan (semua disetujui bertahap, radius 16px TIDAK diubah)**:
  - **Rail jadi satu daftar utuh**: kartu `rounded-2xl overflow-hidden` + header `border-b` + list `divide-y`, hover menyelimuti baris penuh (`px-5 py-3`), footer "Lihat semua trending →". Blok hashtag diubah dari grid 2 kolom menjadi list vertikal (`Topik Populer · #N` + tag bold + jumlah karya).
  - **Baris aksi kartu feed**: `pt-3 border-t`, tombol seragam `h-9`, hover berwarna (emerald untuk upvote, sky untuk komentar).
  - **Hapus border elemen dalam** (permintaan eksplisit user): border 1px dihapus dari thumbnail, chip skor, pill/badge, dan pembungkus media; `shadow-xs` dihapus dari kartu rail. Prinsipnya: andalkan whitespace + garis pemisah (`divide-y`/`border-b`/`border-t`), bukan kotak-kotak 1px. `components/tech-pill.blade.php` ikut jadi borderless (berlaku global, disengaja demi konsistensi).
  - **Chip skor trending** jadi teks polos (`text-xs font-mono font-semibold text-[var(--text-muted)]` + panah emerald), bukan kotak ber-border.
- **Perilaku rail kanan** (`layouts/app.blade.php:450`): `sticky top-0 max-h-screen overflow-y-auto no-scrollbar`. Ini hasil dua iterasi yang ditolak: (1) rail dengan `overflow-y-auto` + scrollbar terlihat → user menolak scrollbar kedua, minta sisa yang paling kanan saja; (2) rail tanpa sticky → user menolak karena "ikut ilang" saat di-scroll. Kombinasi final: tetap menempel saat halaman di-scroll, scrollbar dalam disembunyikan.
- **Sidebar kiri**: scroll internal dihapus (`partials/sidebar.blade.php` baris 11 → `flex-1 space-y-4 pr-1 my-1`) atas permintaan user ("solid tidak bisa di scroll"). Karena tidak ada lagi pemakai, aturan CSS `.sidebar-scroll` (4 rule) dihapus dari `app.css`. Isi yang melebihi tinggi layar akan terpotong oleh `overflow-hidden` pembungkus, bukan ter-scroll.
- **Verifikasi**: `npm run build` ok, `view:cache`/`view:clear` ok, 142 test lulus (506 assertions), encoding bersih. Diverifikasi di browser: rail `position: sticky` dengan `top` tetap `0` setelah halaman di-scroll 1.300px, `scrollbar-width: none`.
- **Catatan/risiko**: pada viewport 828px, isi rail ±259px lebih tinggi dari layar sehingga baris bawah hanya bisa dicapai dengan wheel di atas rail (scrollbar sengaja tak terlihat). Kalau user keberatan, opsi berikutnya adalah memangkas jumlah item rail (mis. Topik 5 baris) atau memakai scrollbar tipis ala hover. Untuk jangka panjang lihat catatan Milestone 7 ini sebelum mengubah perilaku scroll shell.

---

### Milestone 8: Dark Mode Only (Light Mode Dihapus)
- **Permintaan user**: "BUAT ONLY DARK MODE AJA BRO TIDAK USAH ADA LIGHT MODE".
- **Pendekatan**: `<html class="dark">` di-hardcode. Tailwind memakai `@custom-variant dark (&:where(.dark, .dark *))` (`app.css:4`), jadi cukup kelas itu selalu ada tanpa merombak token. Konsekuensi: palet light di `:root` masih ada di CSS tapi **tidak terjangkau** (dead code) — sengaja dibiarkan agar diff kecil dan bebas risiko.
- **Yang dihapus**:
  - Script anti-FOUC `localStorage.getItem('theme')` + `prefers-color-scheme` di `layouts/app.blade.php` dan 5 halaman auth (`login`, `register`, `forgot-password`, `reset-password`, `verify-email`).
  - Tombol toggle tema: menu "Tema Gelap / Mode Terang" di dropdown akun dan tombol ikon matahari/bulan untuk guest (`layouts/app.blade.php`).
  - `Alpine.store('theme')` di `resources/js/app.js` (tanpa pemakai setelah toggle hilang).
  - Inline `<style>`: `color-scheme: light` → `color-scheme: dark`, rule `html.dark` dihapus.
- **Verifikasi**: HTML hasil render memuat `<html ... class="dark">` pada `/`, `/login`, `/register`; sisa 0 referensi `$store.theme`, `savedTheme`, dan `localStorage.theme`; `npm run build` ok; `view:cache`/`view:clear` ok; 142 test lulus (506 assertions).
- **Catatan untuk agent berikut**: jangan menambahkan toggle tema lagi. Utility `dark:` (287 kemunculan di view) tetap dipakai dan kini selalu aktif; jangan dihapus.

---

### Milestone 9: Audit Komprehensif Ulang, Penyesuaian Navigasi Sidebar & Guest Gating (9 Nov 2026)
- **Konteks & Permintaan User**:
  1. Hapus "Direktori Kreator" dan badge "v1.0" dari sidebar.
  2. Kunci tombol penting di sidebar untuk tamu (`@guest`) agar memicu modal login (`openAuthModal('login')`). Pilihan user: Komunitas, Spaces, dan Kategori Kodingan.
  3. Ganti menu "Prototipe Interaktif" di sidebar dengan "Obrolan" (`route('chat.index')`).
  4. Lakukan audit ulang proyek secara komprehensif dan lengkapi file `.md` yang ada maupun yang belum ada.
- **Yang dikerjakan**:
  - **Navigasi Sidebar (`partials/sidebar.blade.php`)**:
    - Item "Prototipe Interaktif" diganti dengan "Obrolan", link aktif pada `chat.*`.
    - Duplikasi entri "Obrolan" di blok `@auth` bawah dibersihkan sehingga hanya ada 1 link Obrolan di Menu Utama.
    - "Direktori Kreator" dan badge `v1.0` dihapus dari header brand sidebar.
    - Guest gating dipasang pada item: Komunitas, Spaces, Obrolan, dan Kategori Kodingan (`@guest @click.prevent="window.openAuthModal('login')" @endguest`).
    - Gating selaras dipasang pada navigasi mobile bottom bar (`layouts/app.blade.php`).
    - Pengujian `ProjectShowcaseTest.php` disesuaikan untuk memvalidasi teks 'Obrolan' menggantikan 'Prototipe Interaktif'.
  - **Dokumen Audit Komprehensif (`AUDIT.md`)**:
    - Dibuat berkas baru `AUDIT.md` yang merangkum temuan audit read-only: Keamanan (S1-S7), Performa (P1-P5), Integritas Data (D1-D4), Utang Teknis & Kode Mati (U1-U4), Cakupan Testing, dan Status Desain.
  - **Pembaruan Dokumentasi Lainnya**:
    - `README.md`: disinkronkan dengan Laravel 13, dark mode only, deskripsi media GD/FFmpeg, update navigasi sidebar, dan instruksi path PHP Laragon.
    - `PROJECT_CONTEXT.md`: diperbarui dengan skema 28 tabel DB, detail rail sticky no-scrollbar, borderless inner elements, dan sidebar non-scroll.
    - `.ai/rules/`: `index.md`, `layouts.md`, `controllers.md`, `dev-environment.md` disinkronkan.
- **Verifikasi**: `npm run build` ok, `view:cache`/`view:clear` ok, 142 test lulus (506 assertions), encoding bersih.

---

### Milestone 10: Iterasi 4 Sidebar Kiri: Pengelompokan Berlabel & Pengurangan Item Nav (10 Nov 2026)
- **Konteks & Permintaan User**:
  - User merasa deret 14 item nav berurutan di sidebar kiri seperti tumpukan link mentah. Arah yang dipilih: "Group berlabel + kurangi item".
  - Memecah daftar nav menjadi 3 grup berlabel kecil tipis uppercase (`text-[11px] font-bold uppercase tracking-wider text-[var(--text-faint)]`):
    1. **Feed**: Semua Karya, Trending, Terbaru, Mengikuti (auth)
    2. **Jelajahi**: Cari Karya, Direktori Kreator, Komunitas, Spaces
    3. **Aktivitas**: Obrolan, Notifikasi (auth, badge count), Permintaan Pengguna (auth), Koleksi Tersimpan (auth)
  - Mengurangi item: Menghapus entri **Populer** (`tab=populer`) dan **Prototipe Interaktif** (`tab=prototype`) dari link sidebar nav.
  - Mempertahankan deteksi active-state yang bersih pada `$isExploreFeed` agar "Semua Karya" tidak aktif saat ada `tab` di request URL.
  - Menjaga item tetap rapat dan seragam (`flex flex-col gap-0.5`, `min-h-[44px]`, `py-2.5 px-3.5`, `rounded-full`).
  - Menjaga section admin (Moderasi Admin), kategori komunitas, tombol CTA Pamer Kodingan / Masuk Daftar, dan user profile pill tetap utuh.
- **Verifikasi**: `artisan view:clear`, `artisan view:cache`, Pint agent, dan 142 Feature & Unit Tests lulus 100% (512 assertions, 0 failure).

### Milestone 11: Perbaikan Glitch FOUC Navigasi "Shell Menyempit ke Tengah" (10 Nov 2026)
- **Diagnosis (dari `laravel-boost` browser-logs, bukan tebakan)**: `public/hot` ada → Vite dev server aktif. CSS di-serve `Cache-Control: no-cache`, ~154 KB, dari origin berbeda (`http://[::1]:5173`), dan di-fetch ulang tiap pindah halaman. Log mencatat `[vite] server connection lost. Polling for restart...` dua kali. Saat request CSS gagal/nyangkut, Chrome melukis dokumen tanpa CSS → 3 kolom shell runtuh lalu menggeser ke tengah = kesan "menyempit ke tengah sekilas".
- **Perbaikan**:
  1. Menghapus `@import url('https://fonts.googleapis.com/...')` duplikat dari `resources/css/app.css` (font sudah di-`<link>` di `layouts/app.blade.php` dan 5 halaman auth; diverifikasi tiap view yang memuat app.css punya link font).
  2. Menambah critical shell geometry inline di `<style>` setelah tag vite di `layouts/app.blade.php`, dengan kelas baru `.ny-shell`/`.ny-aside`/`.ny-main`/`.ny-rail`. Karena berada setelah tag vite dengan spesifisitas sama, aturan ini menang saat app.css belum mendarat, sehingga shell tidak pernah render runtuh. Tidak menyetel `main max-width` karena halaman meng-override `@section('main_width')`.
  3. **Iterasi lanjutan**: `.ny-aside`/`.ny-rail` diset `display: none` sebagai default (mobile-first), baru dibuka lewat media query `min-width:1024px`/`1280px`. Sebelumnya hanya ditambah, sehingga sebelum app.css mendarat rail kanan ikut render in-flow (default `div` = block) → tiga kolom terjepit lalu melebar = sisa glitch "menyempit ke tengah".
- **Guest gating sidebar (dikonfirmasi user)**: memulihkan proteksi klik tamu pada link **Komunitas** dan **Spaces** (`href="{{ auth()->check() ? route(...) : '#' }}"` + `@guest @click.prevent="window.openAuthModal('login')" @endguest`) sesuai `.ai/rules/layouts.md`. Sebelumnya gate ini terhapus tanpa persetujuan user.
- **Jebakan Blade**: `@vite`/`@section` yang ditulis sebagai teks di dalam komentar CSS tetap dikompilasi Blade → `Vite::__invoke()` dipanggil tanpa argumen (`ArgumentCountError`). Komentar akhirnya ditulis tanpa token `@`.
- **Catatan `shortcut:`**: nilai px di critical CSS menduplikasi kelas Tailwind pada elemen shell di file yang sama; ubah keduanya bersama bila kolom berubah.
- **Akar masalah sebenarnya (diukur)**: dev server nyuplai `app.css` dengan header `no-cache`, ~160 KB, **~120–900 ms** per request, dan browser fetch ulang tiap pindah halaman → tiap navigasi punya jendela tanpa style 0,1–0,9 detik. Critical CSS hanya menyelamatkan geometri, bukan seluruh styling, jadi flash masih terlihat.
- **Resolusi**: dev server tetap dipakai (HMR). Aset build sempat dicoba (`npm run build`, `public/hot` dihapus) untuk mengisolasi penyebab — dengan build, flash hilang total (stylesheet same-origin + hashed + ke-cache), jadi penyebab terbukti. Karena user butuh HMR, `public/hot` dipulihkan dan app kembali ke dev server; sifat `no-cache` dev server tetap bikin pop singkat tiap navigasi, tapi keruntuhan kolom ("menyempit ke tengah") sudah dicegah oleh critical CSS.
- **Verifikasi**: `artisan view:clear`/`view:cache` ok, HTML hasil render memuat blok critical (default-hidden rail/aside) + tanpa `ArgumentCountError`, guest Komunitas/Spaces `href="#"` dengan `click.prevent openAuthModal`, 142 test lulus (512 assertions).

---

## 3. Checklist Verifikasi & Quality Gates

- [x] `npm run build` selesai tanpa error kompilasi Vite/Tailwind.
- [x] `vendor/bin/pint --dirty --format agent` selesai dengan status passed.
- [x] `php artisan view:cache` sukses mengompilasi seluruh file Blade.
- [x] `php artisan test --compact` berhasil menjalankan 142 test dengan 0 kegagalan (506 assertions, 9 Nov 2026).
- [x] Audit design token drift selesai: baseline metrik hardcoded hex terdokumentasi.
- [x] Migrasi token warna selesai: 4.843 → ~106 literal hex di view reguler, 0 hex di aturan komponen `app.css`, bundle CSS -20,7%.
- [x] Verifikasi runtime light/dark (beranda, login, detail proyek): semua token flip benar, 0 variabel tak terdefinisi, 0 teks kontras rendah.
- [x] Percobaan normalisasi radius & full-bleed media di-rollback penuh; token radius di 48 file Blade identik dengan HEAD (621 token), `.project-nav-card` kembali seperti semula (Milestone 6).
- [x] Verifikasi radius lewat px komputasi: `rounded-xl`/`rounded-2xl` = 16px (override `:root`), bukan asumsi default Tailwind.
- [x] Polish kartu feed & rail kanan: rail jadi daftar `divide-y` utuh, border elemen dalam (thumbnail/chip/pill/media) dihapus, `shadow-xs` kartu rail dihapus (Milestone 7).
- [x] Rail kanan `sticky` tanpa scrollbar kedua (`no-scrollbar`); sidebar kiri solid tanpa scroll internal; aturan `.sidebar-scroll` yang tak terpakai dihapus dari `app.css` (Milestone 7).
- [x] Dark mode only: `<html class="dark">` di-hardcode, script `localStorage.theme`, `Alpine.store('theme')`, dan kedua tombol toggle tema dihapus (Milestone 8).
- [x] Navigasi sidebar dimutakhirkan: "Prototipe Interaktif" diganti "Obrolan", "Direktori Kreator" & badge "v1.0" dihapus (Milestone 9).
- [x] Guest gating sidebar & mobile: Komunitas, Spaces, Obrolan, Kategori memicu modal login saat diklik tamu (Milestone 9).
- [x] Berkas `AUDIT.md` komprehensif dibuat dan seluruh file dokumentasi (`README.md`, `PROJECT_CONTEXT.md`, `.ai/rules/*`) disinkronkan (Milestone 9).
- [x] Celah keamanan IDOR pada feed komunitas privat telah ditutup dan diuji via feature test.
- [x] Alur guest tidak lagi dihadang modal login saat mengakses rute publik.
- [x] Tidak ada duplikasi logo atau duplikasi baris otentikasi pada desktop.
- [x] Layout obrolan dan moderasi admin melebar secara proporsional (`max-w-none`).
- [x] Integritas constraint database `project_votes` dan `project_bookmarks` terpasang.
- [x] Alur guest tidak lagi dihadang modal login saat mengakses rute publik.
- [x] Tidak ada duplikasi logo atau duplikasi baris otentikasi pada desktop.
- [x] Layout obrolan dan moderasi admin melebar secara proporsional (`max-w-none`).
- [x] Integritas constraint database `project_votes` dan `project_bookmarks` terpasang.
- [x] Sidebar dirapikan: lebar aside 264/280px, padding `px-6`, item `h-10`, grup Feed/Jelajahi/Aktivitas; label "Permintaan Pengguna" dipertahankan karena di-assert test.
- [x] Bug shell bergeser ke tengah diperbaiki: `.ny-shell` jadi `justify-content: flex-start` sehingga kolom kiri selalu menempel di kiri walau rail kanan di-hide (halaman chat/create/edit) atau belum aktif (di bawah `xl`). Diverifikasi screenshot headless 1024/1200/1279/1280/1530px.
- [x] Sidebar density dinaikkan ala X: item `h-11` teks 15px ikon 20px, label grup 11px, grup `gap-5`, CTA jadi pill full-width `h-12 rounded-full`. Dikoreksi dari usulan awal h-12 (memotong 2 item di 858px) setelah verifikasi screenshot; 12 item muat di 858px, di 800px item terakhir masih perlu scroll.
- [x] Footer editorial (logo + Feed/Pamerkan/Koleksi/Shortcuts/GitHub) dipindah dari kolom tengah ke dasar rail kanan.
- [x] Gap bawah halaman hilang: `body` `lg:pb-8` → `lg:pb-0` (`pb-20` mobile dipertahankan untuk bottom nav). CDP 1530x858: `scrollHeight` 9112 → 9080 = tinggi shell.
- [x] Komentar dirapatkan jadi thread rata: chrome kartu (`rounded-xl`/`border`/`shadow`/`bg`) dibuang dari `components/comment-card.blade.php`, layout avatar 32px + kolom isi, `py-3`, `space-y-1`, pemisah `divide-y` di container (project show) dan tab komentar profil. Terukur 114px per komentar 2 baris (sebelumnya ~140px).
- [x] "Permintaan Pengguna" di sidebar jadi tombol pembuka modal (bukan lagi navigasi ke `/obrolan#permintaan`). Data `$chatRequests` dibagikan `AppServiceProvider` view composer (2 query: conversation pending + pesan terakhir). Aksi Terima/Tolak via `fetch` JSON ke `chat.accept`/`chat.decline` tanpa reload, badge jumlah di item sidebar. Diverifikasi screenshot 1440x900 (1 baris + tombol Tolak/Terima) dan test `test_sidebar_request_modal_lists_pending_requests_for_recipient_only`.
- [x] Item sidebar "Mengikuti" dihapus (sudah ada di tab feed header). Total item login jadi 11, muat sempurna tanpa terpotong di viewport 800px dan 858px (diverifikasi screenshot).
- [x] Perilaku notifikasi standar sosmed disempurnakan: buka halaman `/notifikasi` langsung mereset badge angka unread di lonceng dan sidebar jadi 0 (`$user->unreadNotifications->markAsRead()`), sementara riwayat notifikasi tetap tersimpan utuh dan tidak hilang saat di-refresh. Diverifikasi feature test `tests/Feature/NotificationTest.php`.
- [x] Polish visual halaman Notifikasi (`notifications/index.blade.php`) & dropdown lonceng (`layouts/app.blade.php`): header dilengkapi filter kategori (`Semua`, `Interaksi`, `Karya & Tren`, `Komunitas`), avatar `rounded-full` via `<x-user-avatar>` + mini-badge tipe berwarna semantik per jenis aktivitas, cuplikan isi komentar/post (`comment_excerpt`/`post_excerpt`), thumbnail karya borderless, status `Baru` untuk item yang baru masuk saat dibuka, dan empty state seragam. Dropdown lonceng di `layouts/app.blade.php` ikut disesuaikan memakai token `divide-[var(--border-color)]`, `rounded-2xl`, serta avatar `rounded-full` + fallback inisial. Diverifikasi `artisan view:cache` dan 48 test (`NotificationTest`, `ChatTest`, `ProjectShowcaseTest`) passed.
- [x] Form "Pamer Kodingan" (`projects/create.blade.php`) dirombak dari wizard 5 langkah (`currentStep 1-5`) menjadi "Model Cepat Ala Twitter": composer card ringkas (Judul, Tagline, Kategori, Tipe, Status, Tech Stack chips instan dengan pill quick-add, thumbnail attachment, dan disclosure opsional untuk catatan teknis). Diverifikasi lewat tangkapan layar live CDP dan seluruh 146 unit & feature test passed.
- [x] Flash notification status & login/logout ("Anda telah keluar.", "Selamat datang kembali", dll) diubah dari card statis permanen di tengah layout menjadi **Floating Top Push Notification** (ala notifikasi HP / Dynamic Island): muncul slide-down dari atas ke bawah (`fixed top-4 left-1/2 -translate-x-1/2 z-[100]`), otomatis hilang dalam 3.2 detik atau saat ditutup manual. Card statis di atas feed dihapus. Diverifikasi via live screenshot browser dan 146 tests passed.
- [x] Blok tamu sidebar: kartu promo "Pamer karya, dapat feedback" + 2 manfaat + tombol pill "Daftar gratis"/"Masuk" (sebelumnya grid 2 tombol polos dengan kekosongan ~400px). Tinggi blok dibatasi 299px supaya 8 item nav tamu tetap utuh di 858px (`navOverflows:false` di 858 dan 1080). Versi pertama 454px sempat memotong item "Obrolan".
- [x] Sinkronisasi badge "Permintaan Pengguna" secara live via Alpine (`chatRequestsCount`) saat menerima/menolak permintaan di modal, plus fallback footer editorial di halaman `hide_right_rail` yang tidak menyetel `hide_footer` (`admin/reports`, `projects/edit`, `spaces/show`).
- [x] Perbaikan temuan audit `AUDIT.md` (`S1`, `S2`, `S4`, `D1`, `P1`): menambahkan `visibleTo()` di `ProjectController@latest`, `relatedProjects` (`@show`), dan `bookmarks` (`/koleksi`); menyimpan `github_url` & `website_url` di `ProfileController@update`; serta menghilangkan N+1 query di `components/follow-button.blade.php` dengan request-level memoization `auth_following_ids` dan menghapus query `followers()->count()` yang tak terpakai.
- [x] Migrasi tombol "Lanjutkan dengan Google" ke **Firebase Auth (Google Sign-In)** untuk project `nampungyuk-2d910`: pasang SDK `firebase` + modul `resources/js/firebase.js` (popup + redirect fallback), komponen shared `<x-google-auth-button />` (`login`, `register`, `auth-modal`), verifikasi RS256 JWK backend di `App\Services\FirebaseIdTokenVerifier`, serta endpoint `POST /auth/firebase/google` (`auth.firebase.google`). Diverifikasi `npm run build`, Laravel Pint (`passed`), dan seluruh `155` test (`586` assertions) passed.
- [x] Sinkronisasi avatar Google otomatis di `GoogleAuthController::findOrCreateGoogleUser` (menyinkronkan PP Google terbaru selama belum ada custom avatar `/storage/avatars/...`) + `referrerpolicy="no-referrer"` pada tag avatar, serta penambahan kotak pencarian postingan langsung di halaman profil (`ProfileController@show`, `resources/views/users/show.blade.php`) untuk menyaring tab Karya (termasuk pinned & proyek milik sendiri), Diskusi, Repost, dan Disukai dengan tetap mematuhi `visibleTo()`. Diverifikasi Laravel Pint (`passed`) dan seluruh `159` test (`604` assertions) passed.
- [x] Redesign modal Edit Profil (`resources/views/users/show.blade.php`) bergaya X (banner full-bleed & avatar overlap dengan tombol kamera, header close/judul/tombol Simpan pill, field outlined ala X, counter bio, dan tautan profil tanpa tab password), bar profil menampilkan jumlah postingan halus tanpa tombol notif, serta input pencarian profil bersih dari kotak fokus ganda (`composer-seamless`).
- [x] Halaman Pengaturan sosial media komprehensif (`SettingsController`, `settings/index.blade.php`, migration `preferences` & `user_blocks`) dengan 5 kategori (Akun Anda + ekspor JSON + hapus akun permanen, Keamanan & manajemen sesi aktif, Privasi DM/repost/direktori/tanda dibaca & blokir akun, Preferensi Notifikasi granular, serta Preferensi feed, autoplay & pintasan keyboard), memakai pill toggle switches (`sr-only peer`), serta penegakan penuh di `ChatController`, `FollowController`, `ProjectController`, `CommunityPostController`, `SearchController`, `projects/show.blade.php`, dan `layouts/app.blade.php`.
- [x] Rombak style card postingan (`components/project-card.blade.php`, `project-meta.blade.php`, `project-actions.blade.php`, `tech-pill.blade.php`, `follow-button.blade.php`) menjadi editorial showcase card full-width yang optimal: metadata kreator 2 baris + tombol `Ikuti` outline halus (`variant="subtle"`), judul dan ringkasan fokus, media 16:9 full-width dengan hover-zoom halus, strip tech stack (`compact` maks 4 item) di kiri terpisah dari tautan `Demo`/`Interaktif`/`GitHub` di kanan, serta footer aksi dengan tombol upvote (`^`) pill berbingkai yang jelas dan dukungan kepadatan feed `compact` via `[data-feed-density="compact"]`.
- [x] Penyeragaman desain rail kanan (`projects/index.blade.php`, `layouts/app.blade.php`): menyamakan surface/border & header ringkas di seluruh kartu (`Untuk Diikuti`, `Trending Hari Ini`, `Topik Hangat`, `Komunitas Populer`), memakai tombol `Ikuti` `variant="subtle"`, menghilangkan teks repetitif `Trending · #N`, memberi 2 baris pada judul trending (`line-clamp-2`) dengan index monospace `01..05` dan badge upvote di kanan, serta merapikan `Topik Hangat` menjadi baris 1-baris dengan jumlah karya tabular.
- [x] Pemolesan sidebar kiri (`partials/sidebar.blade.php`, `AppServiceProvider.php`, `SpaceController.php`): padding horizontal lebih lapang (`px-4`), hairline pemisah header logo & antar grup nav, label grup Geist Mono halus, item nav `h-11` bergaya Token Harbor (`rounded-xl bg-[var(--surface)] border-[var(--border-color)]` + dot emerald kanan tanpa garis vertikal menempel di sudut), widget **Live Spaces Radar** real-time (dengan cache `sidebar:live_spaces_v1` yang otomatis di-invalidate saat Space dimulai/diikuti/ditinggalkan/diakhiri) di atas CTA `Pamer Kodingan`, kartu profil bawah berbingkai (`rounded-xl border`), dan menu dropdown profil yang serasi.

## 3b. Milestone 12: Audit Gap Komprehensif & Remediasi Keamanan (15 Nov 2026)

Audit 3 jalur (fitur, keamanan, ops/UX) atas 99 route. Temuan yang dibuktikan langsung:

- **XSS skema URL (tinggi)**: `javascript://x%0Aalert(1)` lolos rule `url` Laravel; dirender sebagai `href` di `users/show.blade.php:310` dan `projects/show.blade.php:241,251,261`. Sumber: `ProfileUpdateRequest.php:41-47`, `ProjectController.php:340,507`.
- **Register tanpa throttle**: `routes/web.php:156` + email verifikasi langsung → email bombing.
- **Blokir tidak memutus chat**: `ChatController.php:191` memuat pesan sebelum cek blok; `older()` (`:399-447`) tanpa cek sama sekali.
- **`parent_id` komentar lintas thread**: `ProjectController.php:781`, `CommunityPostController.php:139-147` hanya `exists:...id`.
- **Tamu diblok di filter kategori/tech**: `ProjectController.php:44-46` (melanggar aturan guest flow).
- **Fitur setengah jadi**: chat Spaces hanya array Alpine (`spaces/show.blade.php:196`, `app.js:281-315` tanpa cabang `chat`); report `community_post` dihitung di badge tapi tak pernah di-query (`AdminModerationController.php:29` vs `:34-44`); role speaker/listener hanya di schema.
- **Ops**: tidak ada `resources/views/errors/*`; queue worker + Reverb tidak terdokumentasi di README; suite SQLite-only; path `/storage/` hardcoded.

Rencana remediasi:
- [x] Wave 1 (selesai): rule `App\Rules\SafeUrl` (hanya http/https, tolak control char) dipakai di `ProfileUpdateRequest` (github/website/links) dan `ProjectController::linkRules()` (demo/github/prototype/thumbnail/video url) — `javascript://x%0Aalert(1)` sekarang ditolak. `throttle` ditambah di `register.store` (5/menit) + 8 endpoint tulis (`chat.send` & `projects.vote` 30/menit, sisanya 10/menit); `spaces.join`/`signal` sengaja tanpa throttle karena ICE candidate. Blokir user kini memutus `show()` (403), `older()` (403 JSON), dan inbox `inboxData()`. `parent_id` komentar di-scope ke `project_id`/`community_post_id` pemiliknya. Guard tamu di filter kategori/tech dihapus (sesuai aturan guest flow), dengan test yang memastikan proyek privat/flagged/warning tetap tersembunyi. Verifikasi: Laravel Pint `passed`, `208` test (`831` assertions) passed, 0 gagal.
- [x] Perbaikan UX setelan (selesai): tombol/radio di `/pengaturan` simpan in-place via `window.nySettingSave()` (`resources/js/app.js`) — `fetch` ke `form.action` dengan `FormData` + `X-CSRF-TOKEN`, tanpa reload, sukses tampil toast, gagal rollback `checked` + toast error; simpan diantrikan per form. `updatePrivacy`/`updateNotifications`/`updatePreferences` membalas JSON saat `wantsJson()`. Opsi **Kepadatan Feed dihapus** (tidak pernah punya efek nyata: hanya padding 18px) — radio + blok CSS `[data-feed-density]` + `data-feed-density` di `<body>` + key `display.feed_density` dibuang; tab Tampilan kini hanya autoplay & pintasan keyboard. Form Akun (email/password/hapus) tetap submit biasa karena butuh konfirmasi password. Temuan: `.env` memakai `DB_CONNECTION=sqlite` (`database/database.sqlite`), bukan MySQL; `@source storage/framework/views/*.php` di `app.css` bikin CSS build membengkak oleh view kompilasi basi (setelah `view:clear` 132 kB → 117 kB).
- [x] Feed state restore (selesai): posisi scroll + jumlah halaman infinite scroll disimpan di sessionStorage saat `pagehide`, dibangun ulang oleh `infiniteFeed.restore()` lalu scroll dikembalikan (back/forward, reload, dan bfcache via `pageshow`). Klik link ke halaman yang sedang dibuka (mis. "Semua Karya" saat sudah di `/`) menghapus simpanan → reload mulai dari halaman 1. Verifikasi di Orca browser: 24 kartu/scroll 6834 kembali utuh setelah back, klik "Semua Karya" → 12 kartu/scroll 0. `210` test (`846` assertions) passed, `npm run build` ok.
- [x] Blokir dari kartu karya, in-place (selesai): menu `⋯` di `components/project-actions.blade.php` membuka **modal konfirmasi** (`components/block-user-modal.blade.php`, `window.openBlockUserModal`) — tidak redirect ke Pengaturan. Konfirmasi → `fetch` JSON ke `settings.blocks.store` → modal tutup, snackbar bawah-center "Berhasil diblokir. @username" + tombol "Buka blokir" (undo via `DELETE /pengaturan/blokir/{id}`, auto-hilang 6 detik). Halaman tidak reload. Tombol "Buka Blokir" di `/pengaturan?tab=privasi` juga AJAX (`@submit.prevent` + `unblockUser()`): baris fade-out, toast "Blokir untuk @x telah dibuka.", scroll tidak bergerak, empty state reaktif; tanpa JS form tetap submit normal. Verifikasi Orca: probe `window.__probe` tetap hidup (tidak reload) di kedua alur, snackbar & toast terbaca. Test: `test_project_card_menu_offers_block_only_for_other_users`, `test_block_and_unblock_endpoints_answer_json_for_in_place_actions`; `212` test (`863` assertions) passed, Pint clean, `npm run build` exit 0.
- [ ] Wave 2: chat Spaces (Reverb) atau tandai coming soon, tab report komunitas, halaman error dark, dokumentasi `artisan dev`/`queue:work`/`reverb:start`.
- [ ] Wave 3: daftar follower/following, edit/hapus komunitas + kick member, OG/sitemap, kontras `--text-faint`, `aria-label` tombol ikon.

## 3c. Milestone 13: Keamanan Akun — verifikasi email, password bertahap, OTP (16 Nov 2026)

Alur lama (form email + password inline di `/pengaturan`, reset password lewat link token) diganti alur bertahap berbasis kode 6 digit lewat email.

- **Infrastruktur**: `app/Services/SecurityCodes.php` (kode 6 digit di-hash di cache, TTL 15 menit, maks 5 percobaan, sekali pakai; `send()` balik `null` kalau mailer gagal supaya controller bisa kasih error, bukan 500), `app/Mail/SecurityCodeMail.php` + `resources/views/mail/security-code.blade.php` (inline style, aman untuk email client). Mailer = bawaan Laravel (Symfony Mailer) via SMTP, **tanpa dependency baru**; `.env.example` sudah preset Gmail (`MAIL_MAILER=log` untuk dev, `smtp.gmail.com:587` + app password untuk produksi).
- **Ganti email** (`/pengaturan/email` → `/pengaturan/email/kode`): kode dikirim ke alamat BARU, email lama tetap aktif sampai kode diverifikasi; verifikasi menulis `email` + `email_verified_at` sekaligus. Form email inline di tab Akun dihapus, route `settings.account.update` + `SettingsController@updateAccount` dihapus.
- **Ganti password bertahap** (`/pengaturan/keamanan/password`): langkah 1 konfirmasi password sekarang (`settings.password.verify`) atau kode email kalau lupa, langkah 2 password baru (`settings.password.update`). Guard server: update ditolak kalau sesi belum terverifikasi (`password_change.verified_at`, 15 menit). Akun Google (`password` null) langsung ke form "Buat Password". Form password inline lama + route `profile.password.update` + `ProfileController@updatePassword` dihapus.
- **Lupa password saat login** (`/lupa-password` → `/lupa-password/kode`): kode ke email akun → kode benar = `Auth::login` dan mendarat di feed; `email_verified_at` diisi kalau masih null. `PasswordResetController` + view `auth/reset-password.blade.php` (alur link token) dihapus; nama route `password.request`/`password.email` dipertahankan agar link "Lupa password?" di halaman login tetap jalan.
- **Prompt akun Google**: login Google tanpa password mem-flash `password_prompt`; `layouts/app.blade.php` menampilkan banner non-blocking "Buat Password" / "Nanti aja".
- **Test**: `tests/Feature/EmailChangeTest.php` (7), `tests/Feature/PasswordChangeTest.php` (11); `SettingsTest`/`ProfileTest`/`ProjectShowcaseTest` dialihkan ke alur baru. Full suite **229 test (951 assertion) passed**, Pint clean, `npm run build` exit 0. E2E manual di dev: `POST /lupa-password` → kode dibaca dari `storage/logs/laravel.log` → `POST /lupa-password/kode` → mendarat di `/` dengan sesi login.
- Sisa untuk produksi: isi `MAIL_USERNAME` + `MAIL_PASSWORD` (app password Gmail 16 digit) dan set `MAIL_MAILER=smtp`; selama `log`, email hanya masuk `storage/logs/laravel.log`.
- **SMTP Gmail aktif di dev (16 Nov 2026)**: `MAIL_MAILER=smtp`, `MAIL_HOST=smtp.gmail.com`, `MAIL_PORT=587`, `MAIL_SCHEME=null`, `MAIL_USERNAME` = `MAIL_FROM_ADDRESS`. Jangan set `MAIL_SCHEME=tls` — Symfony Mailer menolaknya (`UnsupportedSchemeException`), STARTTLS di port 587 otomatis. Pakai `MAIL_SCHEME=smtps` kalau pindah ke port 465. Terverifikasi kirim nyata lewat `Mail::raw` dan lewat `SecurityCodes::send()` (mailable `SecurityCodeMail`) — dua-duanya sukses tanpa error di `storage/logs/laravel.log`.

## 3d. Pemadatan halaman detail karya (16 Nov 2026)

- `<article>` di `resources/views/projects/show.blade.php`: **1423px → 1066px (-25%)** pada viewport 1064px (kolom artikel tetap 710px).
- Media `aspect-video max-h-[260px]` + `object-cover` (crop disetujui user, gaya cover), strip thumbnail 80px → 53px, padding hero/section `p-6/8` → `p-4/5`, judul 26px → `text-xl/2xl` (jadi 1 baris), body 14.5px + `max-w-[68ch]`, tombol aksi tetap 40px.
- Proyek multi-media: ~1503px → 1065px. Sisa tinggi didominasi teks deskripsi (202px) + media 260px + padding minimum.

## 3e. Klarifikasi gerbang obrolan (16 Nov 2026)

- Dua lapis DM yang selama ini tercampur jadi satu copy: (1) `privacy.dm_policy` — `following_only` **menolak** (403, tanpa baris `conversations`, tanpa request), (2) gerbang follow `Conversation::gateStatusFor()` — penerima follow pengirim = langsung, kalau tidak = *permintaan pesan*.
- Yang diperbaiki: `chat/show.blade.php` dapat flag baru `$blockedByPolicy` (tetap reaktif Alpine `x-show`, tidak di-`@if` sesuai `.ai/rules/chat.md`) sehingga composer terkunci tidak lagi bohong "Menunggu ... menerima permintaan pesanmu"; tooltip tombol "Pesan" di `users/show.blade.php` kini membedakan "belum mengikutimu (jadi permintaan)" vs "hanya menerima dari akun yang mereka ikuti"; copy opsi di Setelan → Privasi ikut diperjelas; `.ai/rules/controllers.md` dikoreksi dari "mutual follow" (salah) ke satu arah + lapisan kebijakan.
- Verifikasi: `ChatBlockPrivacyTest` +2 test (penolakan + copy, dan thread lama tetap jalan setelah pindah ke `following_only`). Full suite **231 test (962 assertion) passed**, Pint clean, `npm run build` exit 0.

## 3f. Registrasi kembali ke modal + status Google (16 Nov 2026)

- `RegisterController::store` tidak lagi melempar ke halaman login full screen: `redirect()->back(fallback: route('login'))`, jadi pengguna mendarat kembali di halaman tempat modal dibuka (redirect itu ada sejak commit `bb426c7`, bukan regresi).
- `components/auth-modal.blade.php`: modal auto-buka saat `session('unverified_email')` ada, dan di dalamnya muncul banner hijau `session('status')` ("Registrasi berhasil! ...") + tombol "Kirim Ulang Email" (pakai `route('verification.notice')`) — sebelumnya pesan itu hanya terlihat di halaman login full screen.
- Login manual memang sudah "langsung masuk" sejak dulu (`LoginController@store` → `intended(url('/'))`, tanpa halaman perantara). Yang membuat kesan beda hanya registrasi di atas.
- Kartu "Google Sign-In" di `settings/index.blade.php`: badge kini hijau **Terhubung** kalau `google_id` terisi **atau** email sudah terverifikasi (akun manual tersambung otomatis oleh `GoogleAuthController::findOrCreateGoogleUser()` saat masuk dengan Google), plus satu baris penjelas sesuai kondisinya.
- Jejak: `.ai/rules/views.md` + satu aturan baru — `@php(...)` inline merusak blok `@php ... @endphp` di file yang sama (bikin view 500 `Undefined variable`).
- Verifikasi: `RegistrationTest` kini menguji `from(url('/'))` → redirect balik + banner tampil; full suite **231 test (966 assertion) passed**, Pint clean.

## 3g. Detail karya khusus pengguna login (17 Nov 2026)

- `routes/web.php`: `projects.show` (`/project/{slug}`) sekarang `->middleware('auth')`. Tamu yang membukanya tidak dilempar ke halaman login full screen — `bootstrap/app.php` `redirectGuestsTo()` (sudah ada) mengembalikan mereka ke halaman asal/feed yang sama-host (anti-loop untuk /login, /register, dan URL yang sama) sambil `flash('auth_required', true)`; `layouts/app.blade.php` lalu memanggil `window.openAuthModal('login')` di `DOMContentLoaded`.
- `components/project-card.blade.php`: klik di area kartu (media/judul) kini meneruskan ke `.card-detail-link` (`$el.querySelector('.card-detail-link')?.click()`), dan anchor itu punya `@guest @click.prevent="window.openAuthModal('login')"` — jadi tamu dapat modal langsung tanpa request bolak-balik, sementara pengguna login tetap navigasi normal.
- Yang TETAP publik: feed (`/`), profil (`/u/{username}`), kategori, tech-pill, komunitas, dan fragment `projects.comments.index` (dipakai kartu feed, harus tetap terbuka untuk tamu).
- `AGENTS.md` "Aturan Alur Tamu" diperbarui: dulu melarang membajak klik baca tamu ke detail karya, sekarang detail karya memang login-only. Test lama `test_guest_can_view_project_detail` diganti `test_guest_is_sent_back_with_login_modal_when_opening_project_detail` (assert redirect + `auth_required` + opener modal tampil + anchor kartu tamu tetap punya `@click.prevent="window.openAuthModal('login')"`).
- Verifikasi: full suite **232 test (975 assertion) passed**, Pint clean. Catatan environment: `php artisan boost:mcp` mengunci file compiled view di `storage/framework/views` → suite dijalankan dengan `VIEW_COMPILED_PATH` diarahkan ke folder temp (race `rename(): Access is denied` hilang).

## 3h. Remediasi Audit Lanjutan — UI, Admin, Spaces, Performa & Halaman Error (17 Nov 2026)

- **UI Mobile, Modal Laporan, Edit Proyek & Alert Login**:
  - `resources/views/projects/index.blade.php`: memulihkan overlay pencarian mobile (`#nav-search-mobile`) pada header override beranda serta menambahkan badge angka unread di ikon lonceng mobile.
  - `resources/views/components/report-modal.blade.php`: memperbaiki markup tag `</form>` dan tombol submit duplikat, serta menambahkan pilihan alasan `other` (Lainnya).
  - `resources/views/projects/edit.blade.php`: menghapus emoji pada dropdown status, menambahkan kontrol `visibility` (`public`/`private`) serta `project_type`, dan mengikat state Alpine-nya.
  - `resources/views/auth/login.blade.php`: membedakan rendering `session('status')` bernuansa error/batal Google OAuth memakai warna rose (bukan emerald).
- **Moderasi Admin, Pembersihan Storage & Spaces Realtime**:
  - `app/Http/Controllers/AdminModerationController.php` & `resources/views/admin/reports.blade.php`: menampilkan laporan `CommunityPost` di tab `projects` serta memperbaiki atribut komentar (`$reportable->content ?? $reportable->body`).
  - `app/Services/MediaOptimizer.php`: menambahkan `deleteStoredFile()` dan `deleteProjectMedia()`, lalu menerapkannya saat ganti thumbnail/video di `ProjectController::update()`, hapus proyek (`ProjectController::destroy()`, `AdminModerationController::destroyProject()`), hapus post komunitas (`CommunityPostController::destroy()`, `AdminModerationController::destroyCommunityPost()`), dan hapus akun (`AdminModerationController::destroyUser()`).
  - `app/Http/Controllers/SpaceController.php`, `resources/js/app.js`, `resources/views/spaces/show.blade.php`: menyambungkan obrolan Space melalui sinyal Reverb `kind=chat` (beserta validasi peserta aktif) serta menangani sinyal `leave` dengan `ended: true` untuk menutup stream dan meninggalkan room.
- **Diet HTML, Optimasi Query, SafeUrl & Rate Limiting**:
  - `resources/js/app.js` & `resources/views/components/project-card.blade.php`: memindahkan logika interaktif kartu proyek ke `Alpine.data('projectCard')`, memanggil `galleryImages()` sekali per kartu, dan menambahkan `aria-label` pada kontrol lightbox.
  - `app/Models/Project.php`, `ProjectController.php`, `InfiniteFeedController.php`, `resources/views/projects/bookmarks.blade.php`: caching per-request pengecekan `is_admin` di `scopeVisibleTo()`, eager load `repostedFrom`, penghapusan dead query `$recentReviews`, scoping `ProjectBookmark`/`ProjectVote`/`ProjectRepost` ke ID proyek halaman aktif, penghapusan komentar bersarang multi-level di `destroyComment()`, tombol `Jelajahi Feed` tampil di mobile, serta pengiriman `:is-reposted` pada koleksi tersimpan.
  - `app/Http/Controllers/CommunityController.php` & `routes/web.php`: memakai `App\Rules\SafeUrl` untuk field `icon` komunitas dan menambahkan middleware `throttle` pada endpoint tulis/upload/ekspor/verifikasi yang sebelumnya belum dibatasi.
- **Halaman Error Kustom & Test Baru**:
  - Membuat template error Token Harbor dark-mode (`resources/views/errors/minimal.blade.php`, `403`, `404`, `419`, `429`, `500`).
  - Menambah pengujian di `AdminModerationTest`, `BackendSecurityAndSpacesTest`, `CommentThreadIntegrityTest`, dan `GoogleAuthTest`.

## 3i. Polish First Impression Tamu (Guest Experience) (19 Nov 2026)

- **Sidebar Kiri Tamu Lebih Lega** (`resources/views/partials/sidebar.blade.php`): menyembunyikan item `Cari` (sudah tersedia di search bar kanan atas & mobile header) serta grup `Aktivitas > Obrolan` untuk `@guest` (auth-only), sehingga sidebar tamu turun menjadi 6 item nav dan tombol CTA `Daftar gratis` / `Masuk` aman di atas fold pada layar laptop 768px+.
- **Strip Orientasi & Rail Tamu** (`resources/views/projects/index.blade.php`): mempertahankan 2 tab bar atas (`Untuk Anda` | `Mengikuti`) sesuai preferensi user, menambahkan strip orientasi 1-baris dismissible (`sessionStorage`) di atas feed tamu, menyelaraskan warna tombol Guest Discovery Banner ke token `var(--*)`, dan mengarahkan klik `Trending Hari Ini` tamu langsung ke modal login kontekstual.
- **Kejelasan Kartu & Copy Modal Kontekstual** (`resources/views/components/project-card.blade.php`, `project-actions.blade.php`, `auth-modal.blade.php`, `layouts/app.blade.php`): mempertegas ring/hairline border pada wrapper media gambar, meluruskan hirarki link eksternal (`Live Demo` dengan aksen emerald vs `Prototipe` netral), memformat angka penayangan/komentar (`5.139`), serta mendukung `reason` kontekstual (`detail` / `interact`) pada `window.openAuthModal()` agar subtitle modal menjelaskan alasan login dengan jelas.
- **Google Auth dari Tab Daftar** (`app/Http/Controllers/Auth/GoogleAuthController.php`, `auth-modal.blade.php`, `auth/register.blade.php`): membedakan respons & flash message antara akun lama (langsung masuk dengan *"Selamat datang kembali..."* + `is_new_user: false`) dan akun baru (*"Akun berhasil dibuat..."* + `is_new_user: true`), ditambah hint singkat di tab Daftar bahwa akun Google lama otomatis langsung masuk.
- **Anti-Spam Submit & Proporsi Auth Modal** (`auth-modal.blade.php`, `auth/register.blade.php`, `auth/login.blade.php`, `layouts/app.blade.php`): menambahkan double-submit guard (`@submit` + `:disabled` + spinner) pada form daftar/login, mengecilkan proporsi auth modal (`max-w-[840px]`, banner `unverified_email` digabung jadi 1 kotak kompak), dan menyembunyikan toast global duplikat saat `session('unverified_email')` aktif.
- **Penyederhanaan Kebijakan DM** (`app/Models/User.php`, `app/Models/Conversation.php`, `settings/index.blade.php`, `users/show.blade.php`): mengubah 2 mode `privacy.dm_policy` agar tidak ambigu — `everyone` (default) = siapa pun langsung bisa DM tanpa izin (`accepted`), sedangkan `following_only` ("Minta Izin Dulu") = akun yang diikuti langsung masuk, akun lain masuk permintaan pesan (`pending`) bukan ditolak 403. Blokir tetap menolak 403 dan acceptance tetap monotonik.
- **Upgrade UI Obrolan & Badge Unread Sidebar** (`AppServiceProvider.php`, `ChatController.php`, `partials/sidebar.blade.php`, `layouts/app.blade.php`, `resources/js/app.js`, `chat/_list.blade.php`, `chat/index.blade.php`, `chat/show.blade.php`): menambahkan hitungan & badge angka unread pesan masuk di tombol Obrolan (sidebar desktop + mobile bottom bar + live update `.inbox.updated`), menyatukan layout 2-pane ber-divider, merender bagian "Mulai Obrolan Baru" (saran developer) di pane kiri & empty state, menambahkan Conversation Profile Intro Card di puncak thread, latar dot-grid halus, quick-starter chips, avatar mini pesan masuk, bubble asimetris, dan composer pill menyatu.
- **Realtime Chat Instan & Instagram-Style Typing Bubble** (`MessageSent`, `MessageRead`, `InboxUpdated`, `UserTyping`, `ChatController`, `routes/web.php`, `resources/js/app.js`, `resources/js/echo.js`, `resources/css/app.css`, `chat/show.blade.php`): memindahkan event chat ke `ShouldBroadcastNow` + `safeBroadcast()` (dengan `unset($pending)` & circuit-breaker 10s saat Reverb offline), memisahkan timer typing lokal vs remote, mendukung auto-subscribe percakapan baru & typing sebelum pesan pertama via personal channel, menambahkan endpoint `chat.sync` (fallback adaptif saat Reverb offline) & `chat.read` (read receipt live), serta menambahkan bubble mengetik 3-titik bergelombang ala Instagram di dalam kanvas pesan.
- **Obrolan Full-Bleed + Sidebar Ikon Ramping ala X** (`layouts/app.blade.php`, `partials/sidebar.blade.php`, `chat/_list.blade.php`, `chat/index.blade.php`, `chat/show.blade.php`): menambahkan mode `@section('full_bleed', '1')` & `@section('compact_sidebar', '1')` khusus halaman Obrolan sehingga sidebar kiri otomatis mengecil ke icon-only rail `72px` (logo mark, ikon nav ber-tooltip & badge pojok, tombol `+` bulat putih, avatar profil dengan popover ke kanan), tanpa outer box/double header, sejajar 1 garis `65px` dengan pane daftar & ruang chat, serta transisi geser lebar mulus `280ms` (`ny-sb-from-expanded`/`ny-sb-from-compact`) saat berpindah antara halaman Obrolan dan halaman biasa.
- **Optimasi Performa Navigasi & Diet HTML/JS** (`project-lightbox-modal.blade.php`, `project-card.blade.php`, `layouts/app.blade.php`, `resources/js/app.js`, `resources/js/firebase.js`, `AppServiceProvider.php`, `User.php`): memindahkan Lightbox dari per-kartu (12x duplikat) menjadi 1 komponen global tunggal (`HTML GET / auth 704 KB → 480 KB`, `GET / guest 584 KB → 376 KB`), memisahkan Firebase SDK via dynamic `import('./firebase')` (`app.js 255 KB → 156 KB`, `firebase` chunk 100 KB lazy-loaded), membatasi modal global sesuai role (`@guest`/`@auth`/admin), mengoptimalkan `User::allBlockedIds()` menjadi 1 query ringan, dan men-cache `Schema::hasTable()` + query kondisional di `AppServiceProvider` (`AUTH GET /obrolan 784.9 ms → 32.5 ms`, `AUTH GET / 639.4 ms → 144.4 ms`).
- **Optimasi Performa Putaran 2 — Alpine Double-Init, Lazy Echo & OPcache** (`layouts/app.blade.php`, `projects/index.blade.php`, `spaces/show.blade.php`, `resources/js/echo.js`, `resources/js/app.js`, PHP 8.3 `php.ini`):
  - Menghapus atribut `x-init="init()"` ganda pada `notificationBell`, `liveFeed`, dan `spaceRoom` agar `init()` tidak terpanggil dua kali oleh Alpine 3.
  - Membungkus render drawer sidebar mobile di `layouts/app.blade.php` dengan `<template x-if="mobileSidebarOpen">` agar DOM & SVG sidebar tidak dirender ganda secara eager di desktop.
  - Menginisialisasi badge lonceng notifikasi langsung dari `$unreadNotificationsCount` (`initialUnread`) dan menunda fetch `/notifikasi/latest` sampai tombol lonceng dibuka, dilengkapi state loading `"Memuat notifikasi..."`.
  - Menghapus static `import './echo'` di `app.js` dan memindahkannya ke helper `ensureEcho()` (lazy chunk `echo-*.js` 73.49 KB, hanya dimuat oleh `chatRoom`, `inboxLive`, dan `spaceRoom` saat masuk `/obrolan*` atau `/spaces/*`), sehingga halaman Feed/Profil tidak membuka koneksi WebSocket ke `:8080`.
  - Mengaktifkan Zend OPcache di `C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.ini` (`opcache.enable=1`, `opcache.enable_cli=1`, `opcache.memory_consumption=128`, `opcache.interned_strings_buffer=16`, `opcache.max_accelerated_files=20000`, `opcache.validate_timestamps=1`, `opcache.revalidate_freq=0`, `opcache.save_comments=1`; backup di `php.ini.bak`).
  - Hasil build & test: `app.js` turun **156.36 KB → 83.48 KB** (`gzip: 27.36 KB`, `echo` terpisah `73.49 KB`), `artisan view:cache` ok, Pint passed, dan 6 suite fitur terkait (`ProjectShowcaseTest`, `ChatTest`, `ChatRequestTest`, `ChatBlockPrivacyTest`, `NotificationTest`, `BackendSecurityAndSpacesTest`) **96/96 test (397 assertions) passed**.
- **Obrolan `max-w-[1440px]` Konsisten & Perbesaran Skala UI** (`layouts/app.blade.php`, `partials/sidebar.blade.php`, `chat/_list.blade.php`, `chat/index.blade.php`, `chat/show.blade.php`):
  - Mengembalikan container `.ny-shell` pada mode `full_bleed` ke `max-w-[1440px] mx-auto` beserta `lg:border-r` pada `<main>` agar margin kiri–kanan halaman `/obrolan*` sejajar konsisten dengan halaman Feed/lainnya (tidak mentok ke tepi viewport), sambil mempertahankan perbesaran skala UI.
  - Memperbesar pane daftar obrolan (`23rem` / `xl:25.5rem`) dan menaikkan skala seluruh elemen Obrolan dari nuansa micro-UI: judul `Obrolan` `20px`, search bar `h-11` (`14px`), avatar daftar/saran/search ke `40px` (`size="md"`), nama `15px`, `@username` `13px`, empty state `"Ruang Obrolan Developer"` `24px` + ikon `64px` + grid `max-w-2xl`, serta di ruang percakapan (`chat/show`) header `16px` + tombol `h-9`, Profile Intro Card avatar `xl` + judul `21px`, bubble pesan `15px`, dan composer pill `15px` dengan tombol Kirim `h-10`.
  - Menyamakan ukuran elemen internal & ritme vertikal sidebar minimize (`$compact`, `72px`) dengan mode expanded (ikon `w-5 h-5`, CTA `+` `w-12 h-12`, tombol profil `py-2.5 rounded-xl` + avatar `size="md"` `40px`, `gap-3.5`/`gap-0.5`/`pt-3`) serta `scrollbar-gutter: stable` agar posisi shell konsisten antar halaman.
  - Verifikasi: `npm run build`, `artisan view:cache`, dan `ChatTest` + `ProjectShowcaseTest` lulus.
- **Verifikasi**: `npm run build` exit 0, `artisan view:cache` ok, Laravel Pint `passed`, full suite **237 test (1.006 assertions) passed**.
- **Sistem Manajemen & Tata Kelola Komunitas Kompleks ala Facebook Groups** (`2026_11_19_165421_upgrade_communities_management_schema.php`, `Community.php`, `CommunityMember.php`, `CommunityPost.php`, `CommunityJoinRequest.php`, `CommunityBan.php`, `CommunityAuditLog.php`, `CommunityMembershipNotification.php`, `UpdateCommunitySettingsRequest.php`, `CommunityManagementController.php`, `CommunityController.php`, `CommunityPostController.php`, `InfiniteFeedController.php`, `routes/web.php`, `resources/js/app.js`, `communities/index.blade.php`, `communities/show.blade.php`, `communities/manage.blade.php`, `components/community-post.blade.php`, `feeds/_community-show-items.blade.php`, `CommunityManagementTest.php`):
  - **Skema & Model**: Menambahkan pengaturan kebijakan komunitas (`join_policy`, `post_policy`, `allow_member_comments`, `allow_member_images`, `welcome_message`, `join_questions`, `rules_items`, `location`, `website_url`), status mute anggota (`muted_until`, `mute_reason`), moderasi postingan (`status`, `is_locked`, `reviewed_by`, `reviewed_at`, `rejection_reason`), serta tabel baru `community_join_requests`, `community_bans`, dan `community_audit_logs`.
  - **Hierarki Peran & Tata Kelola**: Mendukung Owner, Admin, Moderator, dan Member (`canManageRole`, `isOwner`, `isAdmin`, `isModerator`, `isBanned`, `isMuted`) serta panel multi-tab `/komunitas/{slug}/kelola` (`antrian`, `anggota`, `pengaturan`, `log`) untuk menyetujui/menolak permintaan gabung beserta pertanyaan masuk, antrean persetujuan postingan, promosi/demosi peran, mute/kick/ban/unban anggota, kunci komentar per postingan, transfer kepemilikan, penghapusan komunitas oleh Owner, dan pencatatan audit log (`CommunityAuditLog`) serta notifikasi (`CommunityMembershipNotification`).
  - **UI & Akses**: Menampilkan daftar "Komunitas yang Kamu Kelola" di `communities/index`, tab `Diskusi` / `Anggota` / `Tentang & Aturan`, banner sambutan/mute/postingan pending, serta gerbang privasi/banned aman di `communities/show` dan `InfiniteFeedController`.
  - **Verifikasi**: `npm run build` exit 0 (`built in 1.18s`), `artisan view:cache` ok, Laravel Pint `--dirty` ok (`UpdateCommunitySettingsRequest.php` diformat), dan 5 suite fitur (`CommunityManagementTest`, `BackendSecurityAndSpacesTest`, `CommentThreadIntegrityTest`, `AdminModerationTest`, `SearchTest`) **38/38 test (234 assertions) passed**.
- **Lencana Relasi Sosial (`Mengikutimu` & `Teman`) serta QA & Perbaikan Menyeluruh Web** (`ProfileController.php`, `users/show.blade.php`, `ProfileTest.php`, `communities/show.blade.php`, `partials/sidebar.blade.php`, `partials/notification-bell.blade.php`, `layouts/app.blade.php`, `projects/index.blade.php`, `notifications/index.blade.php`, `chat/_list.blade.php`, `database/seeders/NampungYukSeeder.php`, `NotificationTest.php`, `CommunityManagementTest.php`):
  - **Relasi Sosial Profil**: Menambahkan status `followsViewer` dan `isMutualFriend` (`Mengikutimu` / `Teman`) yang reaktif di halaman profil pengguna.
  - **Perbaikan QA Manual**: Memperbaiki `SyntaxError` AlpineJS pada banner sambutan komunitas via method `init()`/`dismiss()`, memindahkan CTA `+ Pamer Kodingan` sidebar ke footer tetap agar tidak tumpang tindih dengan navigasi di layar `900px`, mengekstrak `partials/notification-bell.blade.php` ke header desktop `/`, memetakan notifikasi `community_membership` ke kategori `Komunitas`, memindahkan toast global ke bawah (`bottom-20 lg:bottom-6`), mengarahkan penolakan pesan aktif di sidebar obrolan ke `/obrolan`, dan memastikan akun demo di `NampungYukSeeder` berstatus terverifikasi.
  - **Verifikasi**: Chrome re-verify seluruh kasus non-Spaces bersih (`exceptions: []`), `npm run build`, `artisan view:cache`, Laravel Pint, dan 109/109 test suite fitur (548 assertions) lulus.
- **Syarat Minimal 100 Pengikut Buat Komunitas & Spaces Offline** (`Community.php`, `CommunityController.php`, `communities/index.blade.php`, `MassDummySeeder.php`, `2026_11_20_062830_create_space_signals_table.php`, `SpaceSignalLog.php`, `Space.php`, `SpaceSignal.php`, `SpaceController.php`, `routes/web.php`, `resources/js/app.js`, `spaces/show.blade.php`, `BackendSecurityAndSpacesTest.php`, `CommunityManagementTest.php`):
  - **Komunitas Minimal 100 Pengikut**: Menambahkan `Community::MIN_FOLLOWERS_TO_CREATE = 100` & `Community::canBeCreatedBy()` (admin platform dikecualikan), penegakan `403` di `CommunityController@store`, tampilan progress bar `X / 100 pengikut` di modal `communities/index.blade.php`, serta seeding relasi follow di `MassDummySeeder`.
  - **Spaces Offline-Ready**: Mengembalikan `type` pada komponen Alpine `spaceRoom`, mengosongkan STUN eksternal (`iceServers: []`) saat host lokal/offline, menangani ketiadaan mic/kamera secara halus agar user tetap bisa gabung mode dengar/chat, menambahkan `<audio>` peer pada Audio Space, membuat tabel `space_signals` + `SpaceSignalLog` + endpoint `GET /spaces/{space}/sync` (polling fallback saat Reverb `:8080` mati), dan membungkus broadcast dengan `safeBroadcast()` agar tidak melempar HTTP 500.
- **Alur Spaces Terstruktur ala X Spaces & Gerbang Host Kreator** (`2026_11_20_010121_add_structured_roles_and_schedule_to_spaces_tables.php`, `Space.php`, `SpaceParticipant.php`, `SpaceSignal.php`, `SpaceController.php`, `routes/web.php`, `resources/js/app.js`, `spaces/index.blade.php`, `spaces/show.blade.php`, `BackendSecurityAndSpacesTest.php`):
  - **Gerbang Pembuatan Space**: Menambahkan `Space::hostEligibilityFor()` & `Space::canBeCreatedBy()` — hanya Admin atau kreator dengan email terverifikasi, `>= 100` pengikut, `>= 3` karya publik aktif, `reputation_points >= 100`, tautan GitHub/website, dan bebas catatan moderasi. Modal di `spaces/index.blade.php` menampilkan checklist syarat secara transparan bila belum terpenuhi.
  - **Siklus & Panggung X-Style**: Menambahkan status `scheduled` (`start_mode`, `scheduled_at`, tombol `POST /spaces/{space}/start`), penyematan karya bahasan (`pinned_project_id`, `POST /spaces/{space}/pin`), pemisahan panggung (`host`, `cohost` maks. 2, `speaker` maks. 10) vs `listener`, alur angkat tangan (`POST /spaces/{space}/request-speak`), moderasi peran (`POST /spaces/{space}/participants/{user}/role`: `approve_speaker`, `decline_request`, `promote_speaker`, `demote_listener`, `promote_cohost`, `demote_cohost`, `step_down`, `mute`, `unmute`, `kick`), sinkronisasi status mic server-side (`POST /spaces/{space}/mic`), dan reaksi cepat (`reaction`).
  - **WebRTC & UI Role-Aware**: Pendengar bergabung dalam mode dengar tanpa memicu izin mic/kamera hingga disetujui naik panggung; saat diturunkan atau dibisukan paksa, trek audio/video lokal langsung dimatikan.
  - **Verifikasi**: `artisan migrate` ok, `npm run build` ok, `artisan view:cache` ok, Laravel Pint `passed`, dan full PHPUnit suite **261/261 tests (1267 assertions) passed**.
- **Sistem Draft Postingan (Auto-Save saat Refresh/Bug + Simpan Manual ke Draft) & Auto-Normalisasi HTTPS** (`Project.php`, `ProjectController.php`, `ProfileController.php`, `routes/web.php`, `projects/create.blade.php`, `projects/drafts.blade.php`, `projects/edit.blade.php`, `projects/show.blade.php`, `users/show.blade.php`, `ProjectDraftsTest.php`):
  - **Auto-Normalisasi URL**: `ProjectController` otomatis menambahkan `https://` pada field URL (`demo_url`, `github_url`, `prototype_url`, `thumbnail_url`, `video_url`) bila user mengetik domain polos berformat valid (seperti `domain.id`) tanpa skema, sambil tetap menolak string invalid (`not-a-valid-url`) dan skema berbahaya (`javascript:`, dll.) lewat `SafeUrl`.
  - **Auto-Save, Anti-Refresh Error & Recovery**: Halaman `/unggah` menyimpan state ke `localStorage` dan menyinkronkan otomatis ke DB via `POST /unggah/autosave` (debounce saat mengetik + `navigator.sendBeacon` saat `pagehide`/`beforeunload`). Submit form kini memakai `fetch` JSON + validasi URL client-side sehingga error validasi (`422`) atau error server tampil inline tanpa reload halaman (file gambar yang dipilih dan caption tidak pernah hilang).
  - **Simpan & Kelola Draft**: Menambahkan tombol **Simpan ke Draft** di `/unggah`, opsi visibilitas `Draft` di `/project/{slug}/edit`, halaman `/draft` (`projects.drafts`), aksi publikasi langsung `POST /project/{slug}/publish`, serta tab **Draft** khusus pemilik di profil (`users/show.blade.php`). Draft dikecualikan dari `Project::scopeVisibleTo()` dan `Category::projects_count` hingga dipublikasikan.
  - **Verifikasi**: `vendor/bin/pint --dirty --format agent` passed, `artisan view:cache` ok, `npm run build` ok (`1.13s`), dan full PHPUnit suite **264/264 tests (1309 assertions) passed**.

## 3j. Dokumentasi README, Tangkapan Layar & Perbaikan Kartu Pendengar Spaces (20 Nov 2026)

- **Bug Alpine `spaceRoom`** (`resources/js/app.js`): properti `me` hanya hidup di closure sehingga `Number(part.id) !== Number(me)` di daftar peserta panggung melempar `ReferenceError: me is not defined`; sekarang `me` ikut di-return dari komponen Alpine. Commit `454e3b1`, diverifikasi full suite **264/264 tests (1309 assertions) passed**.
- **Penyempurnaan [README.md](README.md)**: bagian **Fitur Utama** diperbarui mengikuti fitur terbaru (Composer Anti-Refresh & Draft, Spaces X-Style, Komunitas ala Facebook Groups dengan gerbang 100 pengikut, Obrolan 2-pane & kebijakan DM, Profil Kreator), angka test suite disinkronkan ke **264 tests / 1.309 assertions**, ditambah info akun demo seeder (`@budi_santoso`, `@siti_rahma`) dan perintah opsional `reverb:start`, serta bagian **Struktur Direktori Utama** dirapikan.
- **12 tangkapan layar asli `docs/images/`**: `banner.png`, `feed.png`, `detail-project.png`, `form-unggah.png`, `draft-karya.png`, `spaces.png`, `komunitas.png`, `obrolan.png`, `profil.png`, `autentikasi.png`, `admin-moderasi.png`, dan `responsive-dark-mode.png` (mobile `390x844`, Dark Mode Only) — semuanya diambil dari aplikasi yang berjalan di `http://localhost:8000` (Chrome headless, akun demo seeder) dan dipasang di bagian **Tangkapan Layar & Pratinjau** README.
- **Perbaikan UI kartu Pendengar Spaces** (`resources/views/spaces/show.blade.php`): kartu pendengar sebelumnya menampilkan nama terpotong (`Q...`) karena tombol moderasi berbagi satu baris; grid diubah ke `grid-cols-1 sm:grid-cols-2 md:grid-cols-3` dengan tata letak `flex-col` (baris identitas + baris aksi ber-divider) sehingga nama tampil penuh di semua lebar layar.
- **Verifikasi**: `npm run build` exit 0, `artisan view:cache` ok, `BackendSecurityAndSpacesTest` **13/13 passed** (`109` assertions), screenshot `spaces.png` diambil ulang. Commit `2175d0a` (`Update README with current features and screenshots, refine Space listener card`, 14 berkas) sudah di-push ke `origin/main` (`454e3b1..2175d0a`) dan working tree bersih.

## 3k. Konsol Moderasi Admin Terstruktur & Visibilitas Peringatan User (22 Nov 2026)

- **Sinkronisasi Kolom `warned_at` (`database/migrations/2026_11_22_040216_sync_warned_at_column_on_users_table.php`)**: migrasi idempotent untuk menyamakan skema `users.warning_at` lawas di SQLite lokal ke `users.warned_at` sehingga `POST /admin/user/{id}/warning` tidak lagi melempar `SQLSTATE[HY000]: no such column: warned_at`.
- **Konsol Moderasi Admin Terstruktur (`AdminModerationController.php`, `resources/views/admin/reports.blade.php`)**:
  - Merapikan halaman `/admin/laporan` menjadi 4 kartu KPI yang dapat diklik (`Laporan Menunggu`, `Konten Berperingatan`, `Akun Peringatan`, `Akun Diblokir`) dan 3 bagian utama (`Antrean Laporan` dengan sub-filter target & status, `Manajemen Pengguna` dengan pencarian + filter status, serta `Konten Berperingatan`).
  - Menambahkan tombol eskalasi `Beri Peringatan` / `Edit Peringatan` langsung di kartu laporan akun, menjaga kompatibilitas penuh query param legacy (`tab=projects`, `tab=users`, `tab=all_users`, `tab=banned`), dan mengoptimalkan `index()` agar hanya memuat query pagination bagian yang sedang aktif.
- **Notifikasi & Banner Peringatan Pemilik Akun (`ModerationWarningNotification.php`, `notifications/index.blade.php`, `layouts/app.blade.php`)**: mengirim notifikasi database `moderation_warning` saat admin memperingatkan akun atau proyek, menampilkannya di halaman `/notifikasi`, serta menampilkan banner peringatan non-intrusif khusus pemilik akun yang sedang dalam peringatan di bar atas shell (disembunyikan di halaman profil sendiri agar tidak ganda).
- **Danger Modals & Redirect Pintar Hapus Akun/Konten (`AdminModerationController.php`, `resources/views/admin/reports.blade.php`, `resources/views/users/show.blade.php`)**: mengganti dialog `confirm()` browser menjadi modal konfirmasi Token Harbor untuk hapus akun (wajib ketik `@username`) dan hapus konten, serta memperbarui `destroyUser()` agar auto-resolve laporan pending dan tetap berada di `/admin/laporan` bila dipicu dari panel moderasi.

---

## 4. Backlog Masa Depan (Rekomendasi Lanjutan)

1. **Konvensi Warna untuk Pekerjaan Baru (wajib)**: jangan tulis `bg-[#hex]` lagi. Pakai utility token: `bg-[var(--surface)]`, `text-[var(--text-muted)]`, `border-[var(--border-color)]`, `bg-[var(--primary)]` + `text-[var(--primary-text)]` untuk CTA, dst. Nilai token didefinisikan sekali di `:root`/`.dark` pada `resources/css/app.css`.
2. **WebRTC Scaling**: Peningkatan arsitektur live audio Spaces dari WebSockets mesh ke Selective Forwarding Unit (SFU) seperti LiveKit bila peserta per ruangan melebihi 8 pembicara bersamaan.
3. **Animasi Terminal**: Penambahan efek ketik interaktif pada mock window `nampungyuk.config.ts` di halaman autentikasi.
4. **Optimasi Video Transcoding Queue**: Pemindahan proses `ffmpeg` synchronous di `MediaOptimizer` ke Laravel Queue background worker untuk mengantisipasi unggahan video berdurasi panjang.
