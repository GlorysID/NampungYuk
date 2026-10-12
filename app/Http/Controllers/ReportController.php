<?php

namespace App\Http\Controllers;

use App\Models\CommunityPost;
use App\Models\Project;
use App\Models\Report;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * Store a newly created report.
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'reportable_type' => ['required', 'string', 'in:project,user,community_post'],
            'reportable_id' => ['required', 'integer'],
            'reason' => ['required', 'string', 'in:spam,illegal_gambling,copyright,harassment,broken_demo,other'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $modelClass = match ($validated['reportable_type']) {
            'project' => Project::class,
            'user' => User::class,
            'community_post' => CommunityPost::class,
        };

        $target = $modelClass::findOrFail($validated['reportable_id']);

        // Prevent self-reporting
        if ($modelClass === User::class && $target->id === $request->user()->id) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Anda tidak dapat melaporkan akun Anda sendiri.'], 422);
            }

            return back()->with('error', 'Anda tidak dapat melaporkan akun Anda sendiri.');
        }

        if ($modelClass === Project::class && $target->user_id === $request->user()->id) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Anda tidak dapat melaporkan proyek Anda sendiri.'], 422);
            }

            return back()->with('error', 'Anda tidak dapat melaporkan proyek Anda sendiri.');
        }

        // Avoid spamming duplicate pending reports from same user
        $existing = Report::where('reporter_id', $request->user()->id)
            ->where('reportable_type', $modelClass)
            ->where('reportable_id', $target->id)
            ->where('status', 'pending')
            ->first();

        if ($existing) {
            $msg = 'Laporan Anda sebelumnya untuk konten ini sedang ditinjau oleh Admin.';
            if ($request->wantsJson()) {
                return response()->json(['message' => $msg, 'already_reported' => true], 200);
            }

            return back()->with('status', $msg);
        }

        Report::create([
            'reporter_id' => $request->user()->id,
            'reportable_type' => $modelClass,
            'reportable_id' => $target->id,
            'reason' => $validated['reason'],
            'notes' => $validated['notes'] ?? $request->input('details'),
            'status' => 'pending',
        ]);

        $successMsg = 'Terima kasih! Laporan Anda telah diterima dan akan segera ditinjau oleh Administrator.';
        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => $successMsg]);
        }

        return back()->with('status', $successMsg);
    }
}
