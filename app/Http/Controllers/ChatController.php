<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ChatController extends Controller
{
    /**
     * Chat inbox — list of conversations (placeholder until realtime ships).
     */
    public function index(): View
    {
        // Suggested people to start chatting with (same source as "who to follow").
        $suggested = User::where('id', '!=', Auth::id())
            ->orderByDesc('reputation_points')
            ->take(8)
            ->get();

        return view('chat.index', compact('suggested'));
    }

    /**
     * A single conversation view (placeholder).
     */
    public function show(string $username): View
    {
        $other = User::where('username', $username)->firstOrFail();

        return view('chat.show', compact('other'));
    }
}
