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
- `AGENTS.md` "Aturan Alur Tamu" diperbarui: dulu melarang membajak klik baca tamu ke detail karya, sekarang detail karya memang login-only. Test lama `test_guest_can_view_project_detail` diganti `test_guest_is_sent_back_with_login_modal_when_opening_project_detail` (assert redirect + `auth_required` + opener modal tampil).
- Verifikasi: full suite **232 test (973 assertion) passed**, Pint clean.

---

## 4. Backlog Masa Depan (Rekomendasi Lanjutan)

1. **Konvensi Warna untuk Pekerjaan Baru (wajib)**: jangan tulis `bg-[#hex]` lagi. Pakai utility token: `bg-[var(--surface)]`, `text-[var(--text-muted)]`, `border-[var(--border-color)]`, `bg-[var(--primary)]` + `text-[var(--primary-text)]` untuk CTA, dst. Nilai token didefinisikan sekali di `:root`/`.dark` pada `resources/css/app.css`.
2. **WebRTC Scaling**: Peningkatan arsitektur live audio Spaces dari WebSockets mesh ke Selective Forwarding Unit (SFU) seperti LiveKit bila peserta per ruangan melebihi 8 pembicara bersamaan.
3. **Animasi Terminal**: Penambahan efek ketik interaktif pada mock window `nampungyuk.config.ts` di halaman autentikasi.
4. **Optimasi Video Transcoding Queue**: Pemindahan proses `ffmpeg` synchronous di `MediaOptimizer` ke Laravel Queue background worker untuk mengantisipasi unggahan video berdurasi panjang.
