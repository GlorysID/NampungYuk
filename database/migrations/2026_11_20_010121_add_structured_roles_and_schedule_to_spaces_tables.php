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
        Schema::table('spaces', function (Blueprint $table) {
            $table->text('description')->nullable()->after('type');
            $table->foreignId('pinned_project_id')
                ->nullable()
                ->after('description')
                ->constrained('projects')
                ->nullOnDelete();
            $table->timestamp('scheduled_at')->nullable()->after('status')->index();
        });

        Schema::table('space_participants', function (Blueprint $table) {
            $table->timestamp('hand_raised_at')->nullable()->after('is_muted')->index();
            $table->boolean('is_forced_muted')->default(false)->after('hand_raised_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('space_participants', function (Blueprint $table) {
            $table->dropIndex(['hand_raised_at']);
            $table->dropColumn(['hand_raised_at', 'is_forced_muted']);
        });

        Schema::table('spaces', function (Blueprint $table) {
            $table->dropForeign(['pinned_project_id']);
            $table->dropIndex(['scheduled_at']);
            $table->dropColumn(['description', 'pinned_project_id', 'scheduled_at']);
        });
    }
};
