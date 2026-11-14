---
paths:
  - 'resources/views/**/*.blade.php'
---

# Views

## Pakai token warna, jangan hex literal
Semua warna di Blade wajib lewat token dari resources/css/app.css: bg-[var(--surface)], hover:bg-[var(--surface-hover)], text-[var(--text-muted)], border-[var(--border-color)], divide-[var(--border-color)], dan CTA solid = bg-[var(--primary)] + text-[var(--primary-text)]. Jangan tulis bg-[#hex] plus pasangan dark:*-nya; token sudah flip otomatis per mode. Pengecualian: pulau selalu-gelap (code block, panel video, editor wizard), pasangan dengan opacity beda antar mode, dan warna logo Google OAuth (#4285F4 dll).

## Notifikasi: standar sosmed (buka halaman = badge clear, list tetap tersimpan)
`notifications/index.blade.php` merender tiap baris sebagai link `<a>` biasa (`<a href="{{ $data['url'] ?? '#' }}">`). `NotificationController@index` menandai notifikasi belum-dibaca sebagai dibaca (`$user->unreadNotifications->markAsRead()`) begitu halaman dibuka, sehingga badge lonceng & angka di sidebar langsung hilang (0) seperti standar sosmed (X/IG/GitHub). Namun, query `$user->notifications()->paginate(20)` tetap menampilkan seluruh riwayat notifikasi (dibaca maupun belum), sehingga notifikasi TIDAK hilang saat di-refresh. Untuk route AJAX / mark-as-read individual, host redirect divalidasi ke host aplikasi. Avatar notifikasi `rounded-full`.

## Komentar = thread rata, bukan kartu
`components/comment-card.blade.php` dan tab komentar di `users/show.blade.php` TIDAK memakai chrome kartu (tanpa `rounded-xl`, `border`, `shadow-xs`, `bg-[var(--surface)]`, tanpa `p-4/p-5`). Pola yang dipakai: root `py-3`; layout `flex items-start gap-3` dengan avatar `w-8 shrink-0` + kolom isi `min-w-0 flex-1 space-y-1`; teks `text-[14px] leading-relaxed whitespace-pre-line`; baris aksi `gap-3 pt-0.5 text-xs`; balasan bersarang `ml-8 sm:ml-10 pl-3 sm:pl-4 border-l`. Pemisah antar komentar = `divide-y divide-[var(--border-color)]` di container daftar, jadi JANGAN tambah `border-b` di dalam kartu (garis dobel). Jangan kembalikan indent `pl-10` pada teks/aksi — itu yang bikin boros ruang. Terukur: komentar pendek ~89-114px per item (sebelumnya ~130-140px).
