<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Project;
use App\Models\ProjectComment;
use App\Models\ProjectVote;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class NampungYukSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed 10 Realistic Indonesian Developer Personas
        $developers = [
            [
                'name' => 'Budi Santoso',
                'username' => 'budi_santoso',
                'email' => 'budi@example.com',
                'password' => Hash::make('password'),
                'avatar' => 'https://api.dicebear.com/7.x/avataaars/svg?seed=BudiSantoso',
                'bio' => 'Staff Backend Engineer @ Bandung. Mengabdi pada Laravel, PostgreSQL, & arsitektur Event-Driven. Suka kopi robusta & open-source.',
                'github_url' => 'https://github.com/budisantoso',
                'reputation_points' => 1520,
            ],
            [
                'name' => 'Dimas Aditya',
                'username' => 'dimas_aditya',
                'email' => 'dimas@example.com',
                'password' => Hash::make('password'),
                'avatar' => 'https://api.dicebear.com/7.x/avataaars/svg?seed=DimasAditya',
                'bio' => 'Frontend Architect & UI Tinkerer. Obsesi dengan 60fps animations, Tailwind CSS, dan micro-interactions di web modern.',
                'github_url' => 'https://github.com/dimasaditya',
                'reputation_points' => 1280,
            ],
            [
                'name' => 'Siti Rahmawati',
                'username' => 'siti_rahma',
                'email' => 'siti@example.com',
                'password' => Hash::make('password'),
                'avatar' => 'https://api.dicebear.com/7.x/avataaars/svg?seed=SitiRahma',
                'bio' => 'Data & Cloud Infrastructure Engineer. Hobi ngulik ClickHouse, Go, dan membangun pipeline data terdistribusi berskala besar.',
                'github_url' => 'https://github.com/sitirahma',
                'reputation_points' => 1640,
            ],
            [
                'name' => 'Fajar Nugraha',
                'username' => 'fajar_cli',
                'email' => 'fajar@example.com',
                'password' => Hash::make('password'),
                'avatar' => 'https://api.dicebear.com/7.x/avataaars/svg?seed=FajarNugraha',
                'bio' => 'Systems Programmer & Devops enthusiast. Menolak GUI kalau bisa diselesaikan dengan 1 baris Bash atau Golang CLI tool.',
                'github_url' => 'https://github.com/fajarnugraha',
                'reputation_points' => 1090,
            ],
            [
                'name' => 'Alif Pratama',
                'username' => 'alif_pratama',
                'email' => 'alif@example.com',
                'password' => Hash::make('password'),
                'avatar' => 'https://api.dicebear.com/7.x/avataaars/svg?seed=AlifPratama',
                'bio' => 'AI Researcher & Pythonista fokus pada NLP Bahasa Indonesia dan model bahasa lokal berbasis open-weights.',
                'github_url' => 'https://github.com/alifpratama',
                'reputation_points' => 1350,
            ],
            [
                'name' => 'Arya Wicaksono',
                'username' => 'arya_gamedev',
                'email' => 'arya@example.com',
                'password' => Hash::make('password'),
                'avatar' => 'https://api.dicebear.com/7.x/avataaars/svg?seed=AryaWicaksono',
                'bio' => 'Indie Game Developer. Membangun game 2D berbasis Phaser & Godot yang mengangkat cerita rakyat nusantara.',
                'github_url' => 'https://github.com/aryawicaksono',
                'reputation_points' => 1410,
            ],
            [
                'name' => 'Nabila Putri',
                'username' => 'nabila_mobile',
                'email' => 'nabila@example.com',
                'password' => Hash::make('password'),
                'avatar' => 'https://api.dicebear.com/7.x/avataaars/svg?seed=NabilaPutri',
                'bio' => 'Lead Flutter Engineer & Dart advocate. Berkeliling nusantara sambil ngoding aplikasi mobile offline-first ramah UMKM.',
                'github_url' => 'https://github.com/nabilaputri',
                'reputation_points' => 1190,
            ],
            [
                'name' => 'Rian Hidayat',
                'username' => 'rian_dev',
                'email' => 'rian@example.com',
                'password' => Hash::make('password'),
                'avatar' => 'https://api.dicebear.com/7.x/avataaars/svg?seed=RianHidayat',
                'bio' => 'Fullstack JavaScript & TypeScript ninja. Suka meracik tool developer productivity, micro-SaaS, dan bot otomasi.',
                'github_url' => 'https://github.com/rianhidayat',
                'reputation_points' => 970,
            ],
            [
                'name' => 'Kevin Pratama',
                'username' => 'kevin_security',
                'email' => 'kevin@example.com',
                'password' => Hash::make('password'),
                'avatar' => 'https://api.dicebear.com/7.x/avataaars/svg?seed=KevinPratama',
                'bio' => 'Application Security Engineer & Pentester. Hobi ngulik header HTTP, CVE audit, dan bikin checker konfigurasi DNS gratis.',
                'github_url' => 'https://github.com/kevinpratama',
                'reputation_points' => 1140,
            ],
            [
                'name' => 'Dwi Lestari',
                'username' => 'dwi_uiux',
                'email' => 'dwi@example.com',
                'password' => Hash::make('password'),
                'avatar' => 'https://api.dicebear.com/7.x/avataaars/svg?seed=DwiLestari',
                'bio' => 'Design Engineer: bridging the gap between Figma & clean CSS/Radix UI. Percaya performa rendering adalah bagian dari estetika.',
                'github_url' => 'https://github.com/dwilestari',
                'reputation_points' => 1060,
            ],
        ];

        $users = [];
        foreach ($developers as $dev) {
            $users[$dev['username']] = User::updateOrCreate(
                ['username' => $dev['username']],
                $dev
            );
        }

        // 2. Seed Categories
        $categoriesData = [
            [
                'name' => 'Web App',
                'slug' => 'web-app',
                'icon' => 'globe',
                'description' => 'Aplikasi web interaktif, SaaS, dashboard, dan e-commerce modern.',
            ],
            [
                'name' => 'Mobile App',
                'slug' => 'mobile-app',
                'icon' => 'smartphone',
                'description' => 'Aplikasi mobile iOS & Android (Flutter, React Native, Native).',
            ],
            [
                'name' => 'Open Source',
                'slug' => 'open-source',
                'icon' => 'git-branch',
                'description' => 'Library, framework extension, plugin, dan package open-source.',
            ],
            [
                'name' => 'AI & ML',
                'slug' => 'ai-ml',
                'icon' => 'sparkles',
                'description' => 'Model machine learning, AI agents, LLM wrappers, dan computer vision.',
            ],
            [
                'name' => 'Game Dev',
                'slug' => 'game-dev',
                'icon' => 'gamepad',
                'description' => 'Game web canvas, indie game, Phaser, Godot, dan Unity.',
            ],
            [
                'name' => 'CLI Tools',
                'slug' => 'cli-tools',
                'icon' => 'terminal',
                'description' => 'Command line interface, terminal user interface (TUI), otomasi & skrip.',
            ],
            [
                'name' => 'UI / Komponen',
                'slug' => 'ui-components',
                'icon' => 'palette',
                'description' => 'Design system, animasi UI, library komponen Tailwind & CSS.',
            ],
        ];

        $categories = [];
        foreach ($categoriesData as $cat) {
            $categories[$cat['slug']] = Category::updateOrCreate(
                ['slug' => $cat['slug']],
                $cat
            );
        }

        // 3. Seed Realistic Projects Across Multiple Fields
        $projectsData = [
            [
                'username' => 'budi_santoso',
                'category' => 'web-app',
                'title' => 'LaporBanjir - Peta Pantau & Peringatan Dini Banjir Warga',
                'tagline' => 'Peta interaktif pantau tinggi muka air dan laporan banjir real-time warga se-Jabodetabek.',
                'description' => "Proyek ini dibuat untuk membantu warga memantau titik-titik genangan air dan banjir secara real-time. Dilengkapi fitur pelaporan foto dengan geotagging otomatis, integrasi data TMA (Tinggi Muka Air) pintu air Manggarai & Katulampa, serta notifikasi via Telegram bot saat status Siaga 2 tercapai.\n\n### Fitur Utama:\n- Peta Leaflet interaktif dengan layer banjir & jalur evakuasi\n- Laporan warga terverifikasi otomatis dengan EXIF GPS check\n- Graf status pintu air terkini setiap 10 menit\n- Mode offline caching saat koneksi lambat\n\nSilakan dicoba dan berikan masukan atau PR di repository GitHub ya kawan-kawan!",
                'thumbnail' => 'https://images.unsplash.com/photo-1547683905-f686c993aae5?auto=format&fit=crop&w=1200&q=80',
                'demo_url' => 'https://laporbanjir.example.com',
                'github_url' => 'https://github.com/budisantoso/lapor-banjir',
                'prototype_url' => 'https://www.figma.com/proto/laporbanjir-interactive-prototype',
                'tech_stacks' => ['Laravel 12', 'Livewire 3', 'Leaflet.js', 'TailwindCSS', 'PostgreSQL'],
                'base_upvotes' => 384,
                'base_downvotes' => 8,
                'views_count' => 1890,
                'is_featured' => true,
                'created_at' => now()->subHours(2),
                'comments' => [
                    [
                        'username' => 'dimas_aditya',
                        'content' => 'Keren banget mas Budi! Mau tanya, untuk tileset peta Leaflet-nya pakai OpenStreetMap biasa atau custom styling Mapbox? Di mobile layarnya kerasa smooth banget transisinya.',
                        'upvotes' => 14,
                        'created_at' => now()->subHours(1)->subMinutes(40),
                    ],
                    [
                        'username' => 'budi_santoso',
                        'content' => '@dimas_aditya Makasih Dimas! Pakai CartoDB Positron tiles biar warnanya clean monochrome dan kontras sama titik merah genangan. Plus ada canvas tile rendering buat handle 500+ titik simultan.',
                        'upvotes' => 10,
                        'created_at' => now()->subHours(1)->subMinutes(20),
                    ],
                    [
                        'username' => 'siti_rahma',
                        'content' => 'Integrasi ke data Katulampa dan Manggarai ini scrap API publik atau ada endpoint resmi BBWSCC mas? Kalau butuh bantuan optimasi spatial indexing PostGIS kabarin ya!',
                        'upvotes' => 9,
                        'created_at' => now()->subHour(),
                    ],
                    [
                        'username' => 'budi_santoso',
                        'content' => '@siti_rahma Mantap mbak Siti, endpoint BBWSCC kadang suka timeout pas hujan deras haha, next PR mau tambahin cache Redis 5 menit biar gak overload ke server sumber.',
                        'upvotes' => 6,
                        'created_at' => now()->subMinutes(35),
                    ],
                ],
            ],
            [
                'username' => 'dimas_aditya',
                'category' => 'web-app',
                'title' => 'KetikCepat.id - MonkeyType Versi Korpus Bahasa Indonesia',
                'tagline' => 'Platform latihan mengetik 10 jari dengan perbendaharaan kata baku KBBI dan heatmap akurasi keyboard.',
                'description' => "Sering latihan mengetik di MonkeyType tapi kata-katanya selalu bahasa Inggris? KetikCepat.id dibangun khusus dengan dataset 5.000+ kata baku Bahasa Indonesia, kutipan sastra nusantara, dan latihan pengetikan kode programming (PHP, JS, Python, HTML).\n\n### Fitur:\n- Mode WPM 15s, 30s, 60s, & Custom Quotes\n- Heatmap tombol keyboard: mendeteksi jari mana yang sering typo\n- Mode Tema Gelap / Terang kustom\n- Tanpa iklan, 100% open source dan instan tanpa login",
                'thumbnail' => 'https://images.unsplash.com/photo-1587829741301-dc798b83add3?auto=format&fit=crop&w=1200&q=80',
                'demo_url' => 'https://ketikcepat.example.com',
                'github_url' => 'https://github.com/dimasaditya/ketik-cepat-id',
                'tech_stacks' => ['Vue 3', 'Vite', 'Pinia', 'TailwindCSS', 'Canvas API'],
                'base_upvotes' => 542,
                'base_downvotes' => 12,
                'views_count' => 3240,
                'is_featured' => true,
                'created_at' => now()->subHours(5),
                'comments' => [
                    [
                        'username' => 'budi_santoso',
                        'content' => 'Asik banget dipakai latihan pas istirahat ngoding. Baru coba 1 menit dapet 85 WPM di korpus kata baku Indonesia!',
                        'upvotes' => 11,
                        'created_at' => now()->subHours(4),
                    ],
                    [
                        'username' => 'fajar_cli',
                        'content' => 'Bisa tambahin mode pengetikan sintaks bash / vim keybindings gak bro @dimas_aditya? Pasti seru banget buat melatih muscle memory developer terminal.',
                        'upvotes' => 8,
                        'created_at' => now()->subHours(3),
                    ],
                    [
                        'username' => 'dimas_aditya',
                        'content' => '@fajar_cli Good idea mas Fajar! Kebetulan lagi siapin dataset sintaks CLI & shortcut regex buat update rilis minggu depan.',
                        'upvotes' => 7,
                        'created_at' => now()->subHours(2),
                    ],
                    [
                        'username' => 'dwi_uiux',
                        'content' => 'Desain keyboard heatmap-nya juara mas Dimas. Warna kontrasnya nyaman di mata dan animasinya gak ada lag sama sekali!',
                        'upvotes' => 5,
                        'created_at' => now()->subHour(),
                    ],
                ],
            ],
            [
                'username' => 'siti_rahma',
                'category' => 'web-app',
                'title' => 'GajiDev.id - Crowdsourced Benchmark Gaji Tech Indonesia',
                'tagline' => 'Platform transparansi kompensasi programmer, devops, dan QA di Indonesia dengan visualisasi grafik interaktif.',
                'description' => "Terinspirasi dari levels.fyi namun disesuaikan untuk ekosistem tech startup & korporat Indonesia. Memberikan gambaran realistis rentang gaji junior, mid, senior, dan tech lead berdasarkan kota dan tech stack yang digunakan.\n\n### Keamanan & Privasi:\n- 100% anonim, data di-hash tanpa penyimpanan identitas IP permanen\n- Outlier detection otomatis mencegah manipulasi data\n- Filter berdasarkan tahun pengalaman, level, dan lokasi (Jakarta, Bandung, Jogja, Remote)",
                'thumbnail' => 'https://images.unsplash.com/photo-1551288049-bebda4e38f71?auto=format&fit=crop&w=1200&q=80',
                'demo_url' => 'https://gajidev.example.com',
                'github_url' => 'https://github.com/sitirahma/gajidev-id',
                'prototype_url' => 'https://framer.com/share/gajidev-benchmark-ui',
                'tech_stacks' => ['Next.js 15', 'TypeScript', 'Prisma', 'PostgreSQL', 'Chart.js'],
                'base_upvotes' => 715,
                'base_downvotes' => 24,
                'views_count' => 5120,
                'is_featured' => true,
                'created_at' => now()->subHours(12),
                'comments' => [
                    [
                        'username' => 'rian_dev',
                        'content' => 'Inisiatif yang sangat bagus dan berani buat transparansi industri tech lokal. Datanya valid banget sama survei komunitas kemarin.',
                        'upvotes' => 19,
                        'created_at' => now()->subHours(10),
                    ],
                    [
                        'username' => 'kevin_security',
                        'content' => 'Soal privasi data salt dan hashing one-way-nya sudah solid banget mbak @siti_rahma. Gak bisa di-reverse engineering.',
                        'upvotes' => 12,
                        'created_at' => now()->subHours(8),
                    ],
                    [
                        'username' => 'siti_rahma',
                        'content' => '@kevin_security Terima kasih audit kilatnya bro Kevin! Keamanan data kontributor memang prioritas nomor satu kami.',
                        'upvotes' => 8,
                        'created_at' => now()->subHours(6),
                    ],
                ],
            ],
            [
                'username' => 'fajar_cli',
                'category' => 'cli-tools',
                'title' => 'Nusantara-CLI: Generator Dummy Data Indonesia Super Kencang',
                'tagline' => 'Tool CLI berbasis Golang untuk generate jutaan data palsu Indonesia (NIK, NPWP, Alamat, No Telp) untuk DB seeder.',
                'description' => "Kalau pakai Faker bawaan seringkali data Indonesianya kurang variatif atau lambat saat generate jutaan record. Nusantara-CLI mampu menghasilkan 1.000.000 record data dummy Indonesia dalam waktu kurang dari 1.2 detik ke format SQL, JSON, atau CSV.\n\n### Penggunaan:\n```bash\nnusantara generate --count 50000 --format sql --out users.sql\n```\nSupport NIK dengan validasi kode wilayah provinsi/kabupaten asli!",
                'thumbnail' => 'https://images.unsplash.com/photo-1629654297299-c8506221ca97?auto=format&fit=crop&w=1200&q=80',
                'demo_url' => 'https://github.com/fajarnugraha/nusantara-cli/releases',
                'github_url' => 'https://github.com/fajarnugraha/nusantara-cli',
                'tech_stacks' => ['Go', 'Cobra CLI', 'SQLite', 'Concurrency'],
                'base_upvotes' => 312,
                'base_downvotes' => 5,
                'views_count' => 1430,
                'is_featured' => false,
                'created_at' => now()->subDay(),
                'comments' => [
                    [
                        'username' => 'siti_rahma',
                        'content' => '1.2 detik buat 1 juta record itu kencang banget mas Fajar. Goroutine channel-nya di-buffer berapa per batch?',
                        'upvotes' => 10,
                        'created_at' => now()->subHours(20),
                    ],
                    [
                        'username' => 'fajar_cli',
                        'content' => '@siti_rahma Pakai worker pool 8 goroutine dengan buffer 10.000 batch insert, jadi disk IO gak bottleneck di syscall write.',
                        'upvotes' => 9,
                        'created_at' => now()->subHours(18),
                    ],
                    [
                        'username' => 'budi_santoso',
                        'content' => 'Langsung tak jadikan package pendamping seeder di project Laravel kantor. Sangat menghemat waktu seeding test db!',
                        'upvotes' => 7,
                        'created_at' => now()->subHours(14),
                    ],
                ],
            ],
            [
                'username' => 'alif_pratama',
                'category' => 'ai-ml',
                'title' => 'KamusGaul.AI - Slang Nusantara to Formal Indonesian Translator',
                'tagline' => 'Penerjemah AI bahasa Jaksel, slang daerah, dan singkatan Gen-Z ke Bahasa Indonesia formal sesuai PUEBI.',
                'description' => "Proyek eksperimental menggunakan model quantized LLM ringan yang di-fine-tune dengan 20.000 pasang kalimat slang lokal (bahasa Jaksel, singkatan chat WA, bahasa gaul Sunda, Jawa, dan Makassar).\n\nCocok untuk aplikasi customer support otomatis yang perlu memahami pesan pengguna informal atau sekadar seru-seruan menerjemahkan pesan santai jadi bahasa diplomatis.",
                'thumbnail' => 'https://images.unsplash.com/photo-1677442136019-21780ecad995?auto=format&fit=crop&w=1200&q=80',
                'demo_url' => 'https://kamusgaul.example.com',
                'github_url' => 'https://github.com/alifpratama/kamus-gaul-ai',
                'tech_stacks' => ['Python', 'FastAPI', 'Ollama', 'LangChain', 'Streamlit'],
                'base_upvotes' => 460,
                'base_downvotes' => 15,
                'views_count' => 2670,
                'is_featured' => false,
                'created_at' => now()->subDays(2),
                'comments' => [
                    [
                        'username' => 'dimas_aditya',
                        'content' => "Ngakak pas nyoba kalimat 'which is literally gua gak habis thinking' diterjemahkan jadi 'yang sebenarnya membuat saya sangat terheran-heran' wkwkwk akurat banget!",
                        'upvotes' => 24,
                        'created_at' => now()->subDays(1)->subHours(10),
                    ],
                    [
                        'username' => 'alif_pratama',
                        'content' => '@dimas_aditya Haha thanks mas Dimas! Corpus Jaksel memang paling rame variasinya pas training set.',
                        'upvotes' => 8,
                        'created_at' => now()->subDays(1)->subHours(8),
                    ],
                    [
                        'username' => 'rian_dev',
                        'content' => 'Ada rencana bikin extension Chrome atau Telegram bot-nya gak bro? Pasti rame dipakai orang kantor.',
                        'upvotes' => 6,
                        'created_at' => now()->subDays(1)->subHours(4),
                    ],
                ],
            ],
            [
                'username' => 'arya_gamedev',
                'category' => 'game-dev',
                'title' => 'Legenda Rimba: Petualangan Pixel Art 2D di Browser',
                'tagline' => 'Game web action-adventure 2D pixel art dengan gameplay ala Metroidvania bertema mitologi nusantara.',
                'description' => "Game indie yang dibuat penuh menggunakan Phaser.js tanpa game engine berat. Bisa dimainkan langsung di smartphone atau browser desktop dengan 60 FPS halus.\n\n### Yang Menarik:\n- Musik instrumen gamelan 8-bit chiptune gubahan sendiri\n- 4 Boss unik terinspirasi makhluk mitologi nusantara\n- Kontrol responsif gamepad & keyboard\n- Save progress langsung ke LocalStorage",
                'thumbnail' => 'https://images.unsplash.com/photo-1550745165-9bc0b252726f?auto=format&fit=crop&w=1200&q=80',
                'demo_url' => 'https://legendarimba.example.com',
                'github_url' => 'https://github.com/aryawicaksono/legenda-rimba-game',
                'tech_stacks' => ['Phaser 3', 'TypeScript', 'WebAudio API', 'Aseprite'],
                'base_upvotes' => 628,
                'base_downvotes' => 9,
                'views_count' => 4310,
                'is_featured' => true,
                'created_at' => now()->subDays(3),
                'comments' => [
                    [
                        'username' => 'dwi_uiux',
                        'content' => 'Palet warna pixel art-nya adem banget mas Arya! BGM gamelan chiptune-nya nagih parah.',
                        'upvotes' => 15,
                        'created_at' => now()->subDays(2)->subHours(12),
                    ],
                    [
                        'username' => 'arya_gamedev',
                        'content' => '@dwi_uiux Makasih banyak Dwi! BGM-nya diaransemen pakai Famitracker nada pelog dan slendro.',
                        'upvotes' => 9,
                        'created_at' => now()->subDays(2)->subHours(8),
                    ],
                    [
                        'username' => 'nabila_mobile',
                        'content' => 'Main di mobile browser lancar banget touch control-nya! Porting ke PWA atau APK Flutter webview mantap nih.',
                        'upvotes' => 7,
                        'created_at' => now()->subDays(2)->subHours(2),
                    ],
                ],
            ],
            [
                'username' => 'budi_santoso',
                'category' => 'open-source',
                'title' => 'Laravel-Rupiah: Helper & Cast Mata Uang Rupiah Otomatis',
                'tagline' => 'Package Laravel ringan untuk formatting Rupiah, spell-out terbilang kata, dan kalkulasi PPN/PPh.',
                'description' => "Package kecil tapi sangat sering dibutuhkan di hampir setiap project lokal. Cukup pasang via composer:\n```bash\ncomposer require budisantoso/laravel-rupiah\n```\nMenyediakan custom Eloquent Cast `AsRupiah::class`, fungsi Blade `@rupiah(\$amount)`, serta konversi angka ke terbilang kata otomatis (*'Satu Juta Lima Ratus Ribu Rupiah'*).",
                'thumbnail' => 'https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?auto=format&fit=crop&w=1200&q=80',
                'demo_url' => 'https://packagist.org/packages/budisantoso/laravel-rupiah',
                'github_url' => 'https://github.com/budisantoso/laravel-rupiah',
                'tech_stacks' => ['Laravel 11 & 12', 'PHP 8.3', 'Pest', 'GitHub Actions'],
                'base_upvotes' => 289,
                'base_downvotes' => 4,
                'views_count' => 1980,
                'is_featured' => false,
                'created_at' => now()->subDays(4),
                'comments' => [
                    [
                        'username' => 'siti_rahma',
                        'content' => 'Fitur terbilang katanya handle angka desimal sen rupiah juga gak mas Budi?',
                        'upvotes' => 6,
                        'created_at' => now()->subDays(3)->subHours(18),
                    ],
                    [
                        'username' => 'budi_santoso',
                        'content' => "@siti_rahma Yes mbak Siti, kalau ada sen misal Rp 1.500,50 otomatis keluar 'Satu Ribu Lima Ratus Rupiah Lima Puluh Sen'.",
                        'upvotes' => 8,
                        'created_at' => now()->subDays(3)->subHours(14),
                    ],
                ],
            ],
            [
                'username' => 'nabila_mobile',
                'category' => 'mobile-app',
                'title' => 'KasKite: Pencatat Keuangan UMKM Offline-First',
                'tagline' => 'Aplikasi pencatatan kas warung & UMKM yang tetap jalan mulus tanpa sinyal internet dengan sinkronisasi otomatis.',
                'description' => "Dibuat khusus untuk pedagang pasar dan warung kelontong yang sering berada di area minim sinyal. Menggunakan arsitektur offline-first SQLite lokal, dan secara otomatis melakukan background-sync saat terhubung WiFi/kuota.\n\n### Fitur Utama:\n- Kas masuk/keluar & cetak struk via thermal printer Bluetooth\n- Laporan laba/rugi harian dan bulanan otomatis\n- Dukungan multi-cabang dengan enkripsi end-to-end\n- Backup instan ke Google Drive / local storage",
                'thumbnail' => 'https://images.unsplash.com/photo-1556742049-0a67e5572246?auto=format&fit=crop&w=1200&q=80',
                'demo_url' => 'https://kaskite.example.com',
                'github_url' => 'https://github.com/nabilaputri/kaskite-mobile',
                'tech_stacks' => ['Flutter', 'Dart', 'Riverpod', 'SQLite', 'Supabase'],
                'base_upvotes' => 415,
                'base_downvotes' => 6,
                'views_count' => 2480,
                'is_featured' => true,
                'created_at' => now()->subDays(5),
                'comments' => [
                    [
                        'username' => 'dwi_uiux',
                        'content' => 'Desain UI kasirnya kelihatan ramah banget buat orang tua atau pedagang yang gak terlalu terbiasa dengan app modern. Tombol dan font-nya besar dan kontras!',
                        'upvotes' => 12,
                        'created_at' => now()->subDays(4)->subHours(16),
                    ],
                    [
                        'username' => 'nabila_mobile',
                        'content' => '@dwi_uiux Makasih feedback-nya Dwi! Kemarin sempat riset dan test langsung ke pedagang pasar tradisional di Sleman buat uji kemudahan navigasinya.',
                        'upvotes' => 10,
                        'created_at' => now()->subDays(4)->subHours(12),
                    ],
                    [
                        'username' => 'budi_santoso',
                        'content' => 'Sinkronisasi offline-to-cloud-nya pakai mekanisme CRDT atau timestamp vector clock mbak Nabila?',
                        'upvotes' => 7,
                        'created_at' => now()->subDays(4)->subHours(6),
                    ],
                    [
                        'username' => 'nabila_mobile',
                        'content' => '@budi_santoso Pakai event log berbasis vector clock mas Budi, jadi kalau pedagang offline 3 hari terus online, data tidak ada yang saling overwrite.',
                        'upvotes' => 9,
                        'created_at' => now()->subDays(4)->subHours(2),
                    ],
                ],
            ],
            [
                'username' => 'kevin_security',
                'category' => 'cli-tools',
                'title' => 'KawalDNS: CLI Pemantau Propagasi DNS ISP Indonesia',
                'tagline' => 'Alat CLI cepat untuk cek propagasi record DNS langsung ke 12 provider ISP lokal di Indonesia.',
                'description' => "Sering kesel habis setup domain atau ganti IP server tapi gak yakin apakah resolver ISP lokal (IndiHome, Biznet, FirstMedia, Telkomsel, XL, MyRepublic) sudah update atau masih menahan cache lama?\n\nKawalDNS melakukan query serentak via DoH (DNS over HTTPS) dan resolver publik lokal untuk memberikan report status propagasi dalam hitungan 500ms.",
                'thumbnail' => 'https://images.unsplash.com/photo-1544197150-b99a580bb7a8?auto=format&fit=crop&w=1200&q=80',
                'demo_url' => 'https://github.com/kevinpratama/kawaldns/releases',
                'github_url' => 'https://github.com/kevinpratama/kawaldns',
                'tech_stacks' => ['Go', 'DNS Protocol', 'BubbleTea', 'Lipgloss TUI'],
                'base_upvotes' => 370,
                'base_downvotes' => 7,
                'views_count' => 1820,
                'is_featured' => false,
                'created_at' => now()->subDays(6),
                'comments' => [
                    [
                        'username' => 'fajar_cli',
                        'content' => 'Ini tool yang saya cari-cari tiap kali migrasi DNS server klien! TUI tampilan tabelnya rapi banget pakai Lipgloss.',
                        'upvotes' => 11,
                        'created_at' => now()->subDays(5)->subHours(15),
                    ],
                    [
                        'username' => 'kevin_security',
                        'content' => '@fajar_cli Senang kalau bermanfaat mas Fajar! Resolver ISP lokal kita memang terkenal lumayan agresif TTL caching-nya haha.',
                        'upvotes' => 8,
                        'created_at' => now()->subDays(5)->subHours(10),
                    ],
                ],
            ],
            [
                'username' => 'dwi_uiux',
                'category' => 'ui-components',
                'title' => 'Nusantara-Icons: 400+ SVG Icon Budaya & Kehidupan Lokal',
                'tagline' => 'Icon pack open-source format SVG, React, & Vue bertema elemen khas Indonesia dan Asia Tenggara.',
                'description' => "Icon library buatan tangan (handcrafted) berisi 400+ icon vector pixel-perfect untuk website dan aplikasi:\n- Transportasi: Angkot, bajaj, becak, kapal ferry, ojek online\n- Kuliner: Gerobak martabak, rendang, nasi tumpeng, cangkir kopi tubruk\n- Budaya & Tradisi: Wayang, keris, motif batik, candi\n- Komersial: Rupiah koin, nota kasir, toko kelontong\n\nTersedia dalam 3 gaya: Outline (2px), Solid, dan Duotone.",
                'thumbnail' => 'https://images.unsplash.com/photo-1507238691740-187a5b1d37b8?auto=format&fit=crop&w=1200&q=80',
                'demo_url' => 'https://nusantara-icons.example.com',
                'github_url' => 'https://github.com/dwilestari/nusantara-icons',
                'tech_stacks' => ['SVG', 'React', 'Vue 3', 'TailwindCSS', 'Figma'],
                'base_upvotes' => 580,
                'base_downvotes' => 11,
                'views_count' => 3650,
                'is_featured' => true,
                'created_at' => now()->subDays(7),
                'comments' => [
                    [
                        'username' => 'dimas_aditya',
                        'content' => 'Langsung tak pasang di landing page proyek pribadi mbak Dwi. Icon gerobak martabak sama bajaj-nya estetik parah!',
                        'upvotes' => 16,
                        'created_at' => now()->subDays(6)->subHours(14),
                    ],
                    [
                        'username' => 'dwi_uiux',
                        'content' => '@dimas_aditya Wah asik, makasih mas Dimas! Kalau ada request icon khas daerah tertentu mention aja ya di GitHub issue.',
                        'upvotes' => 9,
                        'created_at' => now()->subDays(6)->subHours(10),
                    ],
                    [
                        'username' => 'budi_santoso',
                        'content' => 'Keren banget! Ada rencana buat bikin Blade component version-nya buat anak-anak Laravel?',
                        'upvotes' => 8,
                        'created_at' => now()->subDays(6)->subHours(4),
                    ],
                    [
                        'username' => 'dwi_uiux',
                        'content' => '@budi_santoso Sedang disiapkan mas Budi, nanti formatnya mirip Blade Icons library!',
                        'upvotes' => 7,
                        'created_at' => now()->subDays(6)->subHours(1),
                    ],
                ],
            ],
            [
                'username' => 'rian_dev',
                'category' => 'ai-ml',
                'title' => 'ResepBot: Asisten Masak Berbasis AI Bahan Kulkas',
                'tagline' => 'Input bahan masakan sisa di kulkas, AI meracik resep masakan rumahan lezat lengkap dengan panduan kalori.',
                'description' => "Bingung mau masak apa dengan sisa tempe, telur, dan kecap di kulkas? ResepBot menganalisis kombinasi bahan yang kamu miliki, lalu menghasilkan resep masakan rumahan Indonesia yang lezat dan realistis tanpa perlu belanja bahan aneh-aneh.\n\nDilengkapi estimasi biaya per porsi dan substitusi bumbu jika ada yang kurang.",
                'thumbnail' => 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=1200&q=80',
                'demo_url' => 'https://resepbot.example.com',
                'github_url' => 'https://github.com/rianhidayat/resep-bot-ai',
                'tech_stacks' => ['Next.js 15', 'Claude 3.5 API', 'TailwindCSS', 'Vercel AI SDK'],
                'base_upvotes' => 432,
                'base_downvotes' => 8,
                'views_count' => 2890,
                'is_featured' => false,
                'created_at' => now()->subDays(8),
                'comments' => [
                    [
                        'username' => 'alif_pratama',
                        'content' => 'Prompt engineering-nya rapi nih, output resepnya beneran masakan rumahan yang masuk akal dan gak halusinasi bumbu.',
                        'upvotes' => 11,
                        'created_at' => now()->subDays(7)->subHours(18),
                    ],
                    [
                        'username' => 'rian_dev',
                        'content' => '@alif_pratama Makasih mas Alif! Dikasih system prompt ketat dengan referensi buku resep nusantara biar takaran garam dan micinnya pas haha.',
                        'upvotes' => 8,
                        'created_at' => now()->subDays(7)->subHours(12),
                    ],
                ],
            ],
            [
                'username' => 'arya_gamedev',
                'category' => 'open-source',
                'title' => 'Wayang-ECS: Framework Entity Component System Game Web',
                'tagline' => 'Framework ECS mikro tanpa dependensi eksternal, berukuran kurang dari 4KB gzipped untuk game HTML5.',
                'description' => 'Engine ECS super ringan yang dioptimalkan untuk game web canvas 2D. Menggunakan TypedArrays untuk contiguous memory layout, spatial hash grid bawaan untuk deteksi tabrakan super cepat, dan arsitektur zero-allocation di game loop.',
                'thumbnail' => 'https://images.unsplash.com/photo-1511512578047-dfb367046420?auto=format&fit=crop&w=1200&q=80',
                'demo_url' => 'https://wayang-ecs.example.com',
                'github_url' => 'https://github.com/aryawicaksono/wayang-ecs',
                'tech_stacks' => ['TypeScript', 'WebGL', 'Canvas API', 'Vitest'],
                'base_upvotes' => 340,
                'base_downvotes' => 5,
                'views_count' => 1710,
                'is_featured' => false,
                'created_at' => now()->subDays(9),
                'comments' => [
                    [
                        'username' => 'dimas_aditya',
                        'content' => 'Ukuran 4KB gzipped tapi dapet spatial grid itu gokil sih mas Arya. Memory footprint-nya stabil banget pas benchmark 5.000 entities.',
                        'upvotes' => 13,
                        'created_at' => now()->subDays(8)->subHours(15),
                    ],
                    [
                        'username' => 'arya_gamedev',
                        'content' => '@dimas_aditya Yup mas Dimas, kuncinya hindari garbage collection spikes di tengah-tengah render loop.',
                        'upvotes' => 7,
                        'created_at' => now()->subDays(8)->subHours(10),
                    ],
                ],
            ],
        ];

        // 4. Populate Projects, Votes & Comments
        foreach ($projectsData as $item) {
            $author = $users[$item['username']] ?? null;
            $cat = $categories[$item['category']] ?? null;

            if (! $author || ! $cat) {
                continue;
            }

            $project = Project::updateOrCreate(
                ['slug' => Str::slug($item['title'])],
                [
                    'user_id' => $author->id,
                    'category_id' => $cat->id,
                    'title' => $item['title'],
                    'slug' => Str::slug($item['title']),
                    'tagline' => $item['tagline'],
                    'description' => $item['description'],
                    'thumbnail' => $item['thumbnail'],
                    'demo_url' => $item['demo_url'],
                    'github_url' => $item['github_url'],
                    'prototype_url' => $item['prototype_url'] ?? null,
                    'tech_stacks' => $item['tech_stacks'],
                    'upvotes_count' => $item['base_upvotes'],
                    'downvotes_count' => $item['base_downvotes'],
                    'score' => $item['base_upvotes'] - $item['base_downvotes'],
                    'comments_count' => count($item['comments'] ?? []),
                    'views_count' => $item['views_count'],
                    'is_featured' => $item['is_featured'],
                    'created_at' => $item['created_at'],
                ]
            );

            // Seed real ProjectVotes from developer peers
            // Randomly select 4-8 other developers to have upvoted this project
            $otherDevKeys = array_diff(array_keys($users), [$author->username]);
            shuffle($otherDevKeys);
            $voterKeys = array_slice($otherDevKeys, 0, rand(4, 7));

            foreach ($voterKeys as $voterKey) {
                $voter = $users[$voterKey];
                ProjectVote::updateOrCreate(
                    [
                        'project_id' => $project->id,
                        'user_id' => $voter->id,
                    ],
                    [
                        'type' => 'up',
                        'ip_address' => '127.0.0.1',
                    ]
                );
            }

            // Seed Comments
            if (! empty($item['comments'])) {
                foreach ($item['comments'] as $commData) {
                    $commUser = $users[$commData['username']] ?? null;
                    if ($commUser) {
                        ProjectComment::updateOrCreate(
                            [
                                'project_id' => $project->id,
                                'user_id' => $commUser->id,
                                'content' => $commData['content'],
                            ],
                            [
                                'upvotes_count' => $commData['upvotes'],
                                'created_at' => $commData['created_at'],
                            ]
                        );
                    }
                }
            }
        }

        // 5. Recalculate Category and Project counts
        foreach (Category::all() as $category) {
            $category->update(['projects_count' => $category->projects()->count()]);
        }

        foreach (Project::all() as $p) {
            $p->update([
                'comments_count' => $p->comments()->count(),
            ]);
        }
    }
}
