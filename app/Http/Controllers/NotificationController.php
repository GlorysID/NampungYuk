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

        // Ambil semua notifikasi (dibaca & belum dibaca) untuk ditampilkan di riwayat
        $notifications = $user->notifications()->paginate(20);

        // Standar sosmed: saat halaman notifikasi dibuka, badge angka di lonceng & sidebar
        // langsung hilang (ditandai sudah dilihat/dibaca), tapi riwayat notifikasinya
        // tetap tersimpan utuh di list.
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
