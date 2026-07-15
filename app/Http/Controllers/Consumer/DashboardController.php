<?php

namespace App\Http\Controllers\Consumer;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $bookings = Booking::with(['service', 'providerProfile.user'])
            ->where('consumer_id', $request->user()->id)
            ->latest()
            ->limit(5)
            ->get();

        return view('consumer.dashboard', compact('bookings'));
    }
}