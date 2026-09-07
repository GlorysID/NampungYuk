# Dev Environment: PHP 8.3 path, MySQL, dan test environment

## PHP executable (WAJIB)
- PHP di system PATH (XAMPP 8.2.12) TIDAK kompatibel dengan app ini (butuh >= 8.3).
- Selalu gunakan full path untuk PHP/artisan/composer:
  - `& "C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe" artisan <cmd> --no-interaction`
  - `& "C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe" "C:\laragon\bin\composer\composer.phar" <args>`
- Pint harus dijalankan via PHP 8.3 juga (vendor\bin\pint adalah script PHP, bukan batch).

## Database
- Dev DB: MySQL 8.4.3 (Laragon) di 127.0.0.1:3306, database `nampungyuk`, user root, password kosong. Konfigurasi sudah ada di `.env`.
- CLI mysql: `& "C:\laragon\bin\mysql\mysql-8.4.3-winx64\bin\mysql.exe" -u root nampungyuk -e "..."`
- phpunit.xml memakai sqlite `:memory:` untuk test — JANGAN diubah.
- Standar: semua tabel domain wajib FK constraint eksplisit + index sejak migration pertama.

## Konvensi kode
- Model User memakai PHP attribute style Laravel 13: `#[Fillable([...])]`, `#[Hidden([...])]`, `#[Cast(...)]` — ikuti pola ini.
- Laravel 13 (framework 13.x) TIDAK punya attribute `#[Rule]` untuk validasi — gunakan method `rules()` array di FormRequest.
- Route "/" (welcome) tidak bernama — jangan pakai `route('/')`, gunakan `url('/')`.
