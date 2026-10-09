<?php

namespace Database\Seeders;

use App\Models\Community;
use App\Models\CommunityMember;
use App\Models\CommunityPost;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CommunitySeeder extends Seeder
{
    /**
     * Seed Indonesian developer communities with members and posts.
     */
    public function run(): void
    {
        $communities = [
            ['name' => 'Web Dev Indonesia', 'desc' => 'Wadah developer web Indonesia: Laravel, Vue, React, dan ekosistem frontend-backend.', 'icon' => 'https://images.unsplash.com/photo-1517180102446-f3ece451e9d8?auto=format&fit=crop&w=200&q=80'],
            ['name' => 'AI & Machine Learning', 'desc' => 'Diskusi seputar AI, LLM, computer vision, dan machine learning untuk developer lokal.', 'icon' => 'https://images.unsplash.com/photo-1677442136019-21780ecad995?auto=format&fit=crop&w=200&q=80'],
            ['name' => 'Mobile Developer ID', 'desc' => 'Komunitas Flutter, React Native, Kotlin, dan Swift developer Indonesia.', 'icon' => 'https://images.unsplash.com/photo-1512941937669-90a1b58e7e9c?auto=format&fit=crop&w=200&q=80'],
            ['name' => 'Open Source Nusantara', 'desc' => 'Berkontribusi ke open-source & membangun library yang berguna bagi komunitas.', 'icon' => 'https://images.unsplash.com/photo-1618401471353-b98afee0b2eb?auto=format&fit=crop&w=200&q=80'],
            ['name' => 'DevOps & Cloud', 'desc' => 'Docker, Kubernetes, CI/CD, dan arsitektur cloud untuk tim engineering.', 'icon' => 'https://images.unsplash.com/photo-1667372393119-3d4c48d07fc9?auto=format&fit=crop&w=200&q=80'],
            ['name' => 'Game Dev Indonesia', 'desc' => 'Indie game developer: Godot, Unity, Phaser, dan pixel art nusantara.', 'icon' => 'https://images.unsplash.com/photo-1550745165-9bc0b252726f?auto=format&fit=crop&w=200&q=80'],
        ];

        $postPool = [
            'Halo semuanya! Baru join, senang bisa ada di sini 👋',
            'Ada rekomendasi tool buat optimasi workflow coding gak nih?',
            'Berhasil deploy project pertama ke production minggu ini, terima kasih sarannya semua!',
            'Diskusi: monolith vs microservices, kalian pilih mana dan kenapa?',
            'Sharing: akhirnya paham konsep ini setelah sekian lama bingung 😅',
            'Ada yang udah coba teknologi terbaru ini? Worth it gak?',
            'Butuh masukan soal arsitektur backend untuk project skala menengah.',
            'Tips: selalu tulis test dulu sebelum refactor, ngirit waktu banget.',
            'Meetup online kapan nih? Sudah kangen diskusi bareng.',
            'Baru rilis versi terbaru project saya, feedback-nya ditunggu ya!',
            'Menurutku dokumentasi yang rapi itu investasi, bukan beban. Setuju?',
            'Pertanyaan pemula: kapan sebaiknya pakai state management di frontend?',
            'Akhirnya bisa ngerti Docker multi-stage build, hemat ukuran image 70%.',
            'Rekomendasi baca apa nih buat naikin skill system design?',
            'Code review di tim kalian pakai tools apa? Butuh saran.',
            'Kadang bug paling susah itu typo satu huruf 😂 kalian pernah?',
            'Sharing: pakai queue bikin response API turun dari 2s ke 200ms.',
            'Ada grup belajar bareng untuk persiapan interview teknis?',
            'Best practice nge-namain variable biar gampang dibaca tim gimana?',
            'AI code assistant ngubah cara kerja ku banget, kalian gimana?',
        ];

        $announcePool = [
            '📢 Pengumuman: Meetup online komunitas kita Sabtu depan jam 8 malam. Jangan lupa hadir!',
            '📢 Panduan komunitas: mohon jaga diskusi tetap sopan & relevan. Terima kasih.',
            '📢 Komunitas kita resmi mencapai 20+ anggota. Terima kasih semua!',
            '📢 Akan ada sesi sharing "belajar X dari nol" pekan ini. Stay tuned!',
        ];

        $users = User::where('email', 'like', 'user%@nampungyuk.test')->get();
        if ($users->isEmpty()) {
            $users = User::take(20)->get();
        }

        foreach ($communities as $i => $data) {
            $owner = $users[$i % max(1, $users->count())];
            $slug = Str::slug($data['name']);

            $community = Community::updateOrCreate(
                ['slug' => $slug],
                [
                    'owner_id' => $owner->id,
                    'name' => $data['name'],
                    'description' => $data['desc'],
                    'icon' => $data['icon'],
                    'visibility' => 'public',
                    'members_count' => 0,
                    'is_featured' => $i < 3,
                ]
            );

            // Owner membership
            CommunityMember::updateOrCreate(
                ['community_id' => $community->id, 'user_id' => $owner->id],
                ['role' => 'owner']
            );

            // Random members
            $memberCount = rand(8, 20);
            $members = $users->shuffle()->take($memberCount);
            foreach ($members as $m) {
                if ($m->id === $owner->id) {
                    continue;
                }
                CommunityMember::updateOrCreate(
                    ['community_id' => $community->id, 'user_id' => $m->id],
                    ['role' => 'member']
                );
            }

            $community->update(['members_count' => $community->members()->count()]);

            // Posts — more volume so the feed feels alive
            $postCount = rand(8, 16);
            for ($p = 0; $p < $postCount; $p++) {
                $author = $community->memberUsers()->inRandomOrder()->first() ?? $owner;
                $type = ['discussion', 'discussion', 'discussion', 'question', 'question', 'announcement'][array_rand([0, 1, 2, 3, 4, 5])];
                $isQuestion = $type === 'question';
                $isAnnouncement = $type === 'announcement';
                $answered = $isQuestion && rand(0, 1) === 1;

                CommunityPost::create([
                    'community_id' => $community->id,
                    'user_id' => $isAnnouncement ? $owner->id : $author->id,
                    'type' => $type,
                    'content' => $isAnnouncement
                        ? $announcePool[array_rand($announcePool)]
                        : $postPool[array_rand($postPool)],
                    'upvotes_count' => rand(2, 40),
                    'downvotes_count' => rand(0, 3),
                    'score' => rand(2, 38),
                    'comments_count' => 0,
                    'is_pinned' => $isAnnouncement && rand(0, 1) === 1,
                    'is_answered' => $answered,
                    'created_at' => now()->subDays(rand(0, 14))->subHours(rand(1, 23)),
                ]);
            }

            $community->update(['posts_count' => $community->posts()->count()]);
        }

        // Comments on community posts — multiple per post for lively threads
        $commentPool = [
            'Setuju banget!', 'Wah menarik, makasih sharingnya.', 'Boleh dijelasin lebih detail?',
            'Keren, lanjutkan!', 'Aku juga ngalamin hal serupa.', 'Ini yang aku cari, thanks!',
            'Boleh share referensinya?', 'Nice insight, noted!', 'Menurutku tergantung konteks sih.',
            'Langsung tak coba ya, makasih!',
        ];
        foreach (CommunityPost::inRandomOrder()->take(50)->get() as $post) {
            $take = rand(1, 4);
            for ($c = 0; $c < $take; $c++) {
                $commenter = $users->random();
                \App\Models\CommunityPostComment::create([
                    'community_post_id' => $post->id,
                    'user_id' => $commenter->id,
                    'content' => $commentPool[array_rand($commentPool)],
                    'created_at' => $post->created_at->addHours(rand(1, 10)),
                ]);
                $post->increment('comments_count');
            }
        }
    }
}
