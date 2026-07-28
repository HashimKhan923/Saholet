<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CareerApplication;
use App\Models\Contract;
use App\Models\Dispute;
use App\Models\EmergencyRequest;
use App\Models\ProviderProfile;
use App\Models\Subscription;
use App\Models\WithdrawalRequest;
use Illuminate\View\View;

/**
 * One screen for everything an admin needs to act on today, instead of
 * hunting across separate sections. Read-only aggregation — every
 * action link routes back to the existing dedicated controller/view.
 *
 * The set of sections queried here must stay in sync with the sidebar's
 * "Requests" badge total (computed in AppServiceProvider) — that badge is
 * the sum of every section below, so adding a source here without also
 * adding it there (or vice versa) reintroduces a badge/content mismatch.
 */
class RequestsInboxController extends Controller
{
    public function index(): View
    {
        $pendingProviders = ProviderProfile::where('status', ProviderProfile::STATUS_PENDING)
            ->with('user')
            ->oldest('submitted_at')
            ->get();

        $openDisputes = Dispute::where('status', Dispute::STATUS_OPEN)
            ->with('booking')
            ->oldest()
            ->get();

        $submittedContracts = Contract::where('status', Contract::STATUS_SUBMITTED)
            ->with('consumer')
            ->oldest()
            ->get();

        $newApplications = CareerApplication::where('status', CareerApplication::STATUS_SUBMITTED)
            ->with(['listing', 'jobSeeker'])
            ->oldest()
            ->get();

        $pendingWithdrawals = WithdrawalRequest::where('status', WithdrawalRequest::STATUS_PENDING)
            ->with('providerProfile.user')
            ->oldest()
            ->get();

        $pendingSubscriptions = Subscription::where('status', Subscription::STATUS_PENDING_ASSIGNMENT)
            ->with(['consumer:id,name', 'plan:id,name'])
            ->oldest()
            ->get();

        $openEmergencies = EmergencyRequest::where('status', EmergencyRequest::STATUS_OPEN)
            ->with(['consumer:id,name', 'service:id,name'])
            ->oldest()
            ->get();

        $totalPending = $pendingProviders->count() + $openDisputes->count()
            + $submittedContracts->count() + $newApplications->count() + $pendingWithdrawals->count()
            + $pendingSubscriptions->count() + $openEmergencies->count();

        return view('admin.requests.index', compact(
            'pendingProviders', 'openDisputes', 'submittedContracts', 'newApplications', 'pendingWithdrawals',
            'pendingSubscriptions', 'openEmergencies', 'totalPending'
        ));
    }
}
