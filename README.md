# NampungYuk

Platform showcase karya programmer dan wadah kolaborasi komunitas developer. NampungYuk dirancang untuk memamerkan proyek perangkat lunak, mendokumentasikan proses belajar, membagikan tantangan teknis, serta membangun jaringan antardeveloper di Indonesia.

<!-- TEMPAT GAMBAR: BANNER UTAMA PROYEK -->
<!-- Letakkan gambar banner atau logo mockup platform di path: docs/images/banner.png -->
![Banner NampungYuk](docs/images/banner.png)

---

## Daftar Isi

1. [Tentang Platform](#tentang-platform)
2. [Fitur Utama](#fitur-utama)
3. [Tangkapan Layar & Pratinjau](#tangkapan-layar--pratinjau)
4. [Teknologi yang Digunakan](#teknologi-yang-digunakan)
5. [Prasyarat Sistem](#prasyarat-sistem)
6. [Panduan Instalasi Lokal](#panduan-instalasi-lokal)
7. [Konfigurasi Environment (.env)](#konfigurasi-environment-env)
8. [Pengujian Aplikasi](#pengujian-aplikasi)
9. [Struktur Direktori Utama](#struktur-direktori-utama)
10. [Lisensi](#lisensi)

---

## Tentang Platform

NampungYuk hadir sebagai solusi bagi para pengembang aplikasi untuk tidak hanya sekadar menyimpan repositori kode, tetapi juga memamerkan produk yang siap dinilai, dicoba, dan didiskusikan oleh komunitas. Setiap karya yang diunggah memuat informasi komprehensif, mulai dari tech stack, arsitektur, cara instalasi lokal, hingga tantangan dan pembelajaran berharga selama proses pembuatan.

---

## Fitur Utama

### 1. Showcase Proyek & Feed Interaktif
- Penelusuran proyek berdasarkan tab Trending, Terbaru, Populer, dan Prototipe Interaktif.
- Filter pencarian berbasis kategori kodingan dan tech stack (tagar teknologi).
- Sistem voting (upvote/downvote) dengan kalkulasi skor reputasi.
- Fitur bookmark (koleksi tersimpan) dan repost karya.

### 2. Informasi Proyek Mendalam
- Tautan langsung ke live demo, repositori kode sumber (GitHub/GitLab), dan prototipe desain (Figma).
- Panduan teknis cara menjalankan proyek (setup instructions).
- Dokumentasi tantangan (challenges) dan hal yang dipelajari (learnings).
- Media galeri foto dan demonstrasi video.

### 3. Manajemen Siklus Hidup Karya (CRUD)
- Formulir multi-step wizard untuk unggah karya baru secara bertahap dan rapi.
- Kemampuan edit dan pembaruan data proyek oleh pemilik karya atau administrator.
- Fitur hapus proyek dengan penghapusan relasi data (komentar, bookmark, vote) secara aman.

### 4. Komunitas Developer
- Pembuatan dan pengelolaan grup komunitas berbasis topik atau teknologi.
- Diskusi publik, sistem tanya-jawab (Q&A) dengan penandaan solusi terjawab.
- Pin postingan penting oleh moderator komunitas.

### 5. Obrolan Antar Pengguna (Chat)
- Sistem obrolan langsung (direct message) antar developer terdaftar.
- Permintaan obrolan (chat request) untuk menjaga privasi pengguna.

### 6. Autentikasi Fleksibel & Terverifikasi
- **Pendaftaran Manual:** Wajib melakukan verifikasi alamat email via link aktivasi yang dikirimkan ke kotak masuk sebelum dapat login.
- **Login Google (OAuth):** Masuk langsung menggunakan akun Google. Jika alamat email sudah pernah didaftarkan secara manual, sistem akan otomatis menautkan akun dan menandainya sebagai terverifikasi tanpa membuat akun duplikat.
- Fitur reset password melalui email dan pembaruan kata sandi di pengaturan akun.

### 7. Moderasi & Keamanan Konten
- Laporan pelanggaran konten dari pengguna (spam, malware/judi, pelanggaran hak cipta, pelecehan).
- Dashboard moderasi terpusat bagi administrator untuk meninjau laporan, memberikan teguran (warning), take-down karya bermasalah, hingga pemblokiran akun (banned).

---

## Tangkapan Layar & Pratinjau

Bagian ini memuat panduan visual antarmuka platform. Seluruh gambar disimpan dalam direktori `docs/images/`.

### 1. Halaman Beranda & Feed Utama
<!-- TEMPAT GAMBAR: FEED UTAMA -->
<!-- Simpan tangkapan layar beranda (feed proyek) pada path: docs/images/feed.png -->
![Halaman Feed Utama NampungYuk](docs/images/feed.png)

### 2. Halaman Detail Karya & Dokumentasi Teknis
<!-- TEMPAT GAMBAR: DETAIL PROYEK -->
<!-- Simpan tangkapan layar detail proyek (deskripsi, tantangan, setup) pada path: docs/images/detail-project.png -->
![Halaman Detail Proyek](docs/images/detail-project.png)

### 3. Formulir Unggah & Edit Karya (Wizard Form)
<!-- TEMPAT GAMBAR: FORM UNGGAH KARYA -->
<!-- Simpan tangkapan layar formulir wizard upload pada path: docs/images/form-unggah.png -->
![Formulir Unggah Karya](docs/images/form-unggah.png)

### 4. Halaman Komunitas & Diskusi
<!-- TEMPAT GAMBAR: KOMUNITAS -->
<!-- Simpan tangkapan layar halaman komunitas pada path: docs/images/komunitas.png -->
![Halaman Komunitas Developer](docs/images/komunitas.png)

### 5. Fitur Obrolan Developer
<!-- TEMPAT GAMBAR: OBROLAN -->
<!-- Simpan tangkapan layar antarmuka chat pada path: docs/images/obrolan.png -->
![Fitur Obrolan Antar Pengguna](docs/images/obrolan.png)

### 6. Autentikasi & Verifikasi Email
<!-- TEMPAT GAMBAR: MODAL AUTENTIKASI ATAU VERIFIKASI EMAIL -->
<!-- Simpan tangkapan layar modal login atau halaman verifikasi email pada path: docs/images/autentikasi.png -->
![Halaman Verifikasi Email & Autentikasi](docs/images/autentikasi.png)

### 7. Dashboard Moderasi Administrator
<!-- TEMPAT GAMBAR: PANEL MODERASI ADMIN -->
<!-- Simpan tangkapan layar dashboard moderasi laporan pada path: docs/images/admin-moderasi.png -->
![Panel Moderasi Administrator](docs/images/admin-moderasi.png)

### 8. Tampilan Responsif Mobile & Dark Mode
<!-- TEMPAT GAMBAR: RESPONSIVE / DARK MODE -->
<!-- Simpan tangkapan layar tampilan mobile drawer atau tema gelap pada path: docs/images/responsive-dark-mode.png -->
![Tampilan Mode Gelap dan Ponsel](docs/images/responsive-dark-mode.png)

---

## Teknologi yang Digunakan

- **Bahasa Pemrograman:** PHP 8.3+
- **Framework Backend:** Laravel 11 / 12
- **Frontend & UI:** Blade Templates, TailwindCSS, Alpine.js
- **Basis Data:** SQLite (default lokal) / MySQL (produksi)
- **Autentikasi Eksternal:** Laravel Socialite (Google OAuth 2.0)
- **Layanan Realtime:** Laravel Reverb (WebSockets)
- **Kompresi Media:** Intervention Image & WebP Optimizer
- **Testing:** PHPUnit 12

---

## Prasyarat Sistem

Sebelum menjalankan proyek di komputer lokal, pastikan perangkat Anda telah terpasang:

1. **PHP:** Versi 8.3 atau lebih baru (dengan ekstensi `pdo_sqlite`, `pdo_mysql`, `mbstring`, `fileinfo`, `gd` aktif).
2. **Composer:** Versi 2.x
3. **Node.js:** Versi 18.x atau 20.x dan **npm**
4. **Git**

---

## Panduan Instalasi Lokal

Ikuti langkah-langkah berikut untuk memasang proyek dari repositori:

### 1. Klon Repositori
```bash
git clone https://github.com/GlorysID/NampungYuk.git
cd NampungYuk
```

### 2. Pasang Dependensi Backend (PHP)
```bash
composer install
```

### 3. Pasang Dependensi Frontend (JavaScript & CSS)
```bash
npm install
```

### 4. Salin Berkas Konfigurasi Lingkungan
```bash
copy .env.example .env
```
*(Gunakan `cp .env.example .env` jika menggunakan Linux/macOS).*

### 5. Generate Application Key
```bash
php artisan key:generate
```

### 6. Buat Tautan Penyimpanan Media (Storage Symlink)
```bash
php artisan storage:link
```

### 7. Jalankan Migrasi & Seeder Database
Secara default, NampungYuk menggunakan SQLite lokal yang langsung siap digunakan tanpa memerlukan server database terpisah:
```bash
php artisan migrate --seed
```

### 8. Kompilasi Aset Frontend
Untuk mode pengembangan aktif:
```bash
npm run dev
```
Atau untuk build produksi:
```bash
npm run build
```

### 9. Jalankan Server Pengembangan
Buka terminal baru dan jalankan:
```bash
php artisan serve
```
Akses aplikasi melalui browser di alamat: `http://localhost:8000`.

---

## Konfigurasi Environment (.env)

Berikut penjelasan parameter konfigurasi utama pada berkas `.env`:

### 1. Basis Data (Database)

#### Menggunakan SQLite (Rekomendasi Cepat / Bawaan):
```env
DB_CONNECTION=sqlite
# Parameter DB_HOST, DB_PORT, DB_DATABASE dapat dikomentari
```

#### Menggunakan MySQL:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nampungyuk
DB_USERNAME=root
DB_PASSWORD=
```

### 2. Pengiriman Email (Mail Server)

#### Mode Pengujian Lokal (Tercatat di Log):
```env
MAIL_MAILER=log
MAIL_FROM_ADDRESS="no-reply@nampungyuk.com"
MAIL_FROM_NAME="NampungYuk"
```
*Tautan verifikasi email dan reset password dapat dilihat di berkas `storage/logs/laravel.log`.*

#### Pengiriman Email Sungguhan (SMTP / Gmail / Mailtrap):
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=alamat_email_anda@gmail.com
MAIL_PASSWORD=app_password_16_digit_gmail
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="no-reply@nampungyuk.com"
MAIL_FROM_NAME="NampungYuk"
```

### 3. Login dengan Google (Google OAuth)
Dapatkan kredensial OAuth dari [Google Cloud Console](https://console.cloud.google.com/):
```env
GOOGLE_CLIENT_ID=your-google-client-id.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=your-google-client-secret
GOOGLE_REDIRECT_URI=http://localhost:8000/auth/google/callback
```

---

## Pengujian Aplikasi

Proyek ini dilengkapi dengan cakupan pengujian unit dan fitur komprehensif menggunakan PHPUnit:

```bash
# Menjalankan seluruh pengujian
php artisan test

# Menjalankan pengujian dengan output ringkas
php artisan test --compact

# Menjalankan pengujian untuk fitur tertentu
php artisan test --filter=ProjectShowcaseTest
php artisan test --filter=LoginTest
php artisan test --filter=GoogleAuthTest
```

Untuk memastikan konsistensi gaya penulisan kode sesuai standar Laravel:
```bash
php vendor/bin/pint --format agent
```

---

## Struktur Direktori Utama

```text
NampungYuk/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AdminModerationController.php   # Moderasi laporan & pengguna
│   │   │   ├── Auth/                           # Login, register, oauth, verifikasi email
│   │   │   ├── ChatController.php              # Fitur obrolan langsung
│   │   │   ├── CommunityController.php         # Manajemen komunitas
│   │   │   ├── ProfileController.php           # Profil & kata sandi
│   │   │   └── ProjectController.php           # Showcase, unggah, edit, hapus karya
│   │   └── Middleware/                         # Proteksi admin, banned check
│   ├── Models/                                 # Model Eloquent (User, Project, Community, dll)
│   └── Services/                               # Media optimizer & business logic
├── database/
│   ├── factories/                              # Factory data testing
│   ├── migrations/                             # Skema tabel database
│   └── seeders/                                # Seeder data demo awal
├── docs/
│   └── images/                                 # Direktori tangkapan layar untuk README
├── resources/
│   ├── css/                                    # Berkas stylesheet Tailwind
│   ├── js/                                     # Skrip Alpine.js & aplikasi frontend
│   └── views/                                  # Template Blade halaman antarmuka
├── routes/
│   ├── channels.php                            # Broadcast channels WebSockets
│   ├── console.php                             # Perintah artisan kustom
│   └── web.php                                 # Rute navigasi web aplikasi
└── tests/                                      # Pengujian fitur & unit (PHPUnit)
```

---

## Lisensi

Proyek NampungYuk dilisensikan di bawah lisensi terbuka [MIT](LICENSE). Anda bebas menggunakan, memodifikasi, dan mengembangkan kode sumber ini sesuai kebutuhan.
