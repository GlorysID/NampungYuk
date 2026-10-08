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
        Schema::table('projects', function (Blueprint $table) {
            $table->string('visibility', 20)->default('public')->after('status')->index();
            $table->boolean('is_pinned')->default(false)->after('is_featured');
            $table->foreignId('forked_from_id')->nullable()->after('category_id')
                ->constrained('projects')->nullOnDelete();
            $table->integer('forks_count')->default(0)->after('views_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropForeign(['forked_from_id']);
            $table->dropColumn(['visibility', 'is_pinned', 'forked_from_id', 'forks_count']);
        });
    }
};
