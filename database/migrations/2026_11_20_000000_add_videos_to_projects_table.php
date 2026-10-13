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
            // Primary/cover video (uploaded path or external URL).
            $table->string('video')->nullable()->after('images');
            // JSON array of additional gallery video URLs/paths.
            $table->json('videos')->nullable()->after('video');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['video', 'videos']);
        });
    }
};
