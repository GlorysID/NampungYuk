# Project Context: NampungYuk

NampungYuk adalah platform showcase karya programmer dan wadah kolaborasi komunitas developer Indonesia. Platform ini dirancang untuk memamerkan proyek perangkat lunak, membagikan tantangan teknis, mendokumentasikan proses belajar, serta memfasilitasi interaksi langsung antardeveloper.

---

## 1. Spesifikasi Teknologi & Lingkungan

- **Framework Backend**: Laravel 13.30.1
- **Bahasa Pemrograman**: PHP 8.3 (Binary Laragon: `C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe`)
- **Basis Data**:
  - Pengembangan Lokal: MySQL 8.4 (Port 3306, Database: `nampungyuk`, User: `root`, Password: kosong)
  - Pengujian Otomatis (PHPUnit): SQLite `:memory:`
- **Layanan Realtime**: Laravel Reverb 1.12.0 + Laravel Echo 2.5.0 + Pusher-JS 8.6.0 (Port 8080)
- **Kompresi & Optimasi Media**: `App\Services\MediaOptimizer` (PHP GD untuk WebP, CLI `ffmpeg` untuk video H.264)
- **Frontend & Bundling**: Blade Templates, Tailwind CSS v4.3.3 (`@tailwindcss/vite`), Alpine.js 3.17.1, Vite 8.2.2
- **Tipografi**: Geist & Geist Mono
- **Sistem Operasi**: Windows 11 (Host: Windows PowerShell 5.1)

---

## 2. Standar Desain Visual (Token Harbor Aesthetic)

Platform menggunakan bahasa visual arsitektural terinspirasi dari gaya **Token Harbor** yang mengutamakan kontras tinggi, hairline border, serta keterbacaan tingkat tinggi:

### A. Palet Warna Utama

| Token | Dark Mode | Light Mode | Penggunaan |
| :--- | :--- | :--- | :--- |
| **Canvas Utama (`--bg-app`)** | `#0B0B0D` | `#F6F5F0` | Latar belakang halaman aplikasi |
| **Permukaan Kartu (`--surface`)** | `#121214` | `#FFFFFF` | Latar belakang kartu feed, sidebar, dialog |
| **Permukaan Melayang (`--surface-hover`)** | `#19191D` | `#EFECE6` | Hover state tombol sekunder dan baris |
| **Warna Utama (`--primary`)** | `#EAE8E1` | `#0B0B0D` | Tombol CTA solid, tab aktif, teks utama |
| **Teks Tombol Utama (`--primary-text`)** | `#0B0B0D` | `#EAE8E1` | Teks di dalam tombol utama |
| **Teks Utama (`--text-main`)** | `#EAE8E1` | `#0B0B0D` | Judul, heading, nama entitas |
| **Teks Sekunder (`--text-muted`)** | `#8E8D86` | `#6E6D67` | Deskripsi, tagline, metadata waktu |
| **Garis Batas (`--border-color`)** | `#242426` | `#E2DFD7` | Hairline border 1px pada kartu dan divider |
| **Aksen Sukses (`--success`)** | `#10B981` | `#10B981` | Status Live Demo, indikator aktif, upvote aktif |
| **Aksen Peringatan (`--warning`)** | `#F59E0B` | `#F59E0B` | Banner peringatan moderasi |
| **Aksen Bahaya (`--danger`)** | `#EF4444` | `#EF4444` | Tombol hapus, status banned |

### B. Status Implementasi Token (Penting untuk Agent Berikutnya)

- **Sumber kebenaran tunggal**: seluruh token di atas didefinisikan di `resources/css/app.css` (`:root` untuk light, `.dark` untuk dark) dan **harus** jadi satu-satunya sumber warna. Tema tetap double-mode; jangan memangkas light mode.
- **Konvensi wajib untuk kode baru**: **jangan** menulis `bg-[#hex]`, `text-[#hex]`, atau `border-[#hex]` beserta pasangan `dark:`-nya. Pakai utility token, mis. `bg-[var(--surface)]`, `hover:bg-[var(--surface-hover)]`, `text-[var(--text-muted)]`, `border-[var(--border-color)]`, `divide-[var(--border-color)]`, dan `bg-[var(--primary)]` + `text-[var(--primary-text)]` untuk CTA solid. Token otomatis flip karena nilainya didefinisikan per mode; tidak perlu `dark:` untuk warna dasar.
- **Status migrasi (9 Nov 2026, selesai)**: 2.348 pasangan `*-[#light] dark:*-[#dark]` sudah dikollaps menjadi satu utility token. Literal hex di view turun 4.843 → 276 (-94%), dan aturan komponen di `app.css` sudah 0 hex. Bundle CSS produksi turun 133,68 kB → 105,90 kB.
- **Token tambahan hasil migrasi** (di luar tabel palet utama di atas): `--surface-subtle`, `--surface-sunken`, `--surface-input`, `--surface-deep`, `--surface-chip-hover`, `--surface-muted-flat`, `--surface-invert`, `--action-invert`, `--border-soft`, `--text-soft`, `--text-dim`, `--text-strong`, `--text-placeholder`. Nilainya diambil persis dari pasangan hex yang dipakai komponen, jadi tidak ada perubahan visual.
- **Sisa 276 hex yang disengaja** (jangan "dibersihkan" tanpa alasan):
  - Pulau selalu-gelap yang tidak boleh flip: code block `bg-[#0B0B0D] text-emerald-400`, panel video Spaces, editor wizard `projects/create` & `projects/edit`.
  - Pasangan yang opacity-nya beda antar mode (mis. `divide-[#E2DFD7]/60 dark:divide-[#242426]`, `bg-[#EFECE6]/70 dark:bg-[#161619]`) dan satu `!important` (`dark:ring-[#121214]!`).
- **Palette Tailwind yang masih dipakai**: `emerald` 334x, `rose` 328x, `amber` 164x (skala status lengkap: `/10`, `/15`, `/30`, `-600`, `-700`), `zinc` 34x, plus `purple`/`violet`/`blue` 39x. Skala ini sengaja tidak dipetakan ke token tunggal karena akan mengubah hue (rose-500 `#F43F5E` vs `--danger` `#EF4444`).
- **Pengecualian sah**: `#4285F4`/`#EA4335`/`#FBBC05`/`#34A853` adalah logo Google OAuth — jangan dimigrasikan.

### C. Aturan Dimensi & Tipografi
- **Radius (kondisi yang diterima, jangan diubah tanpa approval desain)**: proyek ini meng-override skala Tailwind di `:root` → `rounded-md` 8px, `rounded-lg` 12px, `rounded-xl` **16px**, `rounded-2xl` 16px, `rounded-full` pill. Artinya `rounded-xl` dan `rounded-2xl` setara; jangan "merapikan" ke skala default Tailwind — sudah dicoba (kartu 12px + media full-bleed) dan ditolak user karena terlihat berantakan.
- **Kartu permukaan** (feed, 2 kartu right rail, panel halaman) dan **container media inset** di kartu feed memakai radius 16px dengan hairline border 1px; gambar di kartu feed **inset ber-border**, bukan full-bleed ke tepi kartu.
- **Verifikasi radius**: pakai px hasil komputasi (`getComputedStyle(el).borderRadius`) atau token efektif di CSS build — grep CSS bisa menangkap definisi Tailwind lebih dulu padahal override `:root` yang menang.
- **Shell Maksimal**: `max-w-[1440px]` terpusat.
- **Sidebar Kiri Desktop**: Lebar `280px` (layar lg) hingga `300px` (layar xl), dengan kontainer internal `overflow-y-auto sidebar-scroll`.
- **Kolom Utama Feed**: Maksimal `760px` pada beranda, dapat dilebarkan ke `max-w-none` untuk halaman kerja penuh (Obrolan, Spaces, Moderasi Admin, Form Unggah/Edit).
- **Rail Kanan**: Lebar `340px` (layar xl) hingga `370px`, memuat pencarian global kontekstual dan rekomendasi karya/komunitas.
- **Header**: Tinggi konsisten `h-16` (56px-64px), sticky dengan efek translucent backdrop blur.

---

## 3. Struktur Skema Database & Relasi

Basis data terdiri dari 20 tabel domain dan framework:

1. **`users`**: Data akun, profil sosial, reputasi (`reputation_points`), hak akses (`is_admin`), serta riwayat moderasi (`is_banned`, `ban_reason`, `warning_notes`).
2. **`user_links`**: Tautan portofolio eksternal developer (`user_id`, `label`, `url`, `sort_order`).
3. **`follows`**: Graf sosial hubungan pengikut dan yang diikuti (`follower_id`, `following_id`).
4. **`categories`**: Kategori teknologi proyek (`name`, `slug`, `icon`, `projects_count`).
5. **`projects`**: Entitas utama karya perangkat lunak:
   - Metadata: `title`, `slug`, `tagline`, `description`, `tech_stacks` (JSON), `category_id`, `user_id`.
   - Media: `thumbnail`, `images` (JSON), `video`, `videos` (JSON), `video_poster`.
   - Tautan: `demo_url`, `github_url`, `prototype_url`.
   - Status & Metrik: `status`, `project_type`, `visibility` (`public` / `private`), `score`, `upvotes_count`, `downvotes_count`, `comments_count`, `views_count`, `reposts_count`.
   - Moderasi: `is_flagged`, `warning_notes`, `is_pinned`.
6. **`project_votes`**: Rekam jejak voting karya (`project_id`, `user_id`, `type`, `ip_address`). Dilengkapi composite unique index `(project_id, user_id)`.
7. **`project_bookmarks`**: Koleksi proyek tersimpan (`project_id`, `user_id`, `ip_address`). Dilengkapi composite unique index `(project_id, user_id)`.
8. **`project_reposts`**: Bagikan ulang proyek ke linimasa profil (`project_id`, `user_id`).
9. **`project_comments`**: Komentar dan diskusi bertingkat pada karya (`project_id`, `user_id`, `parent_id`, `content`).
10. **`communities`**: Grup developer berbasis topik (`name`, `slug`, `owner_id`, `topic`, `visibility`, `members_count`, `posts_count`).
11. **`community_members`**: Keanggotaan grup komunitas (`community_id`, `user_id`, `role` = `member` / `mod`).
12. **`community_posts`**: Postingan diskusi dan tanya jawab komunitas (`community_id`, `user_id`, `type`, `content`, `images`, `is_answered`, `is_pinned`, `score`).
13. **`community_post_votes`**: Voting upvote postingan komunitas.
14. **`community_post_comments`**: Balasan dan komentar pada postingan komunitas.
15. **`spaces`**: Ruangan live audio/video WebRTC (`title`, `slug`, `host_id`, `type`, `status` = `live` / `ended`).
16. **`space_participants`**: Peserta aktif ruangan space (`space_id`, `user_id`, `role`, `is_muted`).
17. **`conversations`**: Sesi obrolan privat antar dua pengguna (`user_one_id`, `user_two_id`, `status` = `accepted` / `pending`, `initiator_id`).
18. **`messages`**: Isi pesan percakapan dengan fitur soft-delete dan pelacakan edit (`conversation_id`, `sender_id`, `body`, `read_at`, `edited_at`, `deleted_at`).
19. **`reports`**: Sistem pelaporan konten polimorfik (`reporter_id`, `reportable_type`, `reportable_id`, `reason`, `notes`, `status`, `admin_notes`, `resolved_by`).
20. **`notifications`**: Notifikasi aktivitas akun dan platform.

---

## 4. Pola Arsitektur & Aturan Penting

### A. Alur Direct Message & Chat Gate (Instagram-Style Monotonic)
- Pesan pertama antara dua pengguna berstatus `accepted` langsung jika penerima sudah mengikuti pengirim.
- Jika belum saling mengikuti, pesan masuk sebagai `pending` (permintaan pesan) dan pengirim hanya diizinkan mengirim 1 pesan pembuka.
- Status `accepted` bersifat satu arah (monotonik): setelah diterima, utas tidak pernah diturunkan kembali ke `pending`.
- Antarmuka chat menggunakan reaktivitas murni Alpine.js (`x-show="isPending"`, `x-show="canSend"`) tanpa server-rendered `@if` wrapper agar transisi realtime via Echo dapat berjalan tanpa reload halaman.

### B. Kebijakan Privasi Karya & Komunitas
- Proyek berstatus `private` atau yang memiliki `warning_notes` disaring menggunakan scope `Project::scopeVisibleTo($userId)` sehingga hanya dapat dilihat oleh pemiliknya dan administrator.
- Seluruh mutasi (vote, bookmark, comment, repost) serta penyiaran realtime (`ProjectPublished`, `CommentPosted`) diblokir jika karya berstatus privat atau bertanda peringatan.
- Feed komunitas privat (`/feed-fragment/community/{slug}`) dibatasi dengan pengecekan hak keanggotaan aktif (`abort(403)` bagi non-member).

### C. WebRTC Spaces & Reverb Signaling
- Resolusi entitas space pada rute join, leave, end, dan signal mendukung parameter slug string maupun ID numerik (`resolveSpace`).
- Langganan channel privat Reverb `space.{spaceId}` ditunda di client hingga permintaan `POST /spaces/{space}/join` berhasil, untuk menghindari galat 403 Forbidden.
- Endpoint `SpaceController::signal` memvalidasi bahwa pengirim dan penerima adalah peserta aktif di tabel `space_participants`.

### D. Optimasi Performa & Query Memoization
- Query kategori, komunitas visible, dan jumlah laporan pending pada sidebar di-memoize per siklus request di `AppServiceProvider` melalui atribut request `__sidebar_composer_data`.
- Entitas widget rail (`rail:trending_projects_v2` dan `projects.hot_hashtags`) disimpan dalam cache selama 5 menit dengan struktur data array ter-serialisasi murni (mencegah error `unserialize` objek Eloquent).

---

## 5. Panduan Operasional & Perintah Verifikasi

Selalu gunakan path binary PHP 8.3 Laragon di Windows PowerShell 5.1:

```powershell
# Menjalankan seluruh pengujian (Feature & Unit Tests)
& "C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe" artisan test --compact

# Menjalankan pengujian untuk file tertentu
& "C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe" artisan test --compact tests/Feature/ProjectShowcaseTest.php
& "C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe" artisan test --compact tests/Feature/BackendSecurityAndSpacesTest.php
& "C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe" artisan test --compact tests/Feature/LoginTest.php

# Kompilasi aset frontend Vite & Tailwind
npm run build

# Menjalankan Laravel Pint Code Formatter
& "C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe" vendor/bin/pint --dirty --format agent

# Validasi kompilasi Blade view
& "C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe" artisan view:cache
& "C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe" artisan view:clear
```
