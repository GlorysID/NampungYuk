<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class NotificationController extends Controller
{
    /**
     * Display the full notifications page.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();

        $notifications = $user->notifications()->paginate(20);

        // Mark all as read when opening the full page.
        $user->unreadNotifications->markAsRead();

        return view('notifications.index', compact('notifications'));
    }

    /**
     * Return the latest notifications as JSON (for header dropdown / polling).
     */
    public function latest(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $items = $user->notifications()
            ->latest()
            ->take(10)
            ->get()
            ->map(fn ($n) => [
                'id' => $n->id,
                'data' => $n->data,
                'read' => ! is_null($n->read_at),
                'created_at' => $n->created_at->diffForHumans(),
            ]);

        return response()->json([
            'unread_count' => $user->unreadNotifications()->count(),
            'notifications' => $items,
        ]);
    }

    /**
     * Mark a single notification as read.
     */
    public function markAsRead(Request $request, string $id): JsonResponse|RedirectResponse
    {
        $user = Auth::user();
        $notification = $user->notifications()->whereKey($id)->first();

        if ($notification) {
            $notification->markAsRead();
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'unread_count' => $user->unreadNotifications()->count(),
            ]);
        }

        return back();
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead(Request $request): JsonResponse|RedirectResponse
    {
        $user = Auth::user();
        $user->unreadNotifications->markAsRead();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'unread_count' => 0,
            ]);
        }

        return back();
    }
}
