<?php

namespace App\Http\Controllers\Api\Consumer;

use App\Http\Controllers\Controller;
use App\Http\Resources\BookingResource;
use App\Models\Booking;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /** The consumer's 5 most recent bookings — a quick home-screen summary. */
    public function index(Request $request): JsonResponse
    {
        $bookings = Booking::with(['service', 'providerProfile.user'])
            ->where('consumer_id', $request->user()->id)
            ->latest()
            ->limit(5)
            ->get();

        return response()->json(['recent_bookings' => BookingResource::collection($bookings)]);
    }
}
