---
paths:
  - resources/views/layouts/app.blade.php
  - resources/views/partials/sidebar.blade.php
---

# Layouts

## Header search is contextual via $onCommunity / $onProfile
The header search bar (desktop #nav-search + mobile #nav-search-mobile) always GETs to projects.index unless on communities.*, where it targets communities.index. A third context, $onProfile (route profile.show), injects a hidden `u={username}` so results scope to that creator's projects in ProjectController@index. When adding new page contexts, update BOTH forms and keep the `u` param out of non-profile searches.

## Shell scroll: rail kanan sticky tanpa scrollbar kedua, sidebar kiri non-scroll
Rail kanan (`layouts/app.blade.php`) harus tetap `sticky top-0 max-h-screen overflow-y-auto no-scrollbar`. User sudah dua kali menolak varian lain: rail dengan scrollbar terlihat (minta sisa scrollbar paling kanan saja) dan rail tanpa sticky (keluhannya "ikut ilang" saat di-scroll). Jangan jadikan rail `h-screen` tanpa sticky, dan jangan biarkan scrollbar dalam tampil.
Sidebar kiri (`partials/sidebar.blade.php`): area grup nav memakai `flex-1 min-h-0 overflow-y-auto no-scrollbar flex flex-col gap-6` + `justify-between` HANYA saat login (`auth()->check()`), supaya sisa tinggi disebar jadi jarak antar grup dan CTA tidak terpisah jauh dari baris profil. JANGAN pakai `justify-between` untuk tamu: tamu cuma punya 8 item, sisa ruang jadi ~95px per gap dan terlihat rusak. CTA `Pamer Kodingan` = tombol putih FULL-WIDTH `h-12 rounded-full` (pill selebar sidebar, bukan pill kecil rata kanan) sebagai anak terakhir area nav, `sticky bottom-0 z-10 bg-[var(--surface-invert)] pt-2 pb-3 border-t`, hanya `@auth`. Density item nav (disetujui user, meniru X): item `h-11 text-[15px]` ikon `w-5 h-5`, label grup `text-[11px] mb-1.5`, jarak antar grup `gap-5`, brand `h-12`. Menaikkan item ke `h-12`+ akan memotong item terbawah di viewport 858px (scrollbar sidebar disembunyikan) — jangan naikkan tanpa mengecilkan yang lain. Blok profil user `shrink-0` di bawahnya; tamu hanya melihat grid Masuk/Daftar `h-9`. Footer editorial (logo + link Feed/Pamerkan/Koleksi/Shortcuts/GitHub) TIDAK ada di kolom tengah — sudah dipindah ke dasar rail kanan (`layouts/app.blade.php`, setelah `@yield('rail')`, dibungkus `@unless(View::hasSection('hide_footer'))`). Konsekuensi: di halaman ber-`hide_right_rail` (chat, create, edit, spaces/show, admin/reports) footer tidak tampil. Efek samping lain: di tinggi viewport < ~720px item paling bawah perlu di-scroll, scrollbar tetap tersembunyi. Kelas `.sidebar-scroll` sudah dihapus dari `app.css` karena tak terpakai — jangan pakai lagi. Menu "Prototipe Interaktif" telah digantikan dengan "Obrolan"; badge "v1.0" sudah dihapus. "Direktori Kreator" tetap ada di grup Jelajahi (label "Permintaan Pengguna" di-assert `tests/Feature/ProjectShowcaseTest.php` — jangan dihapus/di-rename tanpa update test).

## Shell harus rata kiri, bukan center
`.ny-shell` wajib `justify-content: flex-start`. Dulu `center`: karena group kolom (sidebar + main + rail) lebarnya berubah antar breakpoint, kolom kiri ikut bergeser ke tengah dan muncul celah di kiri saat rail kanan di-hide (`hide_right_rail`) atau belum aktif (di bawah `xl`). Jangan kembalikan `justify-center` di `.ny-shell` maupun utility `justify-center` di elemen shell (`layouts/app.blade.php`). Nilai kolom di `<style>` ini menduplikasi class Tailwind di elemen yang sama — ubah keduanya bersamaan.

## Guest gating sidebar & mobile nav
Klik pada Komunitas, Spaces, Obrolan, dan Kategori Kodingan oleh tamu (`@guest`) dikunci memicu `window.openAuthModal('login')` (memakai `href="{{ auth()->check() ? ... : '#' }}"` dan `@guest @click.prevent="window.openAuthModal('login')" @endguest`). Jangan menghapus proteksi ini tanpa persetujuan user.

## Dark mode only: jangan tambah toggle tema
Aplikasi ini dark-mode-only. `<html class="dark">` di-hardcode di `layouts/app.blade.php` dan 5 halaman auth (`auth/*.blade.php`); jangan menambahkan toggle tema, script `localStorage.theme`, atau `prefers-color-scheme` lagi. `Alpine.store('theme')` sudah dihapus dari `resources/js/app.js`.
Tailwind memakai `@custom-variant dark (&:where(.dark, .dark *))` (`app.css:4`), jadi utility `dark:` di view tetap benar dan selalu aktif — jangan dihapus. Palet light di blok `:root` `app.css` sengaja dibiarkan sebagai dead code; jangan dianggap bug.
