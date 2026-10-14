<?php

namespace App\Http\Controllers;

use App\Events\InboxUpdated;
use App\Events\MessageRead;
use App\Events\MessageSent;
use App\Events\UserTyping;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Notifications\NewMessageNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ChatController extends Controller
{
    /**
     * How many messages to load per "page" of a conversation.
     */
    private const MESSAGES_PER_PAGE = 40;

    /**
     * Chat inbox — list of conversations with the other participant.
     */
    public function index(Request $request): View
    {
        $userId = Auth::id();
        $search = $request->query('q');

        ['conversations' => $conversations, 'requests' => $requests] = $this->inboxData($userId, $search);

        // Suggested people to start a new chat with (exclude self, banned).
        $suggested = User::where('id', '!=', $userId)
            ->where('is_banned', false)
            ->orderByDesc('reputation_points')
            ->take(8)
            ->get();

        // People search for "start a new chat".
        $peopleResults = $this->peopleSearch($userId, $search, $conversations);

        return view('chat.index', compact('conversations', 'requests', 'suggested', 'search', 'peopleResults'));
    }

    /**
     * Build the inbox (accepted conversations + pending requests) for a user.
     *
     * @return array{conversations: \Illuminate\Support\Collection, requests: \Illuminate\Support\Collection}
     */
    private function inboxData(int $userId, ?string $search): array
    {
        $all = Conversation::with(['userOne', 'userTwo'])
            ->where(function ($q) use ($userId) {
                $q->where('user_one_id', $userId)->orWhere('user_two_id', $userId);
            })
            ->orderByDesc('last_message_at')
            ->orderByDesc('updated_at')
            ->get()
            ->map(function ($c) use ($userId) {
                $c->other = $c->otherUser($userId);
                $c->last = $c->messages()->latest()->first();
                $c->unread = $c->unreadCountFor($userId);

                return $c;
            })
            ->filter(fn ($c) => $c->other !== null);

        // Pending requests are only shown to the recipient (the non-initiator).
        $requests = $all
            ->filter(fn ($c) => $c->isPending() && $c->initiator_id !== $userId)
            ->values();

        // Active chats: accepted, or pending ones I initiated (shown as "awaiting").
        $conversations = $all
            ->filter(fn ($c) => ! $c->isPending() || $c->initiator_id === $userId)
            ->values();

        if ($search) {
            $needle = strtolower($search);
            $conversations = $conversations->filter(
                fn ($c) => str_contains(strtolower($c->other->name), $needle)
                    || str_contains(strtolower($c->other->username), $needle)
            )->values();
        }

        return ['conversations' => $conversations, 'requests' => $requests];
    }

    /**
     * Search accounts to start a new chat with, excluding ones already listed.
     *
     * @param  \Illuminate\Support\Collection  $conversations
     * @return \Illuminate\Support\Collection
     */
    private function peopleSearch(int $userId, ?string $search, \Illuminate\Support\Collection $conversations)
    {
        if (! $search) {
            return collect();
        }

        $listedIds = $conversations->map(fn ($c) => $c->other->id)->all();

        return User::where('id', '!=', $userId)
            ->where('is_banned', false)
            ->search($search)
            ->orderByDesc('reputation_points')
            ->take(10)
            ->get()
            ->reject(fn ($u) => in_array($u->id, $listedIds, true))
            ->values();
    }

    /**
     * A single conversation view.
     */
    public function show(Request $request, string $username): View
    {
        $userId = Auth::id();
        $other = User::where('username', $username)->firstOrFail();

        if ($other->id === $userId) {
            abort(404);
        }

        // Never create a conversation just by viewing. If none exists yet, the
        // chat starts empty — the first message will establish pending/accepted.
        $conversation = Conversation::findBetween($userId, $other->id);

        if ($conversation) {
            $isPending = $conversation->isPending();
            $isInitiator = $conversation->initiator_id === $userId;

            // Only the recipient (non-initiator) may accept a pending request.
            $canRespond = $isPending && ! $isInitiator;

            // Mirror the send() gate exactly:
            //  - accepted chat  → anyone may send.
            //  - pending chat   → only the initiator, and only their single opener
            //                     (so if the opener already exists, even they are locked).
            if (! $isPending) {
                $canSend = true;
            } elseif ($isInitiator) {
                $canSend = ! $conversation->messages()->exists();
            } else {
                $canSend = false;
            }

            // Mark incoming messages as read, then notify the sender live.
            // (Only for accepted chats — a pending request stays "unread" until accepted.)
            if ($conversation->isAccepted()) {
                $readBefore = $conversation->messages()
                    ->where('sender_id', '!=', $userId)
                    ->whereNull('read_at');

                $upToId = (clone $readBefore)->max('id');
                $markedCount = (clone $readBefore)->update(['read_at' => now()]);

                if ($markedCount > 0) {
                    broadcast(new MessageRead($conversation->id, $userId, $upToId))->toOthers();
                }
            }

            // Load only the latest N messages; older ones load on demand.
            $messages = $conversation->messages()
                ->with('sender')
                ->latest()
                ->take(self::MESSAGES_PER_PAGE + 1)
                ->get();
        } else {
            // No conversation yet — a brand-new chat; anyone may send the first message.
            $isPending = false;
            $canRespond = false;
            $canSend = true;
            $messages = collect();
        }

        $hasMore = $messages->count() > self::MESSAGES_PER_PAGE;
        if ($hasMore) {
            $messages = $messages->take(self::MESSAGES_PER_PAGE);
        }
        $messages = $messages->sortBy('id')->values();

        $suggested = User::where('id', '!=', $userId)
            ->where('id', '!=', $other->id)
            ->where('is_banned', false)
            ->orderByDesc('reputation_points')
            ->take(6)
            ->get();

        // Sidebar list (desktop 2-pane): recent conversations + requests.
        ['conversations' => $conversations, 'requests' => $requests] = $this->inboxData($userId, null);
        $peopleResults = collect();
        $search = null;
        $conversationId = $conversation?->id;

        return view('chat.show', compact(
            'other',
            'conversation',
            'conversationId',
            'messages',
            'suggested',
            'hasMore',
            'isPending',
            'canRespond',
            'canSend',
            'conversations',
            'requests',
            'peopleResults',
            'search'
        ));
    }

    /**
     * Send a message (AJAX). Broadcasts to the private conversation channel
     * and notifies the recipient's inbox channel.
     */
    public function send(Request $request, User $user): JsonResponse
    {
        $auth = Auth::user();
        if (! $auth) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        if ($user->id === $auth->id) {
            return response()->json(['message' => 'Tidak bisa mengirim pesan ke diri sendiri.'], 422);
        }

        $data = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $conversation = Conversation::forMessage($auth->id, $user->id);

        // A pending request: only the initiator's single opener is allowed, and
        // they cannot keep messaging until the recipient accepts.
        if ($conversation->isPending()) {
            $hasMessages = $conversation->messages()->exists();

            if ($conversation->initiator_id === $auth->id && $hasMessages) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pesan menunggu diterima. Kamu hanya bisa mengirim satu pesan pembuka sampai permintaan diterima.',
                ], 403);
            }

            if ($conversation->initiator_id !== $auth->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terima permintaan pesan ini terlebih dahulu untuk membalas.',
                ], 403);
            }
        }

        $message = $conversation->messages()->create([
            'sender_id' => $auth->id,
            'body' => trim($data['body']),
        ]);

        $conversation->update(['last_message_at' => now()]);

        $message->load('sender');
        broadcast(new MessageSent($message))->toOthers();
        broadcast(new InboxUpdated($user->id, $message));

        // Drop a database notification so the bell dropdown shows the message.
        // Throttle: replace any existing unread "new_message" notification for
        // this same conversation instead of stacking one per message.
        $this->pushMessageNotification($user, $message);

        return response()->json([
            'success' => true,
            'conversation' => [
                'id' => $conversation->id,
                'status' => $conversation->status,
                // The sender is the initiator here; after a pending opener they
                // may not send again until the request is accepted.
                'can_send' => $conversation->isAccepted(),
            ],
            'message' => [
                'id' => $message->id,
                'sender_id' => $message->sender_id,
                'sender_name' => $auth->name,
                'body' => $message->body,
                'time' => $message->created_at->format('H:i'),
                'created_at' => $message->created_at->toIso8601String(),
                'read_at' => null,
                'edited_at' => null,
            ],
        ]);
    }

    /**
     * Create a database notification for the recipient, replacing any existing
     * unread message notification for the same conversation (so the bell shows
     * one fresh entry per conversation instead of one per bubble).
     */
    protected function pushMessageNotification(User $recipient, Message $message): void
    {
        $recipient->notifications()
            ->where('type', NewMessageNotification::class)
            ->whereNull('read_at')
            ->get()
            ->filter(fn ($n) => ($n->data['conversation_id'] ?? null) === $message->conversation_id)
            ->each->delete();

        $recipient->notify(new NewMessageNotification($message));
    }

    /**
     * Broadcast a typing indicator (no persistence).
     */
    public function typing(Request $request, User $user): JsonResponse
    {        $auth = Auth::user();
        if (! $auth || $user->id === $auth->id) {
            return response()->json(['success' => false], 401);
        }

        $typing = (bool) $request->boolean('typing', true);

        // Never create a conversation just for a typing ping.
        $conversation = Conversation::findBetween($auth->id, $user->id);
        if (! $conversation) {
            return response()->json(['success' => true]);
        }

        // Don't leak typing into a pending request the recipient hasn't accepted.
        if ($conversation->isPending() && $conversation->initiator_id !== $auth->id) {
            return response()->json(['success' => true]);
        }

        broadcast(new UserTyping($conversation->id, $auth->id, $auth->name, $typing))->toOthers();

        return response()->json(['success' => true]);
    }

    /**
     * Load older messages for a conversation (infinite scroll upward).
     */
    public function older(Request $request, User $user): JsonResponse
    {
        $auth = Auth::user();
        if (! $auth) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        if ($user->id === $auth->id) {
            return response()->json(['message' => 'Invalid.'], 422);
        }

        $beforeId = (int) $request->query('before', 0);

        $conversation = Conversation::findBetween($auth->id, $user->id);
        if (! $conversation) {
            return response()->json(['success' => true, 'messages' => [], 'has_more' => false]);
        }

        $messages = $conversation->messages()
            ->with('sender')
            ->when($beforeId > 0, fn ($q) => $q->where('id', '<', $beforeId))
            ->latest()
            ->take(self::MESSAGES_PER_PAGE + 1)
            ->get();

        $hasMore = $messages->count() > self::MESSAGES_PER_PAGE;
        if ($hasMore) {
            $messages = $messages->take(self::MESSAGES_PER_PAGE);
        }

        $messages = $messages->sortBy('id')->values()->map(fn ($m) => [
            'id' => $m->id,
            'sender_id' => $m->sender_id,
            'body' => $m->body,
            'time' => $m->created_at->format('H:i'),
            'created_at' => $m->created_at->toIso8601String(),
            'read_at' => $m->read_at?->toIso8601String(),
            'edited_at' => $m->edited_at?->toIso8601String(),
        ]);

        return response()->json([
            'success' => true,
            'messages' => $messages,
            'has_more' => $hasMore,
        ]);
    }

    /**
     * Delete a single message (sender or the other participant may remove it).
     */
    public function destroyMessage(Request $request, Message $message): JsonResponse
    {
        $auth = Auth::user();
        if (! $auth) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $conversation = $message->conversation;

        if (! $conversation || ! $conversation->isParticipant($auth->id)) {
            return response()->json(['message' => 'Tidak diizinkan.'], 403);
        }

        // Only the sender can delete their own message.
        if ((int) $message->sender_id !== (int) $auth->id) {
            return response()->json(['message' => 'Hanya pengirim yang dapat menghapus pesan ini.'], 403);
        }

        $message->delete();

        return response()->json(['success' => true, 'id' => $message->id]);
    }

    /**
     * Edit a message (sender only, within a short window).
     */
    public function editMessage(Request $request, Message $message): JsonResponse
    {
        $auth = Auth::user();
        if (! $auth) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $conversation = $message->conversation;

        if (! $conversation || ! $conversation->isParticipant($auth->id)) {
            return response()->json(['message' => 'Tidak diizinkan.'], 403);
        }

        if ((int) $message->sender_id !== (int) $auth->id) {
            return response()->json(['message' => 'Hanya pengirim yang dapat mengubah pesan ini.'], 403);
        }

        // Editing window: 15 minutes.
        if ($message->created_at->lt(now()->subMinutes(15))) {
            return response()->json(['message' => 'Pesan sudah terlalu lama untuk diubah.'], 403);
        }

        $data = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $message->update([
            'body' => trim($data['body']),
            'edited_at' => now(),
        ]);

        $message->load('sender');
        broadcast(new MessageSent($message))->toOthers();

        return response()->json([
            'success' => true,
            'message' => [
                'id' => $message->id,
                'sender_id' => $message->sender_id,
                'body' => $message->body,
                'time' => $message->created_at->format('H:i'),
                'created_at' => $message->created_at->toIso8601String(),
                'read_at' => $message->read_at?->toIso8601String(),
                'edited_at' => $message->edited_at?->toIso8601String(),
            ],
        ]);
    }

    /**
     * Clear every message in a conversation (participant only). Keeps the
     * conversation row so the gate/initiator state is preserved.
     */
    public function clearConversation(Request $request, string $username): JsonResponse
    {
        $auth = Auth::user();
        if (! $auth) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $other = User::where('username', $username)->firstOrFail();
        $conversation = Conversation::findBetween($auth->id, $other->id);

        if (! $conversation) {
            return response()->json(['success' => true]);
        }

        if (! $conversation->isParticipant($auth->id)) {
            return response()->json(['message' => 'Tidak diizinkan.'], 403);
        }

        $conversation->messages()->forceDelete();
        $conversation->update(['last_message_at' => null]);

        return response()->json(['success' => true]);
    }

    /**
     * Delete (clear) a whole conversation for the authenticated user.
     */
    public function destroyConversation(Request $request, string $username): JsonResponse
    {
        $auth = Auth::user();
        if (! $auth) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $other = User::where('username', $username)->firstOrFail();

        if ($other->id === $auth->id) {
            return response()->json(['message' => 'Invalid.'], 422);
        }

        $conversation = Conversation::findBetween($auth->id, $other->id);
        if (! $conversation) {
            return response()->json(['success' => true]);
        }

        // Hard-delete every message in this conversation.
        $conversation->messages()->forceDelete();
        $conversation->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Accept a pending message request (recipient only).
     */
    public function accept(Request $request, string $username): JsonResponse
    {
        $auth = Auth::user();
        if (! $auth) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $other = User::where('username', $username)->firstOrFail();
        $conversation = Conversation::findBetween($auth->id, $other->id);

        if (! $conversation || ! $conversation->isPending() || $conversation->initiator_id === $auth->id) {
            return response()->json(['message' => 'Tidak ada permintaan untuk diterima.'], 403);
        }

        $conversation->update([
            'status' => 'accepted',
            'accepted_at' => now(),
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * Decline (delete) a pending message request (recipient only).
     */
    public function decline(Request $request, string $username): JsonResponse
    {
        $auth = Auth::user();
        if (! $auth) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $other = User::where('username', $username)->firstOrFail();
        $conversation = Conversation::findBetween($auth->id, $other->id);

        if (! $conversation || ! $conversation->isPending() || $conversation->initiator_id === $auth->id) {
            return response()->json(['message' => 'Tidak ada permintaan untuk dihapus.'], 403);
        }

        $conversation->messages()->forceDelete();
        $conversation->delete();

        return response()->json(['success' => true]);
    }
}
