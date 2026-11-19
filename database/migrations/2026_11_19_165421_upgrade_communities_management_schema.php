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
        Schema::table('communities', function (Blueprint $table) {
            $table->string('join_policy', 20)->default('open')->after('visibility');
            $table->string('post_policy', 20)->default('all_members')->after('join_policy');
            $table->boolean('allow_member_comments')->default(true)->after('post_policy');
            $table->boolean('allow_member_images')->default(true)->after('allow_member_comments');
            $table->text('welcome_message')->nullable()->after('rules');
            $table->json('join_questions')->nullable()->after('welcome_message');
            $table->json('rules_items')->nullable()->after('join_questions');
            $table->string('location', 80)->nullable()->after('topic');
            $table->string('website_url', 255)->nullable()->after('location');
        });

        Schema::table('community_members', function (Blueprint $table) {
            $table->timestamp('muted_until')->nullable()->after('role');
            $table->string('mute_reason', 255)->nullable()->after('muted_until');
            $table->index(['community_id', 'role']);
        });

        Schema::table('community_posts', function (Blueprint $table) {
            $table->string('status', 20)->default('approved')->after('type')->index();
            $table->boolean('is_locked')->default(false)->after('is_answered');
            $table->foreignId('reviewed_by')->nullable()->after('answered_comment_id')
                ->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
            $table->string('rejection_reason', 255)->nullable()->after('reviewed_at');
            $table->index(['community_id', 'status', 'created_at']);
        });

        Schema::create('community_join_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('community_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('status', 20)->default('pending');
            $table->json('answers')->nullable();
            $table->text('note')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->string('rejection_reason', 255)->nullable();
            $table->timestamps();

            $table->unique(['community_id', 'user_id']);
            $table->index(['community_id', 'status']);
        });

        Schema::create('community_bans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('community_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('banned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reason', 255)->nullable();
            $table->timestamps();

            $table->unique(['community_id', 'user_id']);
        });

        Schema::create('community_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('community_id')->constrained()->cascadeOnDelete();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 60);
            $table->foreignId('target_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('summary', 255);
            $table->json('meta')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['community_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('community_audit_logs');
        Schema::dropIfExists('community_bans');
        Schema::dropIfExists('community_join_requests');

        Schema::table('community_posts', function (Blueprint $table) {
            $table->dropIndex(['community_id', 'status', 'created_at']);
            $table->dropIndex(['status']);
            $table->dropConstrainedForeignId('reviewed_by');
            $table->dropColumn(['status', 'is_locked', 'reviewed_at', 'rejection_reason']);
        });

        Schema::table('community_members', function (Blueprint $table) {
            $table->dropIndex(['community_id', 'role']);
            $table->dropColumn(['muted_until', 'mute_reason']);
        });

        Schema::table('communities', function (Blueprint $table) {
            $table->dropColumn([
                'join_policy',
                'post_policy',
                'allow_member_comments',
                'allow_member_images',
                'welcome_message',
                'join_questions',
                'rules_items',
                'location',
                'website_url',
            ]);
        });
    }
};
