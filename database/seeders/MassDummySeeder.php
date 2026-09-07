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

class MassDummySeeder extends Seeder
{
    public function run(): void
    {
        // 1. Real Portrait Photos for selected profiles
        $realPhotos = [
            'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=400&q=80',
            'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=400&q=80',
            'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&w=400&q=80',
            'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=400&q=80',
            'https://images.unsplash.com/photo-1522075469751-3a6694fb2f61?auto=format&fit=crop&w=400&q=80',
            'https://images.unsplash.com/photo-1517841905240-472988babdf9?auto=format&fit=crop&w=400&q=80',
            'https://images.unsplash.com/photo-1539571696357-5a69c17a67c6?auto=format&fit=crop&w=400&q=80',
            'https://images.unsplash.com/photo-1524504388940-b1c1722653e1?auto=format&fit=crop&w=400&q=80',
            'https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?auto=format&fit=crop&w=400&q=80',
            'https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=400&q=80',
            'https://images.unsplash.com/photo-1570295999919-56ceb5ecca61?auto=format&fit=crop&w=400&q=80',
            'https://images.unsplash.com/photo-1580489944761-15a19d654956?auto=format&fit=crop&w=400&q=80',
            'https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?auto=format&fit=crop&w=400&q=80',
            'https://images.unsplash.com/photo-1531746020798-e6953c6e8e04?auto=format&fit=crop&w=400&q=80',
            'https://images.unsplash.com/photo-1501196354995-cbb51c65aaea?auto=format&fit=crop&w=400&q=80',
            'https://images.unsplash.com/photo-1548142813-c348350df52b?auto=format&fit=crop&w=400&q=80',
            'https://images.unsplash.com/photo-1560250097-0b93528c311a?auto=format&fit=crop&w=400&q=80',
            'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?auto=format&fit=crop&w=400&q=80',
            'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?auto=format&fit=crop&w=400&q=80',
            'https://images.unsplash.com/photo-1586297135537-94bc9ba060aa?auto=format&fit=crop&w=400&q=80',
            'https://images.unsplash.com/photo-1527980965255-d3b416303d12?auto=format&fit=crop&w=400&q=80',
            'https://images.unsplash.com/photo-1492562080023-ab3db95bfbce?auto=format&fit=crop&w=400&q=80',
            'https://images.unsplash.com/photo-1517486808906-6ca8b3f04846?auto=format&fit=crop&w=400&q=80',
            'https://images.unsplash.com/photo-1534751516642-a1714f5a596a?auto=format&fit=crop&w=400&q=80',
            'https://images.unsplash.com/photo-1508214751196-bcfd4ca60f91?auto=format&fit=crop&w=400&q=80',
            'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?auto=format&fit=crop&w=400&q=80',
            'https://images.unsplash.com/photo-1519345182560-3f2917c472ef?auto=format&fit=crop&w=400&q=80',
            'https://images.unsplash.com/photo-1499952127939-9bbf5af6c51c?auto=format&fit=crop&w=400&q=80',
            'https://images.unsplash.com/photo-1520813792240-56fc4a3765a7?auto=format&fit=crop&w=400&q=80',
            'https://images.unsplash.com/photo-1543610892-0b1f7e6d8ac1?auto=format&fit=crop&w=400&q=80',
            'https://images.unsplash.com/photo-1566492031773-4f4e44671857?auto=format&fit=crop&w=400&q=80',
            'https://images.unsplash.com/photo-1584999734482-0361aecad844?auto=format&fit=crop&w=400&q=80',
            'https://images.unsplash.com/photo-1542909168-82c3e7fdca5c?auto=format&fit=crop&w=400&q=80',
            'https://images.unsplash.com/photo-1567532939604-b6b5b0db2604?auto=format&fit=crop&w=400&q=80',
            'https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?auto=format&fit=crop&w=400&q=80',
        ];

        // 2. Name generation components
        $firstNamesMale = [
            'Reza', 'Bayu', 'Hendra', 'Aditya', 'Gilang', 'Ilham', 'Teguh', 'Farhan', 'Bagas', 'Angga',
            'Rizky', 'Faisal', 'Eko', 'Aris', 'Joko', 'Pratama', 'Surya', 'Danang', 'Wahyu', 'Taufik',
            'Galih', 'Irfan', 'Randy', 'Andre', 'Rio', 'Vicky', 'Yoga', 'Zulfikar', 'Haris', 'Dedi',
            'Agus', 'Firmansyah', 'Aji', 'Guntur', 'Pandu', 'Satria', 'Bobby', 'Dimas', 'Akbar', 'Aldi',
            'Kevin', 'Samuel', 'Jonathan', 'Daniel', 'David', 'Indra', 'Rahmat', 'Tommy', 'Arif', 'Yusuf',
        ];

        $firstNamesFemale = [
            'Anisa', 'Putri', 'Citra', 'Maya', 'Nadia', 'Dian', 'Tari', 'Rina', 'Dewi', 'Salsabila',
            'Melati', 'Fitri', 'Wulandari', 'Indah', 'Ratna', 'Aulia', 'Intan', 'Tiara', 'Bella', 'Sabrina',
            'Widya', 'Novita', 'Gita', 'Febri', 'Lestari', 'Kartika', 'Ayu', 'Hana', 'Sarah', 'Jessica',
            'Amanda', 'Sherly', 'Vania', 'Clarissa', 'Michelle', 'Stefani', 'Rachel', 'Chelsea', 'Nabila', 'Tasya',
            'Shafa', 'Farah', 'Zahra', 'Salma', 'Jasmine', 'Alika', 'Nadira', 'Syifa', 'Annisa', 'Mutia',
        ];

        $lastNames = [
            'Saputra', 'Pratama', 'Wibowo', 'Kusuma', 'Wijaya', 'Santoso', 'Siregar', 'Nasution', 'Hidayat', 'Permana',
            'Nugroho', 'Setiawan', 'Utomo', 'Suryono', 'Halim', 'Gunawan', 'Subagyo', 'Prasetyo', 'Hartono', 'Suhendra',
            'Kurniawan', 'Ramadhan', 'Pangestu', 'Simanjuntak', 'Panjaitan', 'Lubis', 'Marpaung', 'Sitorus', 'Hutapea', 'Ginting',
            'Tarigan', 'Sinaga', 'Siahaan', 'Pasaribu', 'Situmorang', 'Harahap', 'Daulay', 'Batubara', 'Tambunan', 'Manurung',
            'Manalu', 'Sinambela', 'Tobing', 'Tampubolon', 'Samosir', 'Saragih', 'Damanik', 'Purba', 'Kurnia', 'Subekti',
        ];

        $bios = [
            'Fullstack Laravel & Vue developer berbasis di Bandung. Suka ngulik clean code dan micro-frameworks.',
            'Mahasiswa Teknik Informatika tingkat akhir. Sedang fokus belajar React, TypeScript, dan Next.js.',
            'Junior Go developer yang hobi bikin microservices berkecepatan tinggi dan CLI tools otomatis.',
            'Frontend enthusiast pecinta Tailwind CSS, animasi 60 FPS, dan UI minimalis fungsional.',
            'Mobile developer (Flutter & Android native). Senang membangun aplikasi yang berdampak sosial langsung.',
            'Backend dev @ Jakarta. Hobi ngulik PostgreSQL, Redis, RabbitMQ, dan containerization Docker.',
            'UI/UX Designer yang mulai merambah ke dunia frontend engineering dan web animation.',
            'Python programmer suka otomasi skrip, web scraping, data scraping, dan basic data science.',
            'DevOps engineer pemula. Sedang mendalami Kubernetes, Terraform, dan CI/CD workflow GitHub Actions.',
            'Software engineer freelance. Siap bantu wujudkan ide website, MVP, dan sistem informasi custom.',
            'Penggemar Rust untuk systems programming. Linux enthusiast dan pemakai Neovim garis keras.',
            'Suka berkontribusi ke open-source packages dan membagikan tips coding di forum komunitas.',
            'Web developer fokus di ekosistem Vue 3, Nuxt, dan Pinia state management.',
            'Tech enthusiast & self-taught coder. Suka eksperimen teknologi web modern dan visualisasi data.',
            'iOS developer enthusiast (Swift & SwiftUI). Suka desain interaksi yang intuitif dan halus.',
            'Web3 & smart contract explorer. Suka belajar Solidity dan arsitektur desentralisasi.',
            'Security researcher & bug hunter pemula. Mengutamakan kode aman dan best practices OWASP.',
            'Fullstack developer dengan pengalaman integrasi payment gateway lokal dan SMS/WhatsApp OTP.',
        ];

        // 3. Create 100 Realistic Dummy Developers
        $newUsers = [];
        $photoIndex = 0;
        $commonPassword = Hash::make('password123');

        for ($i = 1; $i <= 100; $i++) {
            $isFemale = ($i % 2 === 0);
            $firstName = $isFemale
                ? $firstNamesFemale[array_rand($firstNamesFemale)]
                : $firstNamesMale[array_rand($firstNamesMale)];
            $lastName = $lastNames[array_rand($lastNames)];
            $fullName = "{$firstName} {$lastName}";

            $username = Str::slug(Str::lower($firstName).'_'.Str::lower($lastName).'_'.$i, '_');

            // Determine Avatar: ~40% photo profiles, rest use diverse DiceBear avatars
            if ($i % 5 <= 1 && $photoIndex < count($realPhotos)) {
                $avatar = $realPhotos[$photoIndex];
                $photoIndex++;
            } elseif ($i % 3 === 0) {
                $avatar = 'https://api.dicebear.com/7.x/avataaars/svg?seed='.urlencode($fullName);
            } elseif ($i % 3 === 1) {
                $avatar = 'https://api.dicebear.com/7.x/bottts/svg?seed='.urlencode($fullName);
            } else {
                $avatar = 'https://api.dicebear.com/7.x/personas/svg?seed='.urlencode($fullName);
            }

            $user = User::updateOrCreate(
                ['email' => "user{$i}@nampungyuk.test"],
                [
                    'name' => $fullName,
                    'username' => $username,
                    'password' => $commonPassword,
                    'avatar' => $avatar,
                    'bio' => $bios[array_rand($bios)],
                    'github_url' => "https://github.com/{$username}",
                    'reputation_points' => rand(15, 680),
                    'created_at' => now()->subDays(rand(1, 90)),
                ]
            );

            $newUsers[] = $user;
        }

        // 4. Preload Categories
        $categories = Category::all()->keyBy('slug');

        // 5. Rich, Varied Project Templates
        $projectTemplates = [
            // Web Apps
            [
                'cat' => 'web-app',
                'title' => 'AbsensiQR: Presensi Geofencing & QR Dinamis Kampus',
                'tagline' => 'Sistem absensi mahasiswa anti-titip absen dengan QR code yang berganti tiap 15 detik dan validasi GPS radius 50m.',
                'desc' => 'Aplikasi web presensi kuliah yang memecahkan masalah titip absen. Dilengkapi validasi radius GPS kampus secara real-time dan QR code dinamis yang diperbarui via WebSocket setiap 15 detik.',
                'stacks' => ['Laravel 12', 'Livewire 3', 'Pusher', 'Leaflet.js', 'TailwindCSS'],
                'thumb' => 'https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?auto=format&fit=crop&w=1000&q=80',
            ],
            [
                'cat' => 'web-app',
                'title' => 'KatalogToko: Website Etalase Produk WhatsApp UMKM',
                'tagline' => 'Platform katalog online instan untuk toko kelontong & UMKM dengan checkout langsung ke format chat WhatsApp rapi.',
                'desc' => 'Membantu penjual online menyusun katalog produk digital tanpa biaya langganan bulanan mahal. Keranjang belanja otomatis memformat pesanan rapi lengkap dengan ongkir dan rincian ke nomor WhatsApp toko.',
                'stacks' => ['Vue 3', 'TailwindCSS', 'Pinia', 'Vite', 'LocalStorage'],
                'thumb' => 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&w=1000&q=80',
            ],
            [
                'cat' => 'web-app',
                'title' => 'SimpelInvois: Generator Faktur & Tagihan PDF Otomatis',
                'tagline' => 'Web app pembuatan invoice profesional gratis untuk freelancer lokal dengan format rupiah dan QRIS statis.',
                'desc' => 'Dirancang untuk freelancer dan agensi kreatif. Masukkan line items pekerjaan, kalkulasi otomatis diskon/PPN, dan download langsung PDF beresolusi tinggi siap kirim ke klien dalam hitungan detik.',
                'stacks' => ['Next.js 15', 'TypeScript', 'TailwindCSS', 'jspdf', 'Lucide Icons'],
                'thumb' => 'https://images.unsplash.com/photo-1554224155-8d04cb21cd6c?auto=format&fit=crop&w=1000&q=80',
            ],
            [
                'cat' => 'web-app',
                'title' => 'CariKost: Peta Pencarian Kost Mahasiswa Tanpa Perantara',
                'tagline' => 'Agregator peta kost langsung dari pemilik dengan filter fasilitas WiFi, AC, kamar mandi dalam, dan jam malam bebas.',
                'desc' => 'Platform berbasis peta interaktif yang menghubungkan mahasiswa baru dengan pemilik rumah kost langsung tanpa markup agen perantara.',
                'stacks' => ['React', 'Leaflet', 'TailwindCSS', 'Supabase', 'Node.js'],
                'thumb' => 'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?auto=format&fit=crop&w=1000&q=80',
            ],
            [
                'cat' => 'web-app',
                'title' => 'JadwalSholat-API: API Waktu Sholat Presisi Koordinat GPS',
                'tagline' => 'Layanan API gratis dan open-source jadwal sholat dengan metode hisab Kemenag RI dan koreksi ketinggian tempat.',
                'desc' => 'Menyediakan endpoint REST API berkecepatan tinggi dengan response time rata-rata di bawah 30ms dan caching CDN Cloudflare.',
                'stacks' => ['Go', 'Fiber', 'Redis', 'PostgreSQL', 'Docker'],
                'thumb' => 'https://images.unsplash.com/photo-1564769625905-50e93615e769?auto=format&fit=crop&w=1000&q=80',
            ],
            [
                'cat' => 'web-app',
                'title' => 'ReviewJurusan: Platform Ulasan Kampus & Mata Kuliah',
                'tagline' => 'Wadah berbagi pengalaman kuliah jujur dan anonim dari mahasiswa dan alumni se-Indonesia.',
                'desc' => 'Membantu calon mahasiswa menentukan pilihan jurusan kuliah dengan membaca review realistik seputar beban tugas, dosen pengampu, dan prospek karir nyata.',
                'stacks' => ['Laravel 12', 'Inertia.js', 'Vue 3', 'PostgreSQL'],
                'thumb' => 'https://images.unsplash.com/photo-1523240795612-9a054b0db644?auto=format&fit=crop&w=1000&q=80',
            ],
            [
                'cat' => 'web-app',
                'title' => 'KalkulatorPajak: Hitung Tarif Efektif PPh 21 TER 2026',
                'tagline' => 'Kalkulator simulasi potongan pajak penghasilan karyawan bulanan dan tahunan sesuai PMK terbaru.',
                'desc' => 'Tool interaktif untuk memverifikasi slip gaji bulanan agar tidak bingung dengan skema TER A, B, C dan penyesuaian di masa pajak Desember.',
                'stacks' => ['Alpine.js', 'TailwindCSS', 'HTML5', 'Vite'],
                'thumb' => 'https://images.unsplash.com/photo-1450133064473-71024230f91b?auto=format&fit=crop&w=1000&q=80',
            ],
            [
                'cat' => 'web-app',
                'title' => 'KantinDigital: Sistem Pre-Order Makanan Kantin Bebas Antre',
                'tagline' => 'Web app pemesanan makanan kantin sekolah/kantor agar pesanan siap saat jam istirahat tiba.',
                'desc' => 'Pengguna memesan 30 menit sebelum jam istirahat, dapur menerima pesanan di dashboard tablet, dan notifikasi berbunyi saat makanan siap diambil.',
                'stacks' => ['Next.js', 'Prisma', 'PostgreSQL', 'TailwindCSS'],
                'thumb' => 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?auto=format&fit=crop&w=1000&q=80',
            ],

            // Mobile Apps
            [
                'cat' => 'mobile-app',
                'title' => 'TemanTani: Deteksi Penyakit Daun Padi via Kamera HP',
                'tagline' => 'Aplikasi mobile Flutter dengan model TensorFlow Lite on-device untuk mendeteksi wereng dan blas padi.',
                'desc' => 'Bisa digunakan di tengah sawah tanpa internet sama sekali! Cukup arahkan kamera ke daun padi, model AI lokal memprediksi diagnosis penyakit dan merekomendasikan takaran pupuk/obat yang tepat.',
                'stacks' => ['Flutter', 'TensorFlow Lite', 'Dart', 'Provider'],
                'thumb' => 'https://images.unsplash.com/photo-1586771107445-d3ca888129ff?auto=format&fit=crop&w=1000&q=80',
            ],
            [
                'cat' => 'mobile-app',
                'title' => 'CatatMeter: Scanner Angka Meteran PDAM Berbasis OCR',
                'tagline' => 'Aplikasi pembaca angka meteran air PDAM mandiri untuk warga mencegah estimasi tagihan membengkak.',
                'desc' => 'Menggunakan ML Kit Text Recognition untuk memindai angka meteran air analog secara instan dan mencatat riwayat pemakaian kubikasi bulanan ke grafik.',
                'stacks' => ['Kotlin', 'Android Jetpack', 'ML Kit OCR', 'Room DB'],
                'thumb' => 'https://images.unsplash.com/photo-1585829365295-ab7cd400c167?auto=format&fit=crop&w=1000&q=80',
            ],
            [
                'cat' => 'mobile-app',
                'title' => 'BelajarAksara: Flashcard & Kuis Aksara Tradisional Nusantara',
                'tagline' => 'Aplikasi mobile edukasi interaktif untuk mengenalkan aksara Jawa (Hanacaraka), Sunda, dan Bali.',
                'desc' => 'Dilengkapi fitur tracing jari untuk melatih goresan aksara tradisional di layar smartphone serta kuis tebak kata dengan audio pelafalan asli.',
                'stacks' => ['React Native', 'Expo', 'Reanimated', 'TypeScript'],
                'thumb' => 'https://images.unsplash.com/photo-1503676260728-1c00da094a0b?auto=format&fit=crop&w=1000&q=80',
            ],
            [
                'cat' => 'mobile-app',
                'title' => 'OjekKampus: Transportasi Antar-Jemput Komunitas Mahasiswa',
                'tagline' => 'Aplikasi ride sharing khusus sesama mahasiswa dalam kawasan kampus dengan tarif flat ramah kantong.',
                'desc' => 'Verifikasi ketat hanya dengan email kampus (.ac.id) untuk keamanan maksimal, rute khusus jalan tikus antar fakultas dan asrama.',
                'stacks' => ['Flutter', 'Firebase', 'Google Maps SDK', 'Bloc'],
                'thumb' => 'https://images.unsplash.com/photo-1558981806-ec527fa84c39?auto=format&fit=crop&w=1000&q=80',
            ],
            [
                'cat' => 'mobile-app',
                'title' => 'PomodoroDev: Timer Fokus Produktivitas Developer',
                'tagline' => 'Timer Pomodoro minimalis dengan integrasi status Slack/Discord dan background ambient lofi music.',
                'desc' => "Membantu programmer menjaga fokus 25 menit sesi coding tanpa terdistraksi media sosial, otomatis mengubah status chat kerja jadi 'In the zone'.",
                'stacks' => ['Swift', 'SwiftUI', 'CoreData', 'AVAudioPlayer'],
                'thumb' => 'https://images.unsplash.com/photo-1434030216411-0b793f4b4173?auto=format&fit=crop&w=1000&q=80',
            ],

            // CLI Tools
            [
                'cat' => 'cli-tools',
                'title' => 'IndoKodepos-CLI: Pencari Kode Pos & Kelurahan Kilat',
                'tagline' => 'Tool terminal super cepat mencari kode pos, kecamatan, dan kabupaten se-Indonesia tanpa buka browser.',
                'desc' => 'Data terindeks dalam database SQLite lokal berukuran 4MB. Query hasil pencarian selesai dalam 2 milidetik dengan output tabel interaktif TUI.',
                'stacks' => ['Rust', 'Clap CLI', 'SQLite', 'Ratatui'],
                'thumb' => 'https://images.unsplash.com/photo-1526374965328-7f61d4dc18c5?auto=format&fit=crop&w=1000&q=80',
            ],
            [
                'cat' => 'cli-tools',
                'title' => 'GitSync: Sinkronisasi Multi-Remote Sekali Perintah',
                'tagline' => 'CLI utility untuk push commit serentak ke GitHub, GitLab, dan Codeberg dengan retry otomatis.',
                'desc' => "Solusi bagi developer yang mengelola mirror repository cadangan tanpa perlu mengetik berulang 'git push origin' dan 'git push backup'.",
                'stacks' => ['Go', 'Cobra', 'Git Exec'],
                'thumb' => 'https://images.unsplash.com/photo-1618401471353-b98afee0b2eb?auto=format&fit=crop&w=1000&q=80',
            ],
            [
                'cat' => 'cli-tools',
                'title' => 'EnvGuard: Linter Pencegah Kebocoran API Key ke Git',
                'tagline' => 'Pre-commit hook ringan mendeteksi token OpenAI, AWS secret, dan DB password yang tak sengaja tertulis di kode.',
                'desc' => "Memindai 80+ pattern entropy dan regex rahasia sebelum developer mengetik 'git commit', mencegah insiden fatal repo publik terkena tagihan cloud.",
                'stacks' => ['Python', 'Click', 'Regex', 'Git Hooks'],
                'thumb' => 'https://images.unsplash.com/photo-1563986768609-322da13575f3?auto=format&fit=crop&w=1000&q=80',
            ],
            [
                'cat' => 'cli-tools',
                'title' => 'SpeedNet: Pengukur Latensi ISP Indonesia di Terminal',
                'tagline' => 'Benchmark kecepatan upload, download, dan ping langsung ke CDN lokal Jakarta, Surabaya, & Medan.',
                'desc' => 'Tanpa flash, tanpa iklan pop-up. Menggunakan multithreading HTTP stream langsung ke node server IXP lokal untuk hasil ukur riil.',
                'stacks' => ['Go', 'TUI', 'Concurrency'],
                'thumb' => 'https://images.unsplash.com/photo-1558494949-ef010cbdcc31?auto=format&fit=crop&w=1000&q=80',
            ],

            // Open Source
            [
                'cat' => 'open-source',
                'title' => 'PHP-Validasi-NIK: Parser Nomor Induk Kependudukan',
                'tagline' => 'Package PHP memvalidasi NIK, mengekstrak tanggal lahir, jenis kelamin, dan nama kabupaten/provinsi.',
                'desc' => 'Lengkap dengan unit test 100% code coverage. Membantu sistem registrasi memvalidasi format NIK secara valid di layer backend.',
                'stacks' => ['PHP 8.3', 'Composer', 'Pest PHP'],
                'thumb' => 'https://images.unsplash.com/photo-1555066931-4365d14bab8c?auto=format&fit=crop&w=1000&q=80',
            ],
            [
                'cat' => 'open-source',
                'title' => 'Go-Midtrans: Wrapper SDK Ringan Snap & Core API',
                'tagline' => 'SDK Golang modern tanpa dependensi eksternal untuk integrasi payment gateway Midtrans.',
                'desc' => 'Mendukung pembayaran GoPay, QRIS, Virtual Account BCA/BNI/Mandiri, dan validasi signature notification webhook dengan aman.',
                'stacks' => ['Go', 'REST API', 'HTTP Client'],
                'thumb' => 'https://images.unsplash.com/photo-1559526324-4b87b5e36e44?auto=format&fit=crop&w=1000&q=80',
            ],
            [
                'cat' => 'open-source',
                'title' => 'React-Rupiah-Input: Input Currency Masking Otomatis',
                'tagline' => 'Komponen React ringan pemformat angka rupiah dengan separator titik ribuan dan output number murni.',
                'desc' => 'Sangat ramah pengguna saat input harga barang di form transaksi. Mencegah bug typo pengetikan angka 0 berlebih.',
                'stacks' => ['React', 'TypeScript', 'NPM Package', 'Rollup'],
                'thumb' => 'https://images.unsplash.com/photo-1579621970563-ebec7560ff3e?auto=format&fit=crop&w=1000&q=80',
            ],
            [
                'cat' => 'open-source',
                'title' => 'FastAPI-Indo-Holiday: API Kalender Hari Libur Nasional',
                'tagline' => 'Microservice kalender libur nasional dan cuti bersama Indonesia dengan auto-update dari SKB 3 Menteri.',
                'desc' => 'Memudahkan developer aplikasi HRIS dan payroll menghitung hari kerja efektif karyawan setiap bulan secara otomatis.',
                'stacks' => ['Python', 'FastAPI', 'SQLite', 'Docker'],
                'thumb' => 'https://images.unsplash.com/photo-1506784983877-45594efa4cbe?auto=format&fit=crop&w=1000&q=80',
            ],

            // AI & ML
            [
                'cat' => 'ai-ml',
                'title' => 'ResumeScan.AI: Skrining CV Otomatis Berdasarkan Job Desc',
                'tagline' => 'Tool AI pencocokan kata kunci dan kompetensi CV pelamar kerja terhadap kriteria lowongan pekerjaan.',
                'desc' => 'Memberikan skor kesesuaian persentase serta saran perbaikan keyword agar CV lebih ramah sistem ATS (Applicant Tracking System).',
                'stacks' => ['Python', 'LangChain', 'OpenAI API', 'FastAPI', 'Vue 3'],
                'thumb' => 'https://images.unsplash.com/photo-1586281380349-632531db7ed4?auto=format&fit=crop&w=1000&q=80',
            ],
            [
                'cat' => 'ai-ml',
                'title' => 'OCR-KTP: Ekstraksi Data KTP Menggunakan OpenCV & Tesseract',
                'tagline' => 'Pipeline computer vision untuk crop area KTP otomatis dan mengekstrak NIK, Nama, dan Alamat.',
                'desc' => 'Melakukan perspective transform untuk meluruskan foto KTP yang miring dan adaptive thresholding sebelum OCR dilakukan.',
                'stacks' => ['Python', 'OpenCV', 'Tesseract OCR', 'Flask'],
                'thumb' => 'https://images.unsplash.com/photo-1589254065878-42c9da997008?auto=format&fit=crop&w=1000&q=80',
            ],
            [
                'cat' => 'ai-ml',
                'title' => 'ChatbotPerpus: RAG Agent Pencarian Referensi Skripsi',
                'tagline' => 'Asisten pintar mahasiswa untuk menelusuri ribuan judul skripsi dan abstrak jurnal kampus dalam hitungan detik.',
                'desc' => 'Menggunakan arsitektur RAG (Retrieval-Augmented Generation) dengan vector database Qdrant dan model embedding lokal.',
                'stacks' => ['Python', 'Qdrant', 'Ollama', 'Chainlit'],
                'thumb' => 'https://images.unsplash.com/photo-1457369804613-52c61a468e7d?auto=format&fit=crop&w=1000&q=80',
            ],

            // Game Dev
            [
                'cat' => 'game-dev',
                'title' => 'Pendekar Bayangan: Game Action 2D Petualangan Silat',
                'tagline' => 'Game web aksi platformer bertarung dengan jurus pencak silat dan animasi sprite buatan tangan.',
                'desc' => 'Dibuat dengan Godot Engine dan di-export ke WebAssembly sehingga dapat dimainkan langsung di browser tanpa instalasi.',
                'stacks' => ['Godot 4', 'GDScript', 'WebAssembly', 'Aseprite'],
                'thumb' => 'https://images.unsplash.com/photo-1538481199705-c710c4e965fc?auto=format&fit=crop&w=1000&q=80',
            ],
            [
                'cat' => 'game-dev',
                'title' => 'TebakKota: Game Web Interaktif Peta Kabupaten Indonesia',
                'tagline' => 'Uji pengetahuan geografimu dengan menebak letak 514 kabupaten/kota di peta buta nusantara!',
                'desc' => 'Makin cepat dan akurat tebakanmu mendekati titik koordinat asli, makin tinggi skor yang diperoleh. Lengkap dengan papan skor leaderboard harian.',
                'stacks' => ['Vue 3', 'D3.js', 'GeoJSON', 'TailwindCSS'],
                'thumb' => 'https://images.unsplash.com/photo-1524661135-423995f22d0b?auto=format&fit=crop&w=1000&q=80',
            ],
            [
                'cat' => 'game-dev',
                'title' => 'AngkotRush: Arcade Menyetir Angkot Kejar Setoran',
                'tagline' => 'Game santai bertema sopir angkot mengantar penumpang menghindari lubang jalan dan razia polisi.',
                'desc' => 'Game 2D berbasis HTML5 canvas dengan musik dangdut koplo 8-bit yang seru dan adiktif.',
                'stacks' => ['JavaScript', 'HTML5 Canvas', 'Howler.js'],
                'thumb' => 'https://images.unsplash.com/photo-1511512578047-dfb367046420?auto=format&fit=crop&w=1000&q=80',
            ],

            // UI Components
            [
                'cat' => 'ui-components',
                'title' => 'Glassmorphism-Card: Koleksi Kartu Efek Kaca Tailwind',
                'tagline' => '30+ varian kartu UI modern dengan efek blur backdrop-filter dan gradasi border neon halus.',
                'desc' => 'Cukup copy-paste class Tailwind CSS siap pakai untuk dashboard, pricing table, dan testimonial section.',
                'stacks' => ['Tailwind CSS v4', 'HTML5', 'Alpine.js'],
                'thumb' => 'https://images.unsplash.com/photo-1558655146-d09347e92766?auto=format&fit=crop&w=1000&q=80',
            ],
            [
                'cat' => 'ui-components',
                'title' => 'BentoGrid-Showcase: Template Layout Bento Interaktif',
                'tagline' => 'Komponen grid asimetris responsif ala Apple untuk portofolio developer dan landing page produk.',
                'desc' => 'Tersedia preset CSS Grid 4-kolom, 6-kolom, dan responsive collapse di layar smartphone dengan micro-interaction hover.',
                'stacks' => ['React', 'Framer Motion', 'TailwindCSS'],
                'thumb' => 'https://images.unsplash.com/photo-1507238691740-187a5b1d37b8?auto=format&fit=crop&w=1000&q=80',
            ],
        ];

        // 6. Realistic Comment Snippets for peer discussions
        $commentPool = [
            'Keren banget idenya! Desain UI-nya rapi dan sangat clean.',
            'Izin bintang repo GitHub-nya ya bro, mau tak coba pelajari kodenya.',
            'Wah ini sangat bermanfaat buat kebutuhan project kantor saya. Mantap!',
            'Performa webnya kencang pas tak coba di browser HP. Pakai framework apa buat state-nya?',
            'Bagus banget solusinya, terutama validasi datanya detail.',
            'Next update boleh ditambahin export ke file Excel/CSV gak mas?',
            'UI dark mode-nya enak banget dilihat, kontrasnya pas di mata.',
            'Gokil sih ini, inspiratif banget buat developer lokal. Sukses terus kawan!',
            'Sudah support responsive screen di tablet belum ya?',
            'Animasi transisinya mulus banget, jempolan!',
            'Dokumentasi di README repo-nya juga sangat jelas dan mudah diikuti.',
            'Keren bro, lanjutkan terus karyanya!',
        ];

        // 7. Seed Projects from random dummy users
        // Select ~40 random dummy users to be authors of these projects
        shuffle($newUsers);
        $projectIndex = 0;

        foreach ($projectTemplates as $tmpl) {
            $cat = $categories->get($tmpl['cat']);
            if (! $cat) {
                continue;
            }

            $author = $newUsers[$projectIndex % count($newUsers)];
            $projectIndex++;

            $upvotes = rand(25, 420);
            $downvotes = rand(0, 15);
            $score = $upvotes - $downvotes;
            $slug = Str::slug($tmpl['title']);

            $project = Project::updateOrCreate(
                ['slug' => $slug],
                [
                    'user_id' => $author->id,
                    'category_id' => $cat->id,
                    'title' => $tmpl['title'],
                    'slug' => $slug,
                    'tagline' => $tmpl['tagline'],
                    'description' => $tmpl['desc'],
                    'thumbnail' => $tmpl['thumb'],
                    'demo_url' => 'https://'.Str::slug(explode(':', $tmpl['title'])[0]).'.example.com',
                    'github_url' => 'https://github.com/'.$author->username.'/'.Str::slug(explode(':', $tmpl['title'])[0]),
                    'prototype_url' => ($projectIndex % 3 === 0)
                        ? 'https://www.figma.com/proto/nampungyuk-'.Str::slug(explode(':', $tmpl['title'])[0])
                        : (($projectIndex % 5 === 0) ? 'https://framer.com/share/'.Str::slug(explode(':', $tmpl['title'])[0]) : null),
                    'tech_stacks' => $tmpl['stacks'],
                    'upvotes_count' => $upvotes,
                    'downvotes_count' => $downvotes,
                    'score' => $score,
                    'comments_count' => 0,
                    'views_count' => rand(150, 2900),
                    'is_featured' => ($score > 250),
                    'created_at' => now()->subDays(rand(1, 30))->subHours(rand(1, 23)),
                ]
            );

            // Add 2 to 4 comments from other dummy users
            $randomCommenters = array_slice($newUsers, rand(0, 80), rand(2, 4));
            $commCount = 0;

            foreach ($randomCommenters as $commenter) {
                if ($commenter->id === $author->id) {
                    continue;
                }

                ProjectComment::updateOrCreate(
                    [
                        'project_id' => $project->id,
                        'user_id' => $commenter->id,
                        'content' => $commentPool[array_rand($commentPool)],
                    ],
                    [
                        'upvotes_count' => rand(2, 28),
                        'created_at' => $project->created_at->addHours(rand(1, 12)),
                    ]
                );
                $commCount++;

                // Also add a vote from this commenter
                ProjectVote::updateOrCreate(
                    [
                        'project_id' => $project->id,
                        'user_id' => $commenter->id,
                    ],
                    [
                        'type' => 'up',
                        'ip_address' => '127.0.0.1',
                    ]
                );
            }

            $project->update(['comments_count' => $commCount]);
        }

        // 8. Update Category Counts
        foreach (Category::all() as $cat) {
            $cat->update(['projects_count' => $cat->projects()->count()]);
        }
    }
}
