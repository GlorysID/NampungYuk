<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\NewFollowerNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FollowController extends Controller
{
    /**
     * Toggle follow/unfollow a user.
     */
    public function toggle(Request $request, User $user): JsonResponse|RedirectResponse
    {
        $authUser = Auth::user();

        if (! $authUser) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            return redirect()->route('login');
        }

        if ($authUser->id === $user->id) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Tidak bisa mengikuti diri sendiri.'], 422);
            }

            return back()->with('error', 'Tidak bisa mengikuti diri sendiri.');
        }

        $isFollowing = $authUser->following()->whereKey($user->id)->exists();

        if ($isFollowing) {
            $authUser->following()->detach($user->id);
            $nowFollowing = false;
            $message = 'Berhenti mengikuti @'.$user->username;
        } else {
            $authUser->following()->attach($user->id);
            $nowFollowing = true;
            $message = 'Kamu sekarang mengikuti @'.$user->username;

            // Notify the followed user.
            $user->notify(new NewFollowerNotification($authUser));
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'following' => $nowFollowing,
                'followers_count' => $user->followers()->count(),
                'message' => $message,
            ]);
        }

        return back()->with('success', $message);
    }
}
