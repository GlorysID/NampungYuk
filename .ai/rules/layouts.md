---
paths:
  - resources/views/layouts/app.blade.php
---

# Layouts

## Header search is contextual via $onCommunity / $onProfile
The header search bar (desktop #nav-search + mobile #nav-search-mobile) always GETs to projects.index unless on communities.*, where it targets communities.index. A third context, $onProfile (route profile.show), injects a hidden `u={username}` so results scope to that creator's projects in ProjectController@index. When adding new page contexts, update BOTH forms and keep the `u` param out of non-profile searches.

## Shell scroll: rail kanan sticky tanpa scrollbar kedua, sidebar kiri non-scroll
Rail kanan (`layouts/app.blade.php:450`) harus tetap `sticky top-0 max-h-screen overflow-y-auto no-scrollbar`. User sudah dua kali menolak varian lain: rail dengan scrollbar terlihat (minta sisa scrollbar paling kanan saja) dan rail tanpa sticky (keluhannya "ikut ilang" saat di-scroll). Jangan jadikan rail `h-screen` tanpa sticky, dan jangan biarkan scrollbar dalam tampil.
Sidebar kiri (`partials/sidebar.blade.php`) sengaja TANPA scroll internal ("solid, tidak bisa di scroll"); isi yang melebihi layar terpotong oleh `overflow-hidden`, bukan ter-scroll. Kelas `.sidebar-scroll` sudah dihapus dari `app.css` karena tak terpakai — jangan pakai lagi.

## Dark mode only: jangan tambah toggle tema
Aplikasi ini dark-mode-only. `<html class="dark">` di-hardcode di `layouts/app.blade.php` dan 5 halaman auth (`auth/*.blade.php`); jangan menambahkan toggle tema, script `localStorage.theme`, atau `prefers-color-scheme` lagi. `Alpine.store('theme')` sudah dihapus dari `resources/js/app.js`.
Tailwind memakai `@custom-variant dark (&:where(.dark, .dark *))` (`app.css:4`), jadi utility `dark:` di view tetap benar dan selalu aktif — jangan dihapus. Palet light di blok `:root` `app.css` sengaja dibiarkan sebagai dead code; jangan dianggap bug.
