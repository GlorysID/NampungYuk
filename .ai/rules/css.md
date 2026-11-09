---
paths:
  - 'resources/css/**'
---

# Css

## Token warna hanya didefinisikan di :root dan .dark
Nilai warna platform hidup di :root (light) dan .dark (dark) dalam app.css. Aturan komponen (mis. .btn-primary, .hl-new-badge, .project-nav-card) harus memakai var(--token), bukan hex, dan tidak perlu blok .dark duplikat kalau tokennya sudah flip. Blok @theme hanya mendaftarkan ramp zinc/emerald; token semantik dipakai lewat arbitrary value var(--...), bukan utility @theme.

## Radius: skala proyek di-override di :root — jangan "dirapikan"
`:root` di app.css sengaja meng-override skala Tailwind: `--radius-sm` 6px, `--radius-md` 8px, `--radius-lg` 12px, `--radius-xl` 16px. Akibatnya `rounded-xl` = 16px = sama dengan `rounded-2xl`. Ini kondisi yang diterima; jangan menghapus override atau memperketat radius tanpa approval desain — sudah dicoba (kartu jadi 12px + media full-bleed) dan ditolak user karena terlihat berantakan. `.project-nav-card` juga meng-hardcode `border-radius: var(--radius-xl)` + `overflow: hidden`; mengubah radius kartu feed berarti mengubah aturan itu, bukan cuma class Blade. Verifikasi radius lewat px komputasi (`getComputedStyle(el).borderRadius`), bukan nama class atau grep CSS (grep bisa kena definisi Tailwind dulu padahal override proyek yang menang di cascade).
