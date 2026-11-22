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
        if (Schema::hasColumn('users', 'warning_at') && ! Schema::hasColumn('users', 'warned_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->renameColumn('warning_at', 'warned_at');
            });
        } elseif (! Schema::hasColumn('users', 'warned_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->timestamp('warned_at')->nullable()->after('warning_notes');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Intentionally a no-op so rolling back 2026_10_19_000003_add_warning_to_users_table
        // (which drops warned_at) remains valid without reintroducing legacy warning_at drift.
    }
};
