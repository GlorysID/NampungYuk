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
            $table->string('project_type', 50)->nullable()->after('category_id');
            $table->string('status', 30)->nullable()->default('beta')->after('project_type');
            $table->text('challenges')->nullable()->after('description');
            $table->text('learnings')->nullable()->after('challenges');
            $table->text('setup_instructions')->nullable()->after('tech_stacks');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn([
                'project_type',
                'status',
                'challenges',
                'learnings',
                'setup_instructions',
            ]);
        });
    }
};
