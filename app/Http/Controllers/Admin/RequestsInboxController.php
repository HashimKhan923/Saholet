<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CareerApplication;
use App\Models\Contract;
use App\Models\Dispute;
use App\Models\ProviderProfile;
use App\Models\WithdrawalRequest;
use Illuminate\View\View;

/**
 * One screen for everything an admin needs to act on today, instead of
 * hunting across four separate sections. Read-only aggregation — every
 * action link routes back to the existing dedicated controller/view.
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

        $totalPending = $pendingProviders->count() + $openDisputes->count()
            + $submittedContracts->count() + $newApplications->count() + $pendingWithdrawals->count();

        return view('admin.requests.index', compact(
            'pendingProviders', 'openDisputes', 'submittedContracts', 'newApplications', 'pendingWithdrawals', 'totalPending'
        ));
    }
}
