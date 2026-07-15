<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Dispute;
use App\Models\Payment;
use App\Models\ProviderProfile;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $pendingProviders = ProviderProfile::where('status', ProviderProfile::STATUS_PENDING)->count();
        $openDisputes = Dispute::where('status', Dispute::STATUS_OPEN)->count();

        $metrics = [
            'providers_approved' => ProviderProfile::where('status', ProviderProfile::STATUS_APPROVED)->count(),
            'bookings_total' => Booking::count(),
            'commission_earned' => (float) Payment::where('status', Payment::STATUS_RELEASED)->sum('commission_amount'),
        ];

        return view('admin.dashboard', compact('pendingProviders', 'openDisputes', 'metrics'));
    }
}