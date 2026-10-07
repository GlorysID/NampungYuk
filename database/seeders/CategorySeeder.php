<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Seed the expanded programming category taxonomy.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Web App', 'slug' => 'web-app', 'icon' => 'globe', 'description' => 'Aplikasi web interaktif, SaaS, dashboard, dan e-commerce modern.'],
            ['name' => 'Mobile App', 'slug' => 'mobile-app', 'icon' => 'smartphone', 'description' => 'Aplikasi mobile iOS & Android (Flutter, React Native, Native).'],
            ['name' => 'Backend & API', 'slug' => 'backend-api', 'icon' => 'server', 'description' => 'REST/GraphQL API, microservices, autentikasi, dan arsitektur server.'],
            ['name' => 'Open Source', 'slug' => 'open-source', 'icon' => 'git-branch', 'description' => 'Library, framework extension, plugin, dan package open-source.'],
            ['name' => 'AI & ML', 'slug' => 'ai-ml', 'icon' => 'sparkles', 'description' => 'Model machine learning, AI agents, LLM wrappers, dan computer vision.'],
            ['name' => 'Game Dev', 'slug' => 'game-dev', 'icon' => 'gamepad', 'description' => 'Game web canvas, indie game, Phaser, Godot, dan Unity.'],
            ['name' => 'CLI Tools', 'slug' => 'cli-tools', 'icon' => 'terminal', 'description' => 'Command line interface, terminal user interface (TUI), otomasi & skrip.'],
            ['name' => 'UI / Komponen', 'slug' => 'ui-components', 'icon' => 'palette', 'description' => 'Design system, animasi UI, library komponen Tailwind & CSS.'],
            ['name' => 'DevOps & Cloud', 'slug' => 'devops-cloud', 'icon' => 'cloud', 'description' => 'CI/CD, Docker, Kubernetes, infrastructure as code, dan cloud.'],
            ['name' => 'Database', 'slug' => 'database', 'icon' => 'database', 'description' => 'Desain skema, query optimization, migration, dan data tooling.'],
            ['name' => 'Cybersecurity', 'slug' => 'cybersecurity', 'icon' => 'shield', 'description' => 'Keamanan aplikasi, pentest, audit, dan tooling pertahanan.'],
            ['name' => 'Blockchain & Web3', 'slug' => 'blockchain-web3', 'icon' => 'link', 'description' => 'Smart contract, dApp, DeFi, wallet, dan protokol terdesentralisasi.'],
            ['name' => 'Data Engineering', 'slug' => 'data-engineering', 'icon' => 'chart', 'description' => 'Pipeline data, ETL, analitik, dan visualisasi data berskala besar.'],
            ['name' => 'Extension & Bot', 'slug' => 'extension-bot', 'icon' => 'puzzle', 'description' => 'Browser extension, bot Discord/Telegram, dan integrasi platform.'],
            ['name' => 'Automation & Scripting', 'slug' => 'automation-scripting', 'icon' => 'bolt', 'description' => 'Skrip otomasi, scraper, task scheduler, dan tooling produktivitas.'],
            ['name' => 'Embedded & IoT', 'slug' => 'embedded-iot', 'icon' => 'chip', 'description' => 'Firmware, mikrokontroler, sensor, dan perangkat terhubung.'],
            ['name' => 'Desktop App', 'slug' => 'desktop-app', 'icon' => 'desktop', 'description' => 'Aplikasi desktop cross-platform (Electron, Tauri, Qt, .NET).'],
            ['name' => 'E-commerce', 'slug' => 'e-commerce', 'icon' => 'cart', 'description' => 'Toko online, payment gateway, dan sistem transaksi.'],
            ['name' => 'Education & LMS', 'slug' => 'education-lms', 'icon' => 'book', 'description' => 'Platform pembelajaran, kursus, kuis, dan manajemen kelas.'],
            ['name' => 'Developer Tools', 'slug' => 'developer-tools', 'icon' => 'wrench', 'description' => 'Editor, linter, formatter, dan tooling yang mempermudah ngoding.'],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }
    }
}
