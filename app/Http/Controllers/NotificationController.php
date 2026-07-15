<?php

namespace App\Http\Controllers;

use App\Models\Notification;
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