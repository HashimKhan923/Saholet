<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $notifications = Notification::where('user_id', $request->user()->id)
            ->latest('id')
            ->paginate(20);

        return response()->json([
            'notifications' => NotificationResource::collection($notifications),
            'unread_count' => Notification::where('user_id', $request->user()->id)->whereNull('read_at')->count(),
            'pagination' => [
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
                'total' => $notifications->total(),
            ],
        ]);
    }

    public function read(Request $request, Notification $notification): JsonResponse
    {
        $this->authorize('markRead', $notification);

        if (! $notification->isRead()) {
            $notification->update(['read_at' => now()]);
        }

        return response()->json(['notification' => new NotificationResource($notification)]);
    }

    public function readAll(Request $request): JsonResponse
    {
        Notification::where('user_id', $request->user()->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['message' => 'All notifications marked as read.']);
    }
}
