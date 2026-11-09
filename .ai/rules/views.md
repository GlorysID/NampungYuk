---
paths:
  - 'resources/views/**/*.blade.php'
---

# Views

## Pakai token warna, jangan hex literal
Semua warna di Blade wajib lewat token dari resources/css/app.css: bg-[var(--surface)], hover:bg-[var(--surface-hover)], text-[var(--text-muted)], border-[var(--border-color)], divide-[var(--border-color)], dan CTA solid = bg-[var(--primary)] + text-[var(--primary-text)]. Jangan tulis bg-[#hex] plus pasangan dark:*-nya; token sudah flip otomatis per mode. Pengecualian: pulau selalu-gelap (code block, panel video, editor wizard), pasangan dengan opacity beda antar mode, dan warna logo Google OAuth (#4285F4 dll).
