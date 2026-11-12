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

---

## 4. Backlog Masa Depan (Rekomendasi Lanjutan)

1. **Konvensi Warna untuk Pekerjaan Baru (wajib)**: jangan tulis `bg-[#hex]` lagi. Pakai utility token: `bg-[var(--surface)]`, `text-[var(--text-muted)]`, `border-[var(--border-color)]`, `bg-[var(--primary)]` + `text-[var(--primary-text)]` untuk CTA, dst. Nilai token didefinisikan sekali di `:root`/`.dark` pada `resources/css/app.css`.
2. **WebRTC Scaling**: Peningkatan arsitektur live audio Spaces dari WebSockets mesh ke Selective Forwarding Unit (SFU) seperti LiveKit bila peserta per ruangan melebihi 8 pembicara bersamaan.
3. **Animasi Terminal**: Penambahan efek ketik interaktif pada mock window `nampungyuk.config.ts` di halaman autentikasi.
4. **Optimasi Video Transcoding Queue**: Pemindahan proses `ffmpeg` synchronous di `MediaOptimizer` ke Laravel Queue background worker untuk mengantisipasi unggahan video berdurasi panjang.
