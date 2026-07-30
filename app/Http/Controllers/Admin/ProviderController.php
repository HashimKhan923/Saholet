<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\LedgerEntry;
use App\Models\ProviderProfile;
use App\Models\WithdrawalRequest;
use App\Services\WalletService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProviderController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status', 'all');

        if (! in_array($status, ['pending', 'approved', 'rejected', 'all'], true)) {
            $status = 'all';
        }

        $query = ProviderProfile::with('user')
            ->orderByRaw('submitted_at IS NULL')
            ->latest('submitted_at')
            ->latest('updated_at');

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $profiles = $query->paginate(15)->withQueryString();

        $counts = [
            'pending' => ProviderProfile::where('status', ProviderProfile::STATUS_PENDING)->count(),
            'approved' => ProviderProfile::where('status', ProviderProfile::STATUS_APPROVED)->count(),
            'rejected' => ProviderProfile::where('status', ProviderProfile::STATUS_REJECTED)->count(),
        ];

        return view('admin.providers.index', compact('profiles', 'status', 'counts'));
    }

    public function show(ProviderProfile $provider, WalletService $wallets): View
    {
        $provider->load([
            'user',
            'documents',
            'reviewer',
            'providerServices.service.category',
            'portfolioPhotos',
            'reviews' => fn ($q) => $q->latest()->limit(5)->with('consumer:id,name'),
            'withdrawalRequests' => fn ($q) => $q->latest()->limit(10),
        ]);

        $bookings = $provider->bookings()
            ->with(['service:id,name', 'consumer:id,name'])
            ->latest('scheduled_date')
            ->limit(10)
            ->get();

        $bookingCounts = [
            'total' => $provider->bookings()->count(),
            'completed' => $provider->bookings()->where('status', Booking::STATUS_COMPLETED)->count(),
            'active' => $provider->bookings()->whereIn('status', Booking::ACTIVE_STATUSES)->count(),
            'cancelled' => $provider->bookings()->where('status', Booking::STATUS_CANCELLED)->count(),
        ];

        $completedValue = (float) $provider->bookings()
            ->where('status', Booking::STATUS_COMPLETED)
            ->sum('price');

        $wallet = $wallets->walletFor($provider->user);

        $lifetimeEarned = (float) $wallet->entries()
            ->where('bucket', LedgerEntry::BUCKET_AVAILABLE)
            ->where('amount', '>', 0)
            ->sum('amount');

        $totalWithdrawn = (float) $provider->withdrawalRequests()
            ->where('status', WithdrawalRequest::STATUS_PAID)
            ->sum('amount');

        $earnings = [
            'lifetime_earned' => $lifetimeEarned,
            'available_balance' => (float) $wallet->available_balance,
            'escrow_balance' => (float) $wallet->escrow_balance,
            'total_withdrawn' => $totalWithdrawn,
            'completed_value' => $completedValue,
        ];

        return view('admin.providers.show', compact('provider', 'bookings', 'bookingCounts', 'earnings'));
    }

    public function approve(ProviderProfile $provider): RedirectResponse
    {
        if (! $provider->isPending()) {
            return back()->with('error', 'Only pending applications can be approved.');
        }

        $provider->update([
                    'status' => ProviderProfile::STATUS_APPROVED,
                    'reviewed_at' => now(),
                    'reviewed_by' => auth()->id(),
                    'rejection_reason' => null,
                ]);

                app(\App\Services\Notifier::class)->notify(
                    $provider->user,
                    'provider',
                    'You’re verified',
                    'Your provider application has been approved. You can now list services and accept bookings.',
                    route('provider.dashboard')
                );

                return back()->with('success', 'Provider approved.');
    }

    public function reject(Request $request, ProviderProfile $provider): RedirectResponse
    {
        if (! $provider->isPending()) {
            return back()->with('error', 'Only pending applications can be rejected.');
        }

        $data = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:1000'],
        ]);

        $provider->update([
            'status' => ProviderProfile::STATUS_REJECTED,
            'rejection_reason' => $data['rejection_reason'],
            'reviewed_at' => now(),
            'reviewed_by' => auth()->id(),
        ]);

        app(\App\Services\Notifier::class)->notify(
            $provider->user,
            'provider',
            'Application needs changes',
            'Your provider application was not approved. Please review the feedback and resubmit.',
            route('provider.onboarding')
        );

        return back()->with('success', 'Provider application rejected.');
    }
}