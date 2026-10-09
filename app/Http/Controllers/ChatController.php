<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ChatController extends Controller
{
    /**
     * Chat inbox — list of conversations with the other participant.
     */
    public function index(Request $request): View
    {
        $userId = Auth::id();
        $search = $request->query('q');

        $conversations = Conversation::with(['userOne', 'userTwo'])
            ->where('user_one_id', $userId)
            ->orWhere('user_two_id', $userId)
            ->orderByDesc('last_message_at')
            ->orderByDesc('updated_at')
            ->get()
            ->map(function ($c) use ($userId) {
                $c->other = $c->otherUser($userId);
                $c->last = $c->messages()->latest()->first();
                $c->unread = $c->messages()
                    ->where('sender_id', '!=', $userId)
                    ->whereNull('read_at')
                    ->count();

                return $c;
            })
            ->filter(fn ($c) => $c->other !== null);

        if ($search) {
            $conversations = $conversations->filter(
                fn ($c) => str_contains(strtolower($c->other->name), strtolower($search))
                    || str_contains(strtolower($c->other->username), strtolower($search))
            );
        }

        $suggested = User::where('id', '!=', $userId)
            ->orderByDesc('reputation_points')
            ->take(8)
            ->get();

        return view('chat.index', compact('conversations', 'suggested', 'search'));
    }

    /**
     * A single conversation view.
     */
    public function show(string $username): View
    {
        $userId = Auth::id();
        $other = User::where('username', $username)->firstOrFail();

        if ($other->id === $userId) {
            abort(404);
        }

        $conversation = Conversation::between($userId, $other->id);

        // Mark incoming messages as read.
        $conversation->messages()
            ->where('sender_id', '!=', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $messages = $conversation->messages()->with('sender')->get();

        $suggested = User::where('id', '!=', $userId)
            ->where('id', '!=', $other->id)
            ->orderByDesc('reputation_points')
            ->take(6)
            ->get();

        return view('chat.show', compact('other', 'conversation', 'messages', 'suggested'));
    }

    /**
     * Send a message (AJAX). Broadcasts to the private conversation channel.
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

        $conversation = Conversation::between($auth->id, $user->id);

        $message = $conversation->messages()->create([
            'sender_id' => $auth->id,
            'body' => $data['body'],
        ]);

        $conversation->update(['last_message_at' => now()]);

        $message->load('sender');
        broadcast(new MessageSent($message))->toOthers();

        return response()->json([
            'success' => true,
            'message' => [
                'id' => $message->id,
                'sender_id' => $message->sender_id,
                'sender_name' => $auth->name,
                'body' => $message->body,
                'time' => $message->created_at->format('H:i'),
            ],
        ]);
    }
}
