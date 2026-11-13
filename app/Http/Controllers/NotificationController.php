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

        // Notifikasi TIDAK ditandai dibaca hanya karena halaman dibuka: status
        // belum-dibaca hanya hilang saat notifikasi itu diklik, atau lewat
        // tombol "Tandai semua dibaca".
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

        $target = (string) $request->input('to', '');
        $parts = parse_url($target);

        // Lanjut ke tujuan notifikasi (dari payload), hanya kalau host-nya host
        // aplikasi ini — dan pakai path relatif supaya user tetap di host yang
        // dia buka (payload bisa berisi host APP_URL yang beda dari request).
        $allowedHosts = array_filter([$request->getHost(), parse_url((string) config('app.url'), PHP_URL_HOST)]);

        if (! empty($parts['path']) && (empty($parts['host']) || in_array($parts['host'], $allowedHosts, true))) {
            $path = $parts['path'].(isset($parts['query']) ? '?'.$parts['query'] : '');

            return redirect()->to($path);
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
