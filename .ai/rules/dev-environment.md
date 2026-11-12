---
paths:
  - app/**
  - config/**
  - routes/**
  - database/**
---

# Dev Environment: PHP 8.3 path, MySQL, dan test environment

## PHP executable (WAJIB)
- PHP di system PATH default TIDAK kompatibel dengan Laravel 13 (butuh >= 8.3).
- Selalu gunakan full path untuk PHP/artisan/composer:
  - `& "C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe" artisan <cmd> --no-interaction`
  - `& "C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe" "C:\laragon\bin\composer\composer.phar" <args>`
- Pint harus dijalankan via PHP 8.3 juga (vendor\bin\pint adalah script PHP, bukan batch).

## Database
- Dev DB: Konfigurasi target MySQL 8.4 (Laragon) di 127.0.0.1:3306, database `nampungyuk`, user root, password kosong. Namun pada workspace lokal saat ini `.env` aktif memakai SQLite (`database/database.sqlite`).
- phpunit.xml memakai sqlite `:memory:` untuk test — JANGAN diubah.
- Standar: semua tabel domain wajib FK constraint eksplisit + index sejak migration pertama.

## Konvensi kode
- Model User memakai PHP attribute style Laravel 13: `#[Fillable([...])]`, `#[Hidden([...])]`, `#[Cast(...)]` — ikuti pola ini.
- Laravel 13 (framework 13.x) TIDAK punya attribute `#[Rule]` untuk validasi — gunakan method `rules()` array di FormRequest.
- Route "/" bernama `projects.index` — gunakan `route('projects.index')`.
