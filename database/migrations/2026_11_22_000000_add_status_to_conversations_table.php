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
        Schema::table('conversations', function (Blueprint $table) {
            // 'accepted' = free chat, 'pending' = awaiting the recipient's approval.
            $table->string('status')->default('accepted')->index()->after('last_message_at');
            // Who opened the conversation (needed to decide who may accept/decline).
            $table->foreignId('initiator_id')->nullable()->after('status')->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('initiator_id');
            $table->dropColumn('status');
        });
    }
};
