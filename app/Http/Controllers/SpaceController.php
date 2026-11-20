<?php

namespace App\Http\Controllers;

use App\Events\SpaceSignal;
use App\Models\Project;
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
    private const REACTION_TYPES = ['clap', 'fire', 'bulb', 'question', 'agree'];

    /**
     * List live, scheduled, and recent ended spaces alongside host eligibility.
     */
    public function index(): View
    {
        $user = Auth::user();

        $live = Space::with(['host', 'pinnedProject'])
            ->withCount('participants')
            ->where('status', 'live')
            ->latest('started_at')
            ->get();

        $scheduled = Space::with(['host', 'pinnedProject'])
            ->withCount('participants')
            ->where('status', 'scheduled')
            ->orderBy('scheduled_at')
            ->get();

        $recent = Space::with('host')
            ->where('status', 'ended')
            ->latest('ended_at')
            ->take(6)
            ->get();

        $hostEligibility = Space::hostEligibilityFor($user);

        $pinnableProjects = $user
            ? Project::visibleTo($user->id)
                ->where('visibility', 'public')
                ->whereNull('warning_notes')
                ->latest()
                ->take(25)
                ->get(['id', 'title', 'slug'])
            : collect();

        return view('spaces.index', compact(
            'live',
            'scheduled',
            'recent',
            'hostEligibility',
            'pinnableProjects'
        ));
    }

    /**
     * Create (start or schedule) a new space — restricted to verified & recognized creators/admins.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();
        if (! $user) {
            return redirect()->route('login');
        }

        abort_unless(
            Space::canBeCreatedBy($user),
            403,
            'Akunmu belum memenuhi syarat kreator terverifikasi untuk memulai Space.'
        );

        $data = $request->validate([
            'title' => ['required', 'string', 'max:80'],
            'type' => ['required', 'in:audio,video'],
            'description' => ['nullable', 'string', 'max:300'],
            'start_mode' => ['nullable', 'in:now,scheduled'],
            'scheduled_at' => ['required_if:start_mode,scheduled', 'nullable', 'date', 'after:now'],
            'pinned_project_id' => ['nullable', 'integer', 'exists:projects,id'],
        ]);

        $pinnedProjectId = null;
        if (! empty($data['pinned_project_id'])) {
            $validProject = Project::visibleTo($user->id)
                ->where('visibility', 'public')
                ->whereNull('warning_notes')
                ->whereKey($data['pinned_project_id'])
                ->exists();

            if (! $validProject) {
                return back()->withErrors([
                    'pinned_project_id' => 'Hanya karya publik aktif yang dapat disematkan di Space.',
                ])->withInput();
            }

            $pinnedProjectId = (int) $data['pinned_project_id'];
        }

        $isScheduled = ($data['start_mode'] ?? 'now') === 'scheduled';

        $space = Space::create([
            'host_id' => $user->id,
            'title' => $data['title'],
            'type' => $data['type'],
            'description' => $data['description'] ?? null,
            'pinned_project_id' => $pinnedProjectId,
            'status' => $isScheduled ? 'scheduled' : 'live',
            'scheduled_at' => $isScheduled ? $data['scheduled_at'] : null,
            'started_at' => $isScheduled ? null : now(),
        ]);

        SpaceParticipant::create([
            'space_id' => $space->id,
            'user_id' => $user->id,
            'role' => 'host',
            'is_muted' => true,
            'is_forced_muted' => false,
        ]);

        return redirect()->route('spaces.show', $space->slug);
    }

    /**
     * Start a scheduled space (host only).
     */
    public function start(Request $request, Space|string $space): JsonResponse|RedirectResponse
    {
        $space = $this->resolveSpace($space);
        $user = Auth::user();

        if (! $user || (int) $space->host_id !== (int) $user->id) {
            abort(403, 'Hanya Host yang dapat memulai Space terjadwal.');
        }

        if ($space->status === 'ended') {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Space telah berakhir.'], 422);
            }

            return redirect()->route('spaces.show', $space->slug)->with('error', 'Space telah berakhir.');
        }

        if ($space->status === 'scheduled') {
            $space->update([
                'status' => 'live',
                'started_at' => now(),
            ]);

            $this->recordAndBroadcast($space, $user->id, null, 'state', [
                'action' => 'space_started',
                'status' => 'live',
            ], false);
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'status' => $space->status]);
        }

        return redirect()->route('spaces.show', $space->slug);
    }

    /**
     * Show a space room.
     */
    public function show(string $slug): View
    {
        $space = Space::with(['host', 'pinnedProject.user'])->where('slug', $slug)->firstOrFail();
        $participants = $space->users()->orderByDesc('space_participants.created_at')->get();
        $authUser = Auth::user();
        $authId = $authUser?->id;

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
        $pinnedProjectData = $this->formatPinnedProject($space);

        $pinnableProjects = $authUser
            ? Project::visibleTo($authUser->id)
                ->where('visibility', 'public')
                ->whereNull('warning_notes')
                ->latest()
                ->take(25)
                ->get(['id', 'title', 'slug'])
            : collect();

        return view('spaces.show', compact(
            'space',
            'participants',
            'initialMessages',
            'lastSignalId',
            'participantsData',
            'pinnedProjectData',
            'pinnableProjects'
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
     * Join a space (defaults to listener role unless user is the host).
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

        $isHost = (int) $space->host_id === (int) $user->id;

        SpaceParticipant::firstOrCreate(
            ['space_id' => $space->id, 'user_id' => $user->id],
            [
                'role' => $isHost ? 'host' : 'listener',
                'is_muted' => true,
                'is_forced_muted' => false,
            ]
        );

        $this->recordAndBroadcast($space, $user->id, null, 'join', ['name' => $user->name], true);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'participants' => $this->freshParticipantsData($space),
            ]);
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

        if ((int) $space->host_id === (int) $user->id) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Host mengakhiri space melalui tombol Akhiri Space.'], 422);
            }

            return redirect()->route('spaces.show', $space->slug);
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
        if (! $user || (int) $space->host_id !== (int) $user->id) {
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
     * Listener toggles request-to-speak (raise/lower hand).
     */
    public function requestSpeak(Request $request, Space|string $space): JsonResponse
    {
        $space = $this->resolveSpace($space);
        $user = Auth::user();

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        if ($space->status !== 'live') {
            return response()->json(['message' => 'Permintaan bicara hanya tersedia saat Space sedang live.'], 422);
        }

        $participant = SpaceParticipant::where('space_id', $space->id)
            ->where('user_id', $user->id)
            ->first();

        if (! $participant) {
            abort(403, 'Gabung ke Space terlebih dahulu.');
        }

        if ($participant->isOnStage()) {
            return response()->json(['message' => 'Kamu sudah berada di panggung.'], 422);
        }

        $raised = $participant->hand_raised_at === null;
        $participant->update([
            'hand_raised_at' => $raised ? now() : null,
        ]);

        $this->recordAndBroadcast($space, $user->id, null, 'state', [
            'action' => 'hand_toggle',
            'user_id' => $user->id,
            'hand_raised' => $raised,
        ], false);

        return response()->json([
            'success' => true,
            'hand_raised' => $raised,
            'participants' => $this->freshParticipantsData($space),
        ]);
    }

    /**
     * Structured stage moderation & role transitions (Host / Co-host / self step-down).
     */
    public function manageParticipantRole(Request $request, Space|string $space, User $user): JsonResponse
    {
        $space = $this->resolveSpace($space);
        $actor = Auth::user();

        if (! $actor) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        if ($space->status === 'ended') {
            return response()->json(['message' => 'Space telah berakhir.'], 422);
        }

        $data = $request->validate([
            'action' => [
                'required',
                'in:approve_speaker,decline_request,promote_speaker,demote_listener,promote_cohost,demote_cohost,step_down,mute,unmute,kick',
            ],
        ]);

        $action = $data['action'];
        $actorParticipant = $this->resolveActorParticipant($space, $actor);

        if (! $actorParticipant) {
            abort(403, 'Kamu bukan peserta Space ini.');
        }

        $targetParticipant = SpaceParticipant::where('space_id', $space->id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        if ($action === 'step_down') {
            if ((int) $actor->id !== (int) $user->id) {
                abort(403, 'Hanya diri sendiri yang dapat memilih turun ke pendengar.');
            }

            if ($targetParticipant->role === 'host') {
                return response()->json(['message' => 'Host tidak dapat turun menjadi pendengar.'], 422);
            }

            $targetParticipant->update([
                'role' => 'listener',
                'is_muted' => true,
                'is_forced_muted' => false,
                'hand_raised_at' => null,
            ]);

            $this->recordAndBroadcast($space, $actor->id, null, 'state', [
                'action' => 'step_down',
                'user_id' => $user->id,
            ], false);

            return response()->json([
                'success' => true,
                'participants' => $this->freshParticipantsData($space),
            ]);
        }

        // Moderation actions require Host or Co-host.
        if (! $actorParticipant->isModerator()) {
            abort(403, 'Hanya Host atau Co-host yang dapat mengatur panggung.');
        }

        if ((int) $user->id === (int) $space->host_id || $targetParticipant->role === 'host') {
            abort(403, 'Peran Host tidak dapat dimodifikasi.');
        }

        $actorIsHost = (int) $actor->id === (int) $space->host_id;

        if (in_array($action, ['promote_cohost', 'demote_cohost'], true) && ! $actorIsHost) {
            abort(403, 'Hanya Host yang dapat mengangkat atau mencopot Co-host.');
        }

        if ($targetParticipant->role === 'cohost' && ! $actorIsHost) {
            abort(403, 'Hanya Host yang dapat memoderasi Co-host.');
        }

        switch ($action) {
            case 'approve_speaker':
            case 'promote_speaker':
                if ($space->status !== 'live') {
                    return response()->json(['message' => 'Naik panggung hanya dapat dilakukan saat Space live.'], 422);
                }

                $stageCount = SpaceParticipant::where('space_id', $space->id)
                    ->whereIn('role', ['cohost', 'speaker'])
                    ->count();

                if (! $targetParticipant->isOnStage() && $stageCount >= Space::MAX_SPEAKERS) {
                    return response()->json(['message' => 'Kapasitas panggung speaker sudah penuh.'], 422);
                }

                $targetParticipant->update([
                    'role' => 'speaker',
                    'hand_raised_at' => null,
                    'is_muted' => true,
                    'is_forced_muted' => false,
                ]);
                break;

            case 'decline_request':
                $targetParticipant->update(['hand_raised_at' => null]);
                break;

            case 'demote_listener':
                $targetParticipant->update([
                    'role' => 'listener',
                    'is_muted' => true,
                    'is_forced_muted' => false,
                    'hand_raised_at' => null,
                ]);
                break;

            case 'promote_cohost':
                if ($targetParticipant->role !== 'cohost') {
                    $cohostCount = SpaceParticipant::where('space_id', $space->id)
                        ->where('role', 'cohost')
                        ->count();

                    if ($cohostCount >= Space::MAX_COHOSTS) {
                        return response()->json(['message' => 'Maksimal '.Space::MAX_COHOSTS.' Co-host dalam satu Space.'], 422);
                    }
                }

                $targetParticipant->update([
                    'role' => 'cohost',
                    'hand_raised_at' => null,
                    'is_forced_muted' => false,
                ]);
                break;

            case 'demote_cohost':
                $targetParticipant->update(['role' => 'speaker']);
                break;

            case 'mute':
                if (! $targetParticipant->isOnStage()) {
                    return response()->json(['message' => 'Peserta tidak berada di panggung.'], 422);
                }

                $targetParticipant->update([
                    'is_muted' => true,
                    'is_forced_muted' => true,
                ]);
                break;

            case 'unmute':
                if (! $targetParticipant->isOnStage()) {
                    return response()->json(['message' => 'Peserta tidak berada di panggung.'], 422);
                }

                $targetParticipant->update(['is_forced_muted' => false]);
                break;

            case 'kick':
                $targetParticipant->delete();

                $this->recordAndBroadcast($space, $actor->id, $user->id, 'kick', [
                    'name' => $user->name,
                ], false);

                $this->recordAndBroadcast($space, $user->id, null, 'leave', [
                    'name' => $user->name,
                    'kicked' => true,
                ], false);

                return response()->json([
                    'success' => true,
                    'participants' => $this->freshParticipantsData($space),
                ]);
        }

        $this->recordAndBroadcast($space, $actor->id, null, 'state', [
            'action' => $action,
            'user_id' => $user->id,
        ], false);

        return response()->json([
            'success' => true,
            'participants' => $this->freshParticipantsData($space),
        ]);
    }

    /**
     * Stage participant updates their own microphone state (server-enforced).
     */
    public function updateMic(Request $request, Space|string $space): JsonResponse
    {
        $space = $this->resolveSpace($space);
        $user = Auth::user();

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $data = $request->validate([
            'muted' => ['required', 'boolean'],
        ]);

        $participant = $this->resolveActorParticipant($space, $user);
        if (! $participant) {
            abort(403, 'Gabung ke Space terlebih dahulu.');
        }

        $muted = (bool) $data['muted'];
        if (! $muted) {
            if ($space->status !== 'live') {
                abort(403, 'Mikrofon hanya dapat dinyalakan saat Space sedang live.');
            }

            if (! $participant->isOnStage()) {
                abort(403, 'Pendengar harus diizinkan naik panggung sebelum menyalakan mikrofon.');
            }

            if ($participant->is_forced_muted) {
                abort(403, 'Mikrofonmu sedang dibisukan oleh Host atau Co-host.');
            }
        }

        $participant->update(['is_muted' => $muted]);

        $this->recordAndBroadcast($space, $user->id, null, 'state', [
            'action' => 'mic_toggle',
            'user_id' => $user->id,
            'is_muted' => $muted,
        ], true);

        return response()->json([
            'success' => true,
            'is_muted' => $muted,
            'participants' => $this->freshParticipantsData($space),
        ]);
    }

    /**
     * Host or Co-host pins or unpins a public project on the Space stage.
     */
    public function pinProject(Request $request, Space|string $space): JsonResponse
    {
        $space = $this->resolveSpace($space);
        $user = Auth::user();

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $actorParticipant = $this->resolveActorParticipant($space, $user);
        if (! $actorParticipant || ! $actorParticipant->isModerator()) {
            abort(403, 'Hanya Host atau Co-host yang dapat menyematkan karya.');
        }

        $data = $request->validate([
            'pinned_project_id' => ['nullable', 'integer', 'exists:projects,id'],
        ]);

        $projectId = $data['pinned_project_id'] ?? null;
        if ($projectId !== null) {
            $valid = Project::visibleTo($user->id)
                ->where('visibility', 'public')
                ->whereNull('warning_notes')
                ->whereKey($projectId)
                ->exists();

            if (! $valid) {
                return response()->json(['message' => 'Hanya karya publik aktif yang dapat disematkan.'], 422);
            }
        }

        $space->update(['pinned_project_id' => $projectId]);
        $space->load('pinnedProject.user');
        $pinned = $this->formatPinnedProject($space);

        $this->recordAndBroadcast($space, $user->id, null, 'state', [
            'action' => 'pin_project',
            'pinned_project' => $pinned,
        ], false);

        return response()->json([
            'success' => true,
            'pinned_project' => $pinned,
        ]);
    }

    /**
     * Poll pending WebRTC signals, space chat, pinned project, and participants for offline/HTTP fallback.
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
            ->where(function ($q) use ($user): void {
                $q->where(function ($sub) use ($user): void {
                    $sub->whereNull('to_user_id')
                        ->where('from_user_id', '!=', $user->id);
                })->orWhere('to_user_id', $user->id);
            })
            ->orderBy('id')
            ->take(100)
            ->get();

        $lastId = $logs->isNotEmpty()
            ? (int) $logs->max('id')
            : max($afterId, (int) (SpaceSignalLog::where('space_id', $space->id)->max('id') ?? 0));

        $space->loadMissing('pinnedProject.user');

        return response()->json([
            'status' => $space->status,
            'started_at' => $space->started_at?->toIso8601String(),
            'pinned_project' => $this->formatPinnedProject($space),
            'last_id' => $lastId,
            'signals' => $logs->map(fn (SpaceSignalLog $log): array => [
                'id' => $log->id,
                'from' => $log->from_user_id,
                'to' => $log->to_user_id,
                'kind' => $log->kind,
                'payload' => $log->payload ?? [],
            ])->values()->all(),
            'participants' => $this->freshParticipantsData($space),
        ]);
    }

    /**
     * WebRTC signaling, live chat, and quick reactions within a space.
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
            'kind' => ['required', 'in:offer,answer,ice,chat,reaction'],
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

        if ($data['kind'] === 'reaction') {
            $reactionType = (string) ($data['payload']['type'] ?? '');
            if (! in_array($reactionType, self::REACTION_TYPES, true)) {
                return response()->json(['message' => 'Jenis reaksi tidak valid.'], 422);
            }

            $log = $this->recordAndBroadcast($space, $user->id, null, 'reaction', [
                'name' => $user->name,
                'type' => $reactionType,
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
     * Resolve or auto-ensure the Host's participant record.
     */
    protected function resolveActorParticipant(Space $space, User $actor): ?SpaceParticipant
    {
        $participant = SpaceParticipant::where('space_id', $space->id)
            ->where('user_id', $actor->id)
            ->first();

        if (! $participant && (int) $space->host_id === (int) $actor->id) {
            return SpaceParticipant::create([
                'space_id' => $space->id,
                'user_id' => $actor->id,
                'role' => 'host',
                'is_muted' => true,
                'is_forced_muted' => false,
            ]);
        }

        return $participant;
    }

    /**
     * Fetch freshly formatted participant payloads for a space.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function freshParticipantsData(Space $space): array
    {
        $participants = $space->users()->orderByDesc('space_participants.created_at')->get();

        return $this->formatParticipants($space, $participants);
    }

    /**
     * Format space participants consistently for initial Blade render and JSON sync.
     *
     * @param  Collection<int, User>  $participants
     * @return array<int, array<string, mixed>>
     */
    protected function formatParticipants(Space $space, $participants): array
    {
        return $participants->map(function ($p) use ($space): array {
            $isHost = (int) $p->id === (int) $space->host_id;
            $role = $isHost ? 'host' : ($p->pivot?->role ?? 'listener');

            return [
                'id' => (int) $p->id,
                'name' => $p->name,
                'username' => $p->username,
                'avatar' => $p->avatar,
                'is_host' => $isHost,
                'role' => $role,
                'on_stage' => in_array($role, Space::STAGE_ROLES, true),
                'is_muted' => (bool) ($p->pivot?->is_muted ?? true),
                'is_forced_muted' => (bool) ($p->pivot?->is_forced_muted ?? false),
                'hand_raised' => $p->pivot?->hand_raised_at !== null,
                'hand_raised_at' => $p->pivot?->hand_raised_at,
            ];
        })->values()->all();
    }

    /**
     * Format pinned project data for Blade and JSON sync.
     *
     * @return array{id: int, title: string, slug: string, tagline: ?string, author_name: ?string}|null
     */
    protected function formatPinnedProject(Space $space): ?array
    {
        $project = $space->pinnedProject;
        if (! $project || $project->visibility !== 'public' || ! empty($project->warning_notes)) {
            return null;
        }

        return [
            'id' => (int) $project->id,
            'title' => $project->title,
            'slug' => $project->slug,
            'tagline' => $project->tagline,
            'author_name' => $project->user?->name,
        ];
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
