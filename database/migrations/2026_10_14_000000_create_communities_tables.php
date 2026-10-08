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
        Schema::create('communities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('visibility', 20)->default('public')->index();
            $table->string('icon')->nullable();
            $table->string('cover')->nullable();
            $table->unsignedInteger('members_count')->default(0);
            $table->unsignedInteger('posts_count')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->timestamps();
        });

        Schema::create('community_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('community_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('role', 20)->default('member'); // owner, mod, member
            $table->timestamps();

            $table->unique(['community_id', 'user_id']);
        });

        Schema::create('community_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('community_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->text('content');
            $table->json('images')->nullable();
            $table->integer('upvotes_count')->default(0);
            $table->integer('downvotes_count')->default(0);
            $table->integer('score')->default(0)->index();
            $table->integer('comments_count')->default(0);
            $table->timestamps();

            $table->index(['community_id', 'created_at']);
        });

        Schema::create('community_post_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('community_post_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('community_post_comments')->cascadeOnDelete();
            $table->text('content');
            $table->integer('upvotes_count')->default(0);
            $table->timestamps();
        });

        Schema::create('community_post_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('community_post_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type', 10); // up / down
            $table->timestamps();

            $table->unique(['community_post_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('community_post_votes');
        Schema::dropIfExists('community_post_comments');
        Schema::dropIfExists('community_posts');
        Schema::dropIfExists('community_members');
        Schema::dropIfExists('communities');
    }
};
