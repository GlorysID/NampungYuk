<?php

namespace App\Http\Controllers;

use App\Events\SpaceSignal;
use App\Models\Space;
use App\Models\SpaceParticipant;
use App\Models\SpaceSignalLog;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
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
        $authId = Auth::id();

        $initialMessages = SpaceSignalLog::where('space_id', $space->id)
            ->where('kind', 'chat')
            ->orderBy('id')
            ->take(100)
            ->get()
            ->map(fn (SpaceSignalLog $log): array => [
                'id' => $log->id,
                'name' => $log->payload['name'] ?? 'Peserta',
                'text' => $log->payload['text'] ?? '',
                'mine' => $authId !== null && (int) $log->from_user_id === (int) $authId,
            ])
            ->values()
            ->all();

        $lastSignalId = (int) (SpaceSignalLog::where('space_id', $space->id)->max('id') ?? 0);

        $participantsData = $this->formatParticipants($space, $participants);

        return view('spaces.show', compact(
            'space',
            'participants',
            'initialMessages',
            'lastSignalId',
            'participantsData'
        ));
    }

    /**
     * Resolve space by slug or numeric ID.
     */
    protected function resolveSpace(Space|string $space): Space
    {
        if ($space instanceof Space) {
            return $space;
        }

        return Space::where('slug', $space)
            ->orWhere('id', $space)
            ->firstOrFail();
    }

    /**
     * Join a space.
     */
    public function join(Request $request, Space|string $space): JsonResponse|RedirectResponse
    {
        $space = $this->resolveSpace($space);

        $user = Auth::user();
        if (! $user) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            return redirect()->route('login');
        }

        if ($space->status === 'ended') {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Space telah berakhir.'], 422);
            }

            return redirect()->route('spaces.index')->with('error', 'Space telah berakhir.');
        }

        SpaceParticipant::firstOrCreate(
            ['space_id' => $space->id, 'user_id' => $user->id],
            ['role' => 'listener', 'is_muted' => true]
        );

        $this->recordAndBroadcast($space, $user->id, null, 'join', ['name' => $user->name], true);

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('spaces.show', $space->slug);
    }

    /**
     * Leave a space.
     */
    public function leave(Request $request, Space|string $space): JsonResponse|RedirectResponse
    {
        $space = $this->resolveSpace($space);

        $user = Auth::user();
        if (! $user) {
            return redirect()->route('login');
        }

        SpaceParticipant::where('space_id', $space->id)->where('user_id', $user->id)->delete();
        $this->recordAndBroadcast($space, $user->id, null, 'leave', ['name' => $user->name], true);

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('spaces.index');
    }

    /**
     * End a space (host only).
     */
    public function end(Request $request, Space|string $space): RedirectResponse
    {
        $space = $this->resolveSpace($space);

        $user = Auth::user();
        if (! $user || $space->host_id !== $user->id) {
            abort(403);
        }

        $space->update(['status' => 'ended', 'ended_at' => now()]);
        $this->recordAndBroadcast($space, $user->id, null, 'leave', ['ended' => true], false);

        SpaceSignalLog::where('space_id', $space->id)
            ->where('kind', '!=', 'chat')
            ->where('created_at', '<', now()->subMinutes(5))
            ->delete();

        return redirect()->route('spaces.index')->with('success', 'Space diakhiri.');
    }

    /**
     * Poll pending WebRTC signals, space chat, and participants for offline/HTTP fallback.
     */
    public function sync(Request $request, Space|string $space): JsonResponse
    {
        $space = $this->resolveSpace($space);

        $user = Auth::user();
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $afterId = max(0, (int) $request->query('after_id', 0));

        $logs = SpaceSignalLog::where('space_id', $space->id)
            ->where('id', '>', $afterId)
            ->where('from_user_id', '!=', $user->id)
            ->where(function ($q) use ($user): void {
                $q->whereNull('to_user_id')
                    ->orWhere('to_user_id', $user->id);
            })
            ->orderBy('id')
            ->take(100)
            ->get();

        $lastId = $logs->isNotEmpty()
            ? (int) $logs->max('id')
            : max($afterId, (int) (SpaceSignalLog::where('space_id', $space->id)->max('id') ?? 0));

        $participants = $space->users()->orderByDesc('space_participants.created_at')->get();

        return response()->json([
            'status' => $space->status,
            'last_id' => $lastId,
            'signals' => $logs->map(fn (SpaceSignalLog $log): array => [
                'id' => $log->id,
                'from' => $log->from_user_id,
                'to' => $log->to_user_id,
                'kind' => $log->kind,
                'payload' => $log->payload ?? [],
            ])->values()->all(),
            'participants' => $this->formatParticipants($space, $participants),
        ]);
    }

    /**
     * WebRTC signaling & live chat relay within a space.
     */
    public function signal(Request $request, Space|string $space): JsonResponse
    {
        $space = $this->resolveSpace($space);

        $user = Auth::user();
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $data = $request->validate([
            'to' => ['nullable', 'integer'],
            'kind' => ['required', 'in:offer,answer,ice,chat'],
            'payload' => ['required', 'array'],
        ]);

        $isSenderParticipant = SpaceParticipant::where('space_id', $space->id)
            ->where('user_id', $user->id)
            ->exists() || (int) $space->host_id === (int) $user->id;

        if (! $isSenderParticipant) {
            abort(403, 'Hanya peserta aktif yang dapat bertukar sinyal.');
        }

        if ($data['kind'] === 'chat') {
            $text = trim((string) ($data['payload']['text'] ?? ''));
            if ($text === '') {
                return response()->json(['message' => 'Pesan tidak boleh kosong.'], 422);
            }

            $log = $this->recordAndBroadcast($space, $user->id, null, 'chat', [
                'name' => $user->name,
                'text' => mb_substr($text, 0, 500),
            ], true);

            return response()->json(['success' => true, 'signal_id' => $log->id]);
        }

        if (empty($data['to'])) {
            return response()->json(['message' => 'Target peserta wajib diisi.'], 422);
        }

        $isRecipientParticipant = SpaceParticipant::where('space_id', $space->id)
            ->where('user_id', $data['to'])
            ->exists();

        if (! $isRecipientParticipant) {
            abort(403, 'Hanya peserta aktif yang dapat bertukar sinyal.');
        }

        $log = $this->recordAndBroadcast($space, $user->id, (int) $data['to'], $data['kind'], $data['payload'], true);

        return response()->json(['success' => true, 'signal_id' => $log->id]);
    }

    /**
     * Format space participants consistently for initial Blade render and JSON sync.
     *
     * @param  Collection<int, User>  $participants
     * @return array<int, array<string, mixed>>
     */
    protected function formatParticipants(Space $space, $participants): array
    {
        return $participants->map(fn ($p): array => [
            'id' => (int) $p->id,
            'name' => $p->name,
            'username' => $p->username,
            'avatar' => $p->avatar,
            'is_host' => (int) $p->id === (int) $space->host_id,
            'role' => $p->pivot?->role ?? ((int) $p->id === (int) $space->host_id ? 'host' : 'listener'),
        ])->values()->all();
    }

    /**
     * Persist a space signal/chat entry for HTTP polling fallback and try immediate WebSocket broadcast.
     *
     * @param  array<string, mixed>  $payload
     */
    protected function recordAndBroadcast(
        Space $space,
        int $fromId,
        ?int $toId,
        string $kind,
        array $payload = [],
        bool $toOthers = true
    ): SpaceSignalLog {
        $log = SpaceSignalLog::create([
            'space_id' => $space->id,
            'from_user_id' => $fromId,
            'to_user_id' => $toId,
            'kind' => $kind,
            'payload' => $payload,
        ]);

        $this->safeBroadcast(
            new SpaceSignal($space->id, $fromId, $toId, $kind, $payload, $log->id),
            $toOthers
        );

        return $log;
    }

    /**
     * Dispatch broadcast event safely so an offline local Reverb server never triggers HTTP 500.
     */
    protected function safeBroadcast(object $event, bool $toOthers = false): void
    {
        $isReverb = config('broadcasting.default') === 'reverb';
        if ($isReverb && Cache::get('spaces:reverb_offline', false)) {
            return;
        }

        try {
            $pending = broadcast($event);
            if ($toOthers) {
                $pending->toOthers();
            }
            unset($pending);
        } catch (\Throwable) {
            if ($isReverb) {
                Cache::put('spaces:reverb_offline', true, 10);
            }
        }
    }
}
