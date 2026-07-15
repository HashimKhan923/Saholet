<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\CareerApplication;
use App\Models\CareerListing;
use App\Models\Contract;
use App\Models\Dispute;
use App\Models\JobPost;
use App\Models\Payment;
use App\Models\ProviderProfile;
use App\Models\Review;
use App\Models\User;
use Illuminate\View\View;

class AnalyticsController extends Controller
{
    public function index(): View
    {
        $users = [
            'total' => User::count(),
            'consumers' => User::where('role', User::ROLE_CONSUMER)->count(),
            'providers' => User::where('role', User::ROLE_PROVIDER)->count(),
            'suspended' => User::whereNotNull('suspended_at')->count(),
        ];

        $providers = [
            'approved' => ProviderProfile::where('status', ProviderProfile::STATUS_APPROVED)->count(),
            'pending' => ProviderProfile::where('status', ProviderProfile::STATUS_PENDING)->count(),
            'rejected' => ProviderProfile::where('status', ProviderProfile::STATUS_REJECTED)->count(),
            'draft' => ProviderProfile::where('status', ProviderProfile::STATUS_DRAFT)->count(),
        ];

        $bookingCounts = Booking::selectRaw('status, COUNT(*) c')->groupBy('status')->pluck('c', 'status');
        $bookings = [
            'total' => (int) $bookingCounts->sum(),
            'pending' => (int) ($bookingCounts['pending'] ?? 0),
            'confirmed' => (int) ($bookingCounts['confirmed'] ?? 0),
            'in_progress' => (int) ($bookingCounts['in_progress'] ?? 0),
            'completed' => (int) ($bookingCounts['completed'] ?? 0),
            'cancelled' => (int) ($bookingCounts['cancelled'] ?? 0),
        ];

        $money = [
            'gmv_completed' => (float) Booking::where('status', Booking::STATUS_COMPLETED)->sum('price'),
            'escrow_held' => (float) Payment::where('status', Payment::STATUS_ESCROW)->sum('amount'),
            'released' => (float) Payment::where('status', Payment::STATUS_RELEASED)->sum('amount'),
            'refunded' => (float) Payment::where('status', Payment::STATUS_REFUNDED)->sum('amount'),
            'commission_earned' => (float) Payment::where('status', Payment::STATUS_RELEASED)->sum('commission_amount'),
        ];

        $reviews = [
            'count' => Review::count(),
            'avg' => round((float) Review::avg('rating'), 2),
        ];

        $disputes = [
            'open' => Dispute::where('status', Dispute::STATUS_OPEN)->count(),
            'resolved' => Dispute::where('status', Dispute::STATUS_RESOLVED)->count(),
            'dismissed' => Dispute::where('status', Dispute::STATUS_DISMISSED)->count(),
        ];

        $openJobs = JobPost::where('status', JobPost::STATUS_OPEN)->count();

        $contractCounts = Contract::selectRaw('status, COUNT(*) c')->groupBy('status')->pluck('c', 'status');
        $contractsSubmittedOrLater = (int) $contractCounts->sum();
        $contractsAcceptedOrLater = (int) $contractCounts->only([
            Contract::STATUS_ACCEPTED, Contract::STATUS_IN_PROGRESS, Contract::STATUS_COMPLETED,
        ])->sum();
        $contracts = [
            'submitted' => (int) ($contractCounts[Contract::STATUS_SUBMITTED] ?? 0),
            'quoted' => (int) ($contractCounts[Contract::STATUS_QUOTED] ?? 0),
            'accepted' => (int) ($contractCounts[Contract::STATUS_ACCEPTED] ?? 0),
            'in_progress' => (int) ($contractCounts[Contract::STATUS_IN_PROGRESS] ?? 0),
            'completed' => (int) ($contractCounts[Contract::STATUS_COMPLETED] ?? 0),
            'rejected' => (int) ($contractCounts[Contract::STATUS_REJECTED] ?? 0),
            'cancelled' => (int) ($contractCounts[Contract::STATUS_CANCELLED] ?? 0),
            'total' => $contractsSubmittedOrLater,
            'quoted_value' => (float) Contract::whereNotNull('quoted_total')->sum('quoted_total'),
            'acceptance_rate' => $contractsSubmittedOrLater > 0
                ? (int) round($contractsAcceptedOrLater / $contractsSubmittedOrLater * 100)
                : null,
        ];

        $listingCounts = CareerListing::selectRaw('status, COUNT(*) c')->groupBy('status')->pluck('c', 'status');
        $applicationCounts = CareerApplication::selectRaw('status, COUNT(*) c')->groupBy('status')->pluck('c', 'status');
        $applicationsTotal = (int) $applicationCounts->sum();
        $careers = [
            'open_listings' => (int) ($listingCounts[CareerListing::STATUS_OPEN] ?? 0),
            'closed_listings' => (int) ($listingCounts[CareerListing::STATUS_CLOSED] ?? 0) + (int) ($listingCounts[CareerListing::STATUS_FILLED] ?? 0),
            'applications_total' => $applicationsTotal,
            'submitted' => (int) ($applicationCounts[CareerApplication::STATUS_SUBMITTED] ?? 0),
            'shortlisted' => (int) ($applicationCounts[CareerApplication::STATUS_SHORTLISTED] ?? 0) + (int) ($applicationCounts[CareerApplication::STATUS_INTERVIEW] ?? 0),
            'hired' => (int) ($applicationCounts[CareerApplication::STATUS_HIRED] ?? 0),
            'rejected' => (int) ($applicationCounts[CareerApplication::STATUS_REJECTED] ?? 0),
            'withdrawn' => (int) ($applicationCounts[CareerApplication::STATUS_WITHDRAWN] ?? 0),
            'hire_rate' => $applicationsTotal > 0
                ? (int) round(((int) ($applicationCounts[CareerApplication::STATUS_HIRED] ?? 0)) / $applicationsTotal * 100)
                : null,
        ];

        // 14-day booking trend
        $since = now()->subDays(13)->startOfDay();
        $rows = Booking::where('created_at', '>=', $since)
            ->selectRaw('DATE(created_at) d, COUNT(*) c')
            ->groupBy('d')
            ->pluck('c', 'd');

        $trend = [];
        for ($i = 0; $i < 14; $i++) {
            $day = $since->copy()->addDays($i);
            $trend[] = [
                'label' => $day->format('d M'),
                'count' => (int) ($rows[$day->toDateString()] ?? 0),
            ];
        }
        $trendMax = max(1, max(array_column($trend, 'count')));

        return view('admin.analytics.index', compact(
            'users', 'providers', 'bookings', 'money', 'reviews', 'disputes', 'openJobs', 'trend', 'trendMax',
            'contracts', 'careers'
        ));
    }
}