<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProviderProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProviderController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status', 'pending');

        if (! in_array($status, ['pending', 'approved', 'rejected', 'all'], true)) {
            $status = 'pending';
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

    public function show(ProviderProfile $provider): View
    {
        $provider->load(['user', 'documents', 'reviewer']);

        return view('admin.providers.show', compact('provider'));
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