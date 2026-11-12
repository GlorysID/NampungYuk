# Laporan Audit Komprehensif: NampungYuk

**Tanggal Audit:** 9 November 2026  
**Status Test Suite:** 142 Passed / 506 Assertions (PHPUnit 12, SQLite `:memory:`)  
**Lingkungan:** Laravel 13.30.1, PHP 8.3.30, Tailwind CSS v4, Dark Mode Only  

---

## 1. Ringkasan Eksekutif

Audit komprehensif dilakukan secara read-only terhadap seluruh codebase NampungYuk mencakup aspek **Keamanan**, **Performa**, **Integritas Data**, **Utang Teknis**, **Cakupan Pengujian**, dan **Konsistensi Desain/Dokumentasi**.

Secara fungsional, platform beroperasi stabil dengan seluruh 142 pengujian otomatis lulus. Namun, audit menemukan beberapa celah privasi data (leakage pada karya privat/berperingatan), masalah N+1 query pada feed, dan ketidaksinkronan penyimpanan profil yang perlu segera diperbaiki.

---

## 2. Temuan Keamanan (Security)

### S1 [Tinggi] Endpoint Publik `/feed/latest` Membocorkan Proyek Privat & Berperingatan
- **Lokasi:** `app/Http/Controllers/ProjectController.php:224`
- **Bukti:** `$query = Project::with(['user','category'])->recent();` dijalankan tanpa memanggil scope `visibleTo(Auth::id())`. Route publik `GET /feed/latest` (`projects.latest`) dapat diakses tanpa login.
- **Dampak:** Siapapun dapat mengambil judul, slug, thumbnail, dan metadata karya privat atau karya yang sedang terkena peringatan/moderasi.
- **Rekomendasi:** Tambahkan `$query->visibleTo(Auth::id());` sebelum pagination.

### S2 [Tinggi] `relatedProjects` di Halaman Detail Membocorkan Proyek Privat/Warned
- **Lokasi:** `app/Http/Controllers/ProjectController.php:287-292`
- **Bukti:** Mengambil proyek terkait dengan kategori sama tanpa menyaring menggunakan `visibleTo(Auth::id())`.
- **Dampak:** Sidebar "Project Terkait" menampilkan judul karya privat milik pengguna lain kepada publik.
- **Rekomendasi:** Terapkan `visibleTo(Auth::id())` pada query `$relatedProjects`.

### S3 [Tinggi] Backdoor Login Hardcoded pada Lingkungan Non-Produksi
- **Lokasi:** `app/Http/Controllers/Auth/LoginController.php:36-46`
- **Bukti:** Saat `app()->environment('local', 'testing')`, input username `budi` atau `admin` dengan password `password`/`budi`/`admin` langsung login tanpa hashing.
- **Dampak:** Meski dibatasi `APP_ENV`, bila terjadi salah konfigurasi env di server staging/produksi, siapa pun dapat membajak akun admin tanpa kredensial sah.
- **Rekomendasi:** Hapus logika bypass ini atau ganti dengan database seeder kredensial standar.

### S4 [Sedang] Halaman Koleksi Bookmark (`/koleksi`) Tidak Memfilter Visibilitas
- **Lokasi:** `app/Http/Controllers/ProjectController.php:857-860`
- **Bukti:** `Project::whereIn('id', $projectIds)->latest()` tanpa `visibleTo(Auth::id())`. Berbeda dengan `InfiniteFeedController@bookmarkFeed:175` yang sudah memanggil `visibleTo`.
- **Dampak:** Proyek yang di-bookmark pengguna kemudian diubah jadi privat atau diberi peringatan tetap tampil di daftar koleksi.
- **Rekomendasi:** Samakan dengan infinite feed bookmark, gunakan `Project::whereIn('id', $projectIds)->visibleTo(Auth::id())->latest()`.

### S5 [Sedang] Atribut Counter & Moderasi Terbuka di `$fillable`
- **Lokasi:** `app/Models/Project.php:16-49` dan `app/Models/User.php:16`
- **Bukti:** Kolom seperti `upvotes_count`, `score`, `is_pinned`, `is_flagged`, `warning_notes`, `is_admin`, `is_banned` terdaftar di `$fillable` / `#[Fillable]`.
- **Dampak:** Saat ini controller memakai array eksplisit, namun penggunaan `$request->all()` di masa depan dapat memicu mass-assignment vulnerability (eskalasi admin/manipulasi skor).
- **Rekomendasi:** Hapus kolom status moderasi dan counter dari fillable.

### S6 [Sedang] Ketiadaan Rate Limiting pada Aksi Berat (Upload Media, Chat, Vote)
- **Lokasi:** `routes/web.php`
- **Bukti:** Endpoint upload media & transcode FFmpeg (`projects.store`/`update`), pengiriman chat (`chat.send`), dan interaksi vote belum diproteksi middleware `throttle`.
- **Dampak:** Rentan terhadap spam atau DoS CPU/disk server melalui upload video massal berulang.
- **Rekomendasi:** Tambahkan middleware `throttle:60,1` atau `throttle:upload` pada route mutasi media dan chat.

---

## 3. Temuan Performa (Performance)

### P1 [Tinggi] N+1 Query pada Tombol Follow di Kartu Feed
- **Lokasi:** `resources/views/components/follow-button.blade.php:9,16` dipanggil via `x-project-card` -> `x-project-meta`.
- **Bukti:** Setiap kartu memanggil `$authUser->isFollowing($user)` dan `$user->followers()->count()`. Pada feed 12 kartu, terjadi 24 query tambahan ke database.
- **Dampak:** Waktu respon halaman melambat drastis saat feed dirender.
- **Rekomendasi:** Eager-load relasi followings di controller atau gunakan eager load count `withCount('followers')`.

### P2 [Sedang] Dashboard Admin Melakukan Query Berat pada Seluruh Tab Sekaligus
- **Lokasi:** `app/Http/Controllers/AdminModerationController.php:26-60`
- **Bukti:** Menjalankan 7 query `COUNT` dan 3 query `paginate` di setiap request, meskipun view hanya menampilkan 1 tab aktif.
- **Dampak:** Beban query tidak perlu pada database admin saat jumlah data membesar.
- **Rekomendasi:** Query hanya data untuk tab yang sedang aktif (`request('tab')`).

### P3 [Sedang] Memuat Seluruh ID Komunitas ke Memori PHP
- **Lokasi:** `app/Http/Controllers/CommunityController.php:34` dan `app/Http/Controllers/InfiniteFeedController.php:83`
- **Bukti:** `Community::visibleTo()->pluck('id')->toArray()` lalu dimasukkan ke `whereIn`.
- **Rekomendasi:** Gunakan subquery Eloquent `whereIn('community_id', Community::visibleTo()->select('id'))`.

### P4 [Rendah] Kolom `projects.created_at` Belum Terindeks
- **Bukti:** Query `latest()` dan `trendingToday` sering melakukan sorting dan filtering berdasarkan `created_at`.
- **Rekomendasi:** Tambahkan index pada kolom `created_at` di tabel `projects`.

---

## 4. Temuan Integritas Data (Data Integrity)

### D1 [Sedang] Bug Update Profil: `github_url` dan `website_url` Tidak Tersimpan
- **Lokasi:** `app/Http/Controllers/ProfileController.php:53-59`
- **Bukti:** Validasi di `ProfileUpdateRequest` memvalidasi `github_url` dan `website_url`, tetapi array `$updateData` di controller tidak memasukkan kedua kolom tersebut saat menyimpan ke database.
- **Dampak:** Pengguna yang mengisi link GitHub atau Website di form edit profil mendapati datanya tidak pernah tersimpan (silent drop).
- **Rekomendasi:** Masukkan `$request->validated('github_url')` dan `$request->validated('website_url')` ke array `$updateData`.

### D2 [Sedang] Counter Denormalisasi Tanpa Transaksi Database
- **Lokasi:** `ProjectController@vote`, `CommunityController@toggleJoin`, dll.
- **Bukti:** Operasi `increment()` dan `decrement()` pada counter (`score`, `upvotes_count`, `members_count`) dijalankan di luar `DB::transaction`.
- **Dampak:** Request concurrent yang datang bersamaan dapat menyebabkan selisih antara nilai counter dan jumlah baris relasi aktual.
- **Rekomendasi:** Bungkus mutasi counter dan pembuatan record vote dalam satu blok `DB::transaction()`.

---

## 5. Temuan Utang Teknis & Kode Mati (Technical Debt)

1. **Method Model Tak Terpakai:**
   - `app/Models/Project.php:292`: `isVideoUrl()` tidak pernah dipanggil di mana pun.
   - `app/Models/Conversation.php:164`: `unreadCountFor()` tidak memiliki pemanggil.
2. **Komponen Blade Yatim:**
   - `resources/views/components/admin-badge.blade.php`, `loading-state.blade.php`, `input.blade.php`, `error-state.blade.php` tidak pernah dirender dengan tag `<x-...>`.
   - `resources/views/welcome.blade.php` adalah template default Laravel yang tidak lagi digunakan rute manapun.
3. **CSS Mati di `app.css`:**
   - Blok palet light di `:root` (baris 40-94) mati total karena aplikasi sudah **dark mode only**.
   - Kelas komponen dengan 0 pemakaian di views: `.hl-panel`, `.ny-glass`, `.ny-gradient-*`, `.btn-vercel-primary`, `.spotlight-card`, `.linear-pill`, `.btn-ghost`, `.ny-scroll`, `.ny-card--interactive`, `.hl-feed-enter`.
   - `.sidebar-scroll` sudah berhasil dihapus pada iterasi sebelumnya.

---

## 6. Cakupan Pengujian (Testing Coverage)

- **Total Pengujian Saat Ini:** 16 File Test (15 Feature, 1 Unit), 142 Test Cases, 506 Assertions.
- **Hasil:** 100% Lulus (Pass).
- **Area Berisiko yang Belum Memiliki Feature Test Khusus:**
  - `GET /feed/latest` (kebocoran privasi S1 belum pernah diuji)
  - Fragment AJAX infinite scroll: `feeds.projects` dan `feeds.bookmarks`
  - Aksi sukses komunitas: pembuatan postingan, join, voting postingan komunitas
  - WebRTC signaling event data delivery

---

## 7. Status Desain Visual & Token

1. **Aplikasi Dark Mode Only:** `<html class="dark">` di-hardcode di layout utama dan 5 halaman autentikasi. Seluruh toggle tema, storage `theme`, dan store Alpine telah dihapus.
2. **Pengurangan Hex:** Literal hex di view reguler telah ditekan dari 4.843 menjadi ~106 kemunculan.
3. **Kerapian Rail & Feed:** Rail kanan sekarang `sticky top-0 max-h-screen overflow-y-auto no-scrollbar` (utuh tanpa scrollbar kedua), sidebar kiri solid tanpa scroll internal, thumbnail dan chip tren dibuat borderless untuk mengurangi kebisingan visual.
4. **Navigasi Sidebar:** "Prototipe Interaktif" telah digantikan dengan rute "Obrolan", dan "Direktori Kreator" serta badge "v1.0" telah dibersihkan.
5. **Guest Gating:** Tamu (`@guest`) yang mengklik Komunitas, Spaces, Obrolan, atau Kategori Kodingan akan langsung memicu modal login (`openAuthModal('login')`).

---

## 8. Prioritas Tindakan Perbaikan (Action Items)

| Prioritas | ID | Deskripsi Tindakan | File Terkait |
| :--- | :--- | :--- | :--- |
| **P0 (Kritis)** | S1 | Tambahkan `visibleTo(Auth::id())` pada query `/feed/latest` | `app/Http/Controllers/ProjectController.php` |
| **P0 (Kritis)** | S2 | Tambahkan `visibleTo(Auth::id())` pada query `relatedProjects` | `app/Http/Controllers/ProjectController.php` |
| **P1 (Tinggi)** | D1 | Simpan `github_url` dan `website_url` saat pembaruan profil | `app/Http/Controllers/ProfileController.php` |
| **P1 (Tinggi)** | S4 | Terapkan `visibleTo` pada halaman koleksi bookmark `/koleksi` | `app/Http/Controllers/ProjectController.php` |
| **P2 (Sedang)** | P1 | Optimasi query follow-button di project card untuk hilangkan N+1 | `resources/views/components/follow-button.blade.php` |
| **P2 (Sedang)** | S3 | Bersihkan hardcoded login credentials di LoginController | `app/Http/Controllers/Auth/LoginController.php` |
| **P3 (Rendah)** | U1-U3 | Bersihkan method/komponen/CSS mati yang tidak terpakai | `app.css`, Model, Views |
