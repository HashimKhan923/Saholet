<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $notifications = Notification::where('user_id', $request->user()->id)
            ->latest('id')
            ->paginate(15);

        return view('notifications.index', compact('notifications'));
    }

    /**
     * Polled from the dashboard every minute as a fallback for when the websocket
     * connection (Echo/Reverb) isn't live — same payload shape as the
     * NotificationCreated broadcast event so the client can feed it through the
     * same `push()` handler either way.
     */
    public function poll(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $latest = Notification::where('user_id', $userId)
            ->latest('id')
            ->limit(10)
            ->get()
            ->map(fn (Notification $n) => [
                'id' => $n->id,
                'type' => $n->type,
                'title' => $n->title,
                'body' => $n->body,
                'url' => $n->url,
                'created_at' => $n->created_at->format('d M, g:i A'),
            ]);

        return response()->json([
            'unread_count' => Notification::where('user_id', $userId)->whereNull('read_at')->count(),
            'latest' => $latest,
        ]);
    }

    public function read(Request $request, Notification $notification): RedirectResponse
    {
        $this->authorize('markRead', $notification);

        if (! $notification->isRead()) {
            $notification->update(['read_at' => now()]);
        }

        return redirect($notification->url ?: route('notifications.index'));
    }

    public function readAll(Request $request): RedirectResponse
    {
        Notification::where('user_id', $request->user()->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return back()->with('success', 'All notifications marked as read.');
    }
}