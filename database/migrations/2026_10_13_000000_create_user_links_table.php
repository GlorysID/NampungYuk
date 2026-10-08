<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('label');       // keterangan, e.g. "Portfolio", "LinkedIn"
            $table->string('url');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['user_id', 'sort_order']);
        });

        // Migrate existing github_url / website_url into the new links table.
        $now = now();
        DB::table('users')->select('id', 'github_url', 'website_url')->orderBy('id')->chunk(200, function ($users) use ($now) {
            $rows = [];
            foreach ($users as $u) {
                $order = 0;
                if (! empty($u->github_url)) {
                    $rows[] = [
                        'user_id' => $u->id,
                        'label' => 'GitHub',
                        'url' => $u->github_url,
                        'sort_order' => $order++,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
                if (! empty($u->website_url)) {
                    $rows[] = [
                        'user_id' => $u->id,
                        'label' => 'Website',
                        'url' => $u->website_url,
                        'sort_order' => $order++,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }
            if (! empty($rows)) {
                DB::table('user_links')->insert($rows);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_links');
    }
};
