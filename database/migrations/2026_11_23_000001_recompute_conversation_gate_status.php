<?php

use App\Models\Conversation;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Recompute the gate status for every existing conversation using the
     * Instagram-style rule (recipient follows sender → accepted). Conversations
     * that were defaulted to 'accepted' before the rule existed are corrected,
     * so a one-way follower can no longer bypass the message request.
     */
    public function up(): void
    {
        DB::table('conversations')->orderBy('id')->chunkById(200, function ($rows) {
            foreach ($rows as $row) {
                $senderId = $row->initiator_id;

                if (! $senderId) {
                    // No initiator recorded (legacy row): try both directions and
                    // keep 'accepted' only if at least one side follows the other's
                    // counterpart in a way that grants direct delivery.
                    $a = (int) $row->user_one_id;
                    $b = (int) $row->user_two_id;

                    // If either user follows the other, treat as accepted (they
                    // had some relationship); otherwise demote to pending.
                    $oneFollows = DB::table('follows')
                        ->where(function ($q) use ($a, $b) {
                            $q->where('follower_id', $a)->where('following_id', $b);
                        })
                        ->orWhere(function ($q) use ($a, $b) {
                            $q->where('follower_id', $b)->where('following_id', $a);
                        })
                        ->exists();

                    if (! $oneFollows && $row->status === 'accepted' && empty($row->accepted_at)) {
                        DB::table('conversations')->where('id', $row->id)->update(['status' => 'pending']);
                    }

                    continue;
                }

                // recipient = the participant who is not the initiator
                $recipientId = ((int) $row->user_one_id === (int) $senderId)
                    ? (int) $row->user_two_id
                    : (int) $row->user_one_id;

                // No explicit acceptance recorded → recompute from follows.
                if (empty($row->accepted_at)) {
                    $recipientFollowsSender = DB::table('follows')
                        ->where('follower_id', $recipientId)
                        ->where('following_id', $senderId)
                        ->exists();

                    DB::table('conversations')->where('id', $row->id)->update([
                        'status' => $recipientFollowsSender ? 'accepted' : 'pending',
                    ]);
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Irreversible data fix — nothing to restore.
    }
};
