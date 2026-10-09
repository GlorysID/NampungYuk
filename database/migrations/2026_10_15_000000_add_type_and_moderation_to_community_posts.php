<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('community_posts', function (Blueprint $table) {
            $table->string('type', 20)->default('discussion')->after('user_id')->index(); // discussion, question, announcement
            $table->boolean('is_pinned')->default(false)->after('comments_count');
            $table->boolean('is_answered')->default(false)->after('is_pinned');
            $table->foreignId('answered_comment_id')->nullable()->after('is_answered')
                ->constrained('community_post_comments')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('community_posts', function (Blueprint $table) {
            $table->dropForeign(['answered_comment_id']);
            $table->dropColumn(['type', 'is_pinned', 'is_answered', 'answered_comment_id']);
        });
    }
};
