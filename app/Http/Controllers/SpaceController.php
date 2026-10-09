<?php

namespace App\Http\Controllers;

use App\Events\SpaceSignal;
use App\Models\Space;
use App\Models\SpaceParticipant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SpaceController extends Controller
{
    /**
     * List live spaces + recent ended ones.
     */
    public function index(): View
    {
        $live = Space::with('host')->where('status', 'live')->latest('started_at')->get();
        $recent = Space::with('host')->where('status', 'ended')->latest('ended_at')->take(6)->get();

        return view('spaces.index', compact('live', 'recent'));
    }

    /**
     * Create (start) a new space.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();
        if (! $user) {
            return redirect()->route('login');
        }

        $data = $request->validate([
            'title' => ['required', 'string', 'max:80'],
            'type' => ['required', 'in:audio,video'],
        ]);

        $space = Space::create([
            'host_id' => $user->id,
            'title' => $data['title'],
            'type' => $data['type'],
            'status' => 'live',
        ]);

        SpaceParticipant::create([
            'space_id' => $space->id,
            'user_id' => $user->id,
            'role' => 'host',
            'is_muted' => false,
        ]);

        return redirect()->route('spaces.show', $space->slug);
    }

    /**
     * Show a space room.
     */
    public function show(string $slug): View
    {
        $space = Space::with('host')->where('slug', $slug)->firstOrFail();
        $participants = $space->users()->orderByDesc('space_participants.created_at')->get();

        return view('spaces.show', compact('space', 'participants'));
    }

    /**
     * Join a space.
     */
    public function join(Request $request, Space $space): JsonResponse|RedirectResponse
    {
        $user = Auth::user();
        if (! $user) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            return redirect()->route('login');
        }

        SpaceParticipant::firstOrCreate(
            ['space_id' => $space->id, 'user_id' => $user->id],
            ['role' => 'listener', 'is_muted' => true]
        );

        broadcast(new SpaceSignal($space->id, $user->id, null, 'join', ['name' => $user->name]))->toOthers();

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('spaces.show', $space->slug);
    }

    /**
     * Leave a space.
     */
    public function leave(Request $request, Space $space): JsonResponse|RedirectResponse
    {
        $user = Auth::user();
        if (! $user) {
            return redirect()->route('login');
        }

        SpaceParticipant::where('space_id', $space->id)->where('user_id', $user->id)->delete();
        broadcast(new SpaceSignal($space->id, $user->id, null, 'leave', ['name' => $user->name]))->toOthers();

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('spaces.index');
    }

    /**
     * End a space (host only).
     */
    public function end(Request $request, Space $space): RedirectResponse
    {
        $user = Auth::user();
        if (! $user || $space->host_id !== $user->id) {
            abort(403);
        }

        $space->update(['status' => 'ended', 'ended_at' => now()]);
        broadcast(new SpaceSignal($space->id, $user->id, null, 'leave', ['ended' => true]));

        return redirect()->route('spaces.index')->with('success', 'Space diakhiri.');
    }

    /**
     * WebRTC signaling relay (offer / answer / ice) to a specific peer.
     */
    public function signal(Request $request, Space $space): JsonResponse
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $data = $request->validate([
            'to' => ['required', 'integer'],
            'kind' => ['required', 'in:offer,answer,ice'],
            'payload' => ['required', 'array'],
        ]);

        broadcast(new SpaceSignal($space->id, $user->id, $data['to'], $data['kind'], $data['payload']))->toOthers();

        return response()->json(['success' => true]);
    }
}
