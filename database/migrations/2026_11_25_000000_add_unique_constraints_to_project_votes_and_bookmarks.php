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
        // 1. Deduplicate project_votes per (project_id, user_id), keeping the latest id.
        if (Schema::hasTable('project_votes')) {
            $duplicateVotes = DB::table('project_votes')
                ->whereNotNull('user_id')
                ->select('project_id', 'user_id', DB::raw('MAX(id) as keep_id'), DB::raw('COUNT(*) as total'))
                ->groupBy('project_id', 'user_id')
                ->having('total', '>', 1)
                ->get();

            foreach ($duplicateVotes as $dup) {
                DB::table('project_votes')
                    ->where('project_id', $dup->project_id)
                    ->where('user_id', $dup->user_id)
                    ->where('id', '<', $dup->keep_id)
                    ->delete();
            }

            Schema::table('project_votes', function (Blueprint $table) {
                $table->unique(['project_id', 'user_id'], 'project_votes_project_user_unique');
            });
        }

        // 2. Deduplicate project_bookmarks per (project_id, user_id), keeping the latest id.
        if (Schema::hasTable('project_bookmarks')) {
            $duplicateBookmarks = DB::table('project_bookmarks')
                ->whereNotNull('user_id')
                ->select('project_id', 'user_id', DB::raw('MAX(id) as keep_id'), DB::raw('COUNT(*) as total'))
                ->groupBy('project_id', 'user_id')
                ->having('total', '>', 1)
                ->get();

            foreach ($duplicateBookmarks as $dup) {
                DB::table('project_bookmarks')
                    ->where('project_id', $dup->project_id)
                    ->where('user_id', $dup->user_id)
                    ->where('id', '<', $dup->keep_id)
                    ->delete();
            }

            Schema::table('project_bookmarks', function (Blueprint $table) {
                $table->unique(['project_id', 'user_id'], 'project_bookmarks_project_user_unique');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('project_votes')) {
            Schema::table('project_votes', function (Blueprint $table) {
                $table->dropUnique('project_votes_project_user_unique');
            });
        }

        if (Schema::hasTable('project_bookmarks')) {
            Schema::table('project_bookmarks', function (Blueprint $table) {
                $table->dropUnique('project_bookmarks_project_user_unique');
            });
        }
    }
};
