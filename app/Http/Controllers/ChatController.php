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
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

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

        /** @var User $auth */
        $auth = Auth::user();
        $blockedIds = $auth->allBlockedIds();

        // Suggested people to start a new chat with (exclude self, banned, blocked).
        $suggested = User::where('id', '!=', $userId)
            ->where('is_banned', false)
            ->discoverable()
            ->when(! empty($blockedIds), fn ($q) => $q->whereNotIn('id', $blockedIds))
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
     * @return array{conversations: Collection, requests: Collection}
     */
    private function inboxData(int $userId, ?string $search): array
    {
        $all = Conversation::with(['userOne', 'userTwo'])
            ->where(function ($q) use ($userId) {
                $q->where('user_one_id', $userId)->orWhere('user_two_id', $userId);
            })
            ->orderByDesc('last_message_at')
            ->orderByDesc('updated_at')
            ->get();

        // A block severs the history: hide those conversations before any message is read.
        /** @var User|null $viewer */
        $viewer = Auth::user();
        $blockedIds = $viewer
            ? $viewer->blockedUsers()->pluck('users.id')
                ->merge($viewer->blockedByUsers()->pluck('users.id'))
                ->all()
            : [];

        $all = $all
            ->reject(fn (Conversation $c) => in_array($c->user_one_id, $blockedIds, true)
                || in_array($c->user_two_id, $blockedIds, true))
            ->values();

        $conversationIds = $all->pluck('id');

        // Batch load latest message per conversation (avoids N+1)
        $latestMessages = Message::whereIn('conversation_id', $conversationIds)
            ->whereIn('id', function ($query) {
                $query->selectRaw('max(id)')
                    ->from('messages')
                    ->whereNull('deleted_at')
                    ->groupBy('conversation_id');
            })
            ->get()
            ->keyBy('conversation_id');

        // Batch count unread incoming messages per conversation (avoids N+1)
        $unreadCounts = Message::whereIn('conversation_id', $conversationIds)
            ->where('sender_id', '!=', $userId)
            ->whereNull('read_at')
            ->selectRaw('conversation_id, count(*) as unread_count')
            ->groupBy('conversation_id')
            ->pluck('unread_count', 'conversation_id');

        $all = $all->map(function ($c) use ($userId, $latestMessages, $unreadCounts) {
            $c->other = $c->otherUser($userId);
            $c->last = $latestMessages->get($c->id);
            $c->unread = (int) ($unreadCounts->get($c->id) ?? 0);

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
     * @return Collection
     */
    private function peopleSearch(int $userId, ?string $search, Collection $conversations)
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

        /** @var User $auth */
        $auth = Auth::user();
        if ($auth->hasBlocked($other) || $auth->isBlockedBy($other)) {
            abort(403, 'Obrolan tidak tersedia karena salah satu akun memblokir.');
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

            // Mark incoming messages as read, then notify the sender live when read receipts are enabled.
            // (Only for accepted chats — a pending request stays "unread" until accepted.)
            if ($conversation->isAccepted()) {
                $readBefore = $conversation->messages()
                    ->where('sender_id', '!=', $userId)
                    ->whereNull('read_at');

                $upToId = (clone $readBefore)->max('id');
                $markedCount = (clone $readBefore)->update(['read_at' => now()]);

                /** @var User|null $authUser */
                $authUser = Auth::user();
                if ($markedCount > 0 && ($authUser?->preference('privacy.show_read_receipts', true) ?? true)) {
                    $this->safeBroadcast(new MessageRead($conversation->id, $userId, $upToId), toOthers: true);
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

        // Recipient DM privacy policy (blocks are enforced before any message read).
        // Only gates a brand-new thread: an existing one keeps working.
        $blockedByPolicy = ! $conversation && ! $other->allowsDirectMessageFrom($auth);
        if ($blockedByPolicy) {
            $canSend = false;
        }

        $hasMore = $messages->count() > self::MESSAGES_PER_PAGE;
        if ($hasMore) {
            $messages = $messages->take(self::MESSAGES_PER_PAGE);
        }
        $messages = $messages->sortBy('id')->values();

        $other->loadCount([
            'projects' => fn ($q) => $q->visibleTo($userId),
            'followers',
        ]);

        $blockedIds = $auth->allBlockedIds();
        $suggested = User::where('id', '!=', $userId)
            ->where('id', '!=', $other->id)
            ->where('is_banned', false)
            ->discoverable()
            ->when(! empty($blockedIds), fn ($q) => $q->whereNotIn('id', $blockedIds))
            ->orderByDesc('reputation_points')
            ->take(6)
            ->get();

        // Sidebar list (desktop 2-pane): recent conversations + requests.
        ['conversations' => $conversations, 'requests' => $requests] = $this->inboxData($userId, null);
        $peopleResults = collect();
        $search = null;
        $conversationId = $conversation?->id;
        $showReadReceipts = (bool) $auth->preference('privacy.show_read_receipts', true)
            && (bool) $other->preference('privacy.show_read_receipts', true);
        $iFollowOther = $auth->isFollowing($other);
        $otherFollowsMe = $other->isFollowing($auth);

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
            'blockedByPolicy',
            'conversations',
            'requests',
            'peopleResults',
            'search',
            'showReadReceipts',
            'iFollowOther',
            'otherFollowsMe'
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

        if ($auth->hasBlocked($user) || $auth->isBlockedBy($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Obrolan tidak tersedia karena salah satu akun sedang memblokir.',
            ], 403);
        }

        $existingConversation = Conversation::findBetween($auth->id, $user->id);
        if (! $existingConversation && ! $user->allowsDirectMessageFrom($auth)) {
            return response()->json([
                'success' => false,
                'message' => 'Pengguna ini hanya menerima obrolan dari akun yang mereka ikuti.',
            ], 403);
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
        Cache::forget($this->typingCacheKey($auth->id, $user->id));
        $this->safeBroadcast(new MessageSent($message), toOthers: true);
        $this->safeBroadcast(new InboxUpdated($user->id, $message));

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
        if (! $recipient->wantsNotification('new_message')) {
            return;
        }

        $recipient->notifications()
            ->where('type', NewMessageNotification::class)
            ->whereNull('read_at')
            ->get()
            ->filter(fn ($n) => ($n->data['conversation_id'] ?? null) === $message->conversation_id)
            ->each->delete();

        $recipient->notify(new NewMessageNotification($message));
    }

    /**
     * Broadcast a typing indicator (no persistence) and cache it briefly so
     * clients can also receive typing state even when Reverb is offline.
     */
    public function typing(Request $request, User $user): JsonResponse
    {
        $auth = Auth::user();
        if (! $auth || $user->id === $auth->id) {
            return response()->json(['success' => false], 401);
        }

        if ($auth->hasBlocked($user) || $auth->isBlockedBy($user)) {
            return response()->json(['success' => false], 403);
        }

        $typing = (bool) $request->boolean('typing', true);

        // Never create a conversation just for a typing ping.
        $conversation = Conversation::findBetween($auth->id, $user->id);

        // Don't leak typing into a pending request the recipient hasn't accepted,
        // or when the initiator already sent their single opener.
        if ($conversation && $conversation->isPending()) {
            if ($conversation->initiator_id !== $auth->id || $conversation->messages()->exists()) {
                return response()->json(['success' => true]);
            }
        }

        $cacheKey = $this->typingCacheKey($auth->id, $user->id);
        if ($typing) {
            Cache::put($cacheKey, true, now()->addSeconds(5));
        } else {
            Cache::forget($cacheKey);
        }

        $this->safeBroadcast(
            new UserTyping($conversation?->id ?? 0, $auth->id, $auth->name, $typing, $user->id),
            toOthers: true
        );

        return response()->json(['success' => true]);
    }

    /**
     * Lightweight realtime sync endpoint (new messages, typing indicator, read receipts).
     * Acts as automatic fallback when WebSocket / Reverb is not connected.
     */
    public function sync(Request $request, User $user): JsonResponse
    {
        $auth = Auth::user();
        if (! $auth) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        if ($user->id === $auth->id) {
            return response()->json(['message' => 'Invalid.'], 422);
        }

        if ($auth->hasBlocked($user) || $auth->isBlockedBy($user)) {
            return response()->json(['success' => false], 403);
        }

        $afterId = max(0, (int) $request->query('after', 0));
        $conversation = Conversation::findBetween($auth->id, $user->id);
        $isTyping = (bool) Cache::get($this->typingCacheKey($user->id, $auth->id), false);

        if (! $conversation) {
            return response()->json([
                'success' => true,
                'conversation' => null,
                'messages' => [],
                'typing' => $isTyping,
                'read_up_to_id' => null,
            ]);
        }

        $isPending = $conversation->isPending();
        $isInitiator = (int) $conversation->initiator_id === (int) $auth->id;
        $canRespond = $isPending && ! $isInitiator;
        $canSend = ! $isPending || ($isInitiator && ! $conversation->messages()->exists());

        $showReadReceipts = (bool) $auth->preference('privacy.show_read_receipts', true)
            && (bool) $user->preference('privacy.show_read_receipts', true);

        if ($conversation->isAccepted()) {
            $unreadQuery = $conversation->messages()
                ->where('sender_id', '!=', $auth->id)
                ->whereNull('read_at');

            $upToId = (clone $unreadQuery)->max('id');
            $marked = (clone $unreadQuery)->update(['read_at' => now()]);

            if ($marked > 0 && $auth->preference('privacy.show_read_receipts', true)) {
                $this->safeBroadcast(new MessageRead($conversation->id, $auth->id, $upToId), toOthers: true);
            }
        }

        $newMessages = $conversation->messages()
            ->with('sender:id,name')
            ->where('id', '>', $afterId)
            ->orderBy('id')
            ->take(50)
            ->get()
            ->map(fn (Message $m) => [
                'id' => $m->id,
                'sender_id' => $m->sender_id,
                'sender_name' => $m->sender?->name,
                'body' => $m->body,
                'time' => $m->created_at->format('H:i'),
                'created_at' => $m->created_at->toIso8601String(),
                'read_at' => $showReadReceipts ? $m->read_at?->toIso8601String() : null,
                'edited_at' => $m->edited_at?->toIso8601String(),
            ])
            ->values();

        $readUpToId = $showReadReceipts
            ? $conversation->messages()
                ->where('sender_id', $auth->id)
                ->whereNotNull('read_at')
                ->max('id')
            : null;

        return response()->json([
            'success' => true,
            'conversation' => [
                'id' => $conversation->id,
                'status' => $conversation->status,
                'can_send' => $canSend,
                'can_respond' => $canRespond,
            ],
            'messages' => $newMessages,
            'typing' => $isTyping,
            'read_up_to_id' => $readUpToId,
        ]);
    }

    /**
     * Mark incoming messages in an active accepted conversation as read and broadcast receipt.
     */
    public function markRead(Request $request, User $user): JsonResponse
    {
        $auth = Auth::user();
        if (! $auth || $user->id === $auth->id) {
            return response()->json(['success' => false], 401);
        }

        if ($auth->hasBlocked($user) || $auth->isBlockedBy($user)) {
            return response()->json(['success' => false], 403);
        }

        $conversation = Conversation::findBetween($auth->id, $user->id);
        if (! $conversation || ! $conversation->isAccepted()) {
            return response()->json(['success' => true, 'marked' => 0]);
        }

        $unreadQuery = $conversation->messages()
            ->where('sender_id', '!=', $auth->id)
            ->whereNull('read_at');

        $upToId = (clone $unreadQuery)->max('id');
        $marked = (clone $unreadQuery)->update(['read_at' => now()]);

        if ($marked > 0 && $auth->preference('privacy.show_read_receipts', true)) {
            $this->safeBroadcast(new MessageRead($conversation->id, $auth->id, $upToId), toOthers: true);
        }

        return response()->json(['success' => true, 'marked' => $marked]);
    }

    protected function typingCacheKey(int $senderId, int $recipientId): string
    {
        return "chat:typing:{$senderId}:{$recipientId}";
    }

    /**
     * Broadcast an event immediately without letting an offline Reverb server
     * break the HTTP response.
     */
    protected function safeBroadcast(object $event, bool $toOthers = false): void
    {
        $isReverb = config('broadcasting.default') === 'reverb';
        if ($isReverb && Cache::get('chat:reverb_offline', false)) {
            return;
        }

        try {
            $pending = broadcast($event);
            if ($toOthers) {
                $pending->toOthers();
            }
            // PendingBroadcast dispatches inside __destruct(); force destruction
            // inside try so BroadcastException is caught instead of bubbling out.
            unset($pending);
        } catch (Throwable $e) {
            if ($isReverb) {
                Cache::put('chat:reverb_offline', true, now()->addSeconds(10));
            }
            Log::debug('Chat broadcast skipped: '.$e->getMessage());
        }
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

        if ($auth->hasBlocked($user) || $auth->isBlockedBy($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Obrolan tidak tersedia karena salah satu akun sedang memblokir.',
            ], 403);
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

        $showReadReceipts = (bool) $auth->preference('privacy.show_read_receipts', true)
            && (bool) $user->preference('privacy.show_read_receipts', true);

        $messages = $messages->sortBy('id')->values()->map(fn ($m) => [
            'id' => $m->id,
            'sender_id' => $m->sender_id,
            'body' => $m->body,
            'time' => $m->created_at->format('H:i'),
            'created_at' => $m->created_at->toIso8601String(),
            'read_at' => $showReadReceipts ? $m->read_at?->toIso8601String() : null,
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
        $this->safeBroadcast(new MessageSent($message), toOthers: true);

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
