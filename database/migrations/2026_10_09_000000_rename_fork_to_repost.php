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
        // Rename fork terminology to repost terminology.
        Schema::table('projects', function (Blueprint $table) {
            $table->renameColumn('forks_count', 'reposts_count');
            $table->dropForeign(['forked_from_id']);
            $table->renameColumn('forked_from_id', 'reposted_from_id');
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->foreign('reposted_from_id')->references('id')->on('projects')->nullOnDelete();
        });

        Schema::create('project_reposts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['project_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_reposts');

        Schema::table('projects', function (Blueprint $table) {
            $table->dropForeign(['reposted_from_id']);
            $table->renameColumn('reposted_from_id', 'forked_from_id');
            $table->renameColumn('reposts_count', 'forks_count');
        });
    }
};
