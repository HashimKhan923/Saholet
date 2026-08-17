<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $role = $request->query('role', 'all');
        $q = trim((string) $request->query('q', ''));

        if (! in_array($role, ['all', 'consumer', 'provider', 'job_seeker', 'deleted'], true)) {
            $role = 'all';
        }

        $query = User::whereIn('role', [User::ROLE_CONSUMER, User::ROLE_PROVIDER, User::ROLE_JOB_SEEKER])
            ->latest('id');

        // Deleted accounts are anonymized, not removed — they only ever show up
        // under the "Deleted" tab, never mixed into the normal role tabs.
        if ($role === 'deleted') {
            $query->where('email', 'like', 'deleted-%');
        } else {
            $query->where('email', 'not like', 'deleted-%');

            if ($role !== 'all') {
                $query->where('role', $role);
            }
        }

        if ($q !== '') {
            $term = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $q) . '%';
            $query->where(fn ($w) => $w->where('name', 'like', $term)
                ->orWhere('email', 'like', $term)
                ->orWhere('phone', 'like', $term));
        }

        $users = $query->paginate(15)->withQueryString();

        $counts = [
            'total' => User::whereIn('role', [User::ROLE_CONSUMER, User::ROLE_PROVIDER, User::ROLE_JOB_SEEKER])->where('email', 'not like', 'deleted-%')->count(),
            'consumers' => User::where('role', User::ROLE_CONSUMER)->where('email', 'not like', 'deleted-%')->count(),
            'providers' => User::where('role', User::ROLE_PROVIDER)->where('email', 'not like', 'deleted-%')->count(),
            'job_seekers' => User::where('role', User::ROLE_JOB_SEEKER)->where('email', 'not like', 'deleted-%')->count(),
            'suspended' => User::whereNotNull('suspended_at')->where('email', 'not like', 'deleted-%')->count(),
            'deleted' => User::where('email', 'like', 'deleted-%')->count(),
        ];

        return view('admin.users.index', compact('users', 'role', 'q', 'counts'));
    }

    public function suspend(User $user): RedirectResponse
    {
        if (! $user->canBeSuspended()) {
            return back()->with('error', 'This account cannot be suspended.');
        }

        if ($user->isSuspended()) {
            return back()->with('error', 'This account is already suspended.');
        }

        $user->update(['suspended_at' => now()]);

        return back()->with('success', $user->name . ' has been suspended.');
    }

    public function unsuspend(User $user): RedirectResponse
    {
        if (! $user->isSuspended()) {
            return back()->with('error', 'This account is not suspended.');
        }

        $user->update(['suspended_at' => null]);

        return back()->with('success', $user->name . ' has been reinstated.');
    }

    /**
     * Anonymizes rather than hard-deletes — every booking/payment/wallet/review
     * FK cascades from users, so a real delete would destroy that history too.
     */
    public function destroy(User $user): RedirectResponse
    {
        if (! $user->canBeDeleted()) {
            return back()->with('error', 'This account cannot be deleted.');
        }

        if ($user->isDeleted()) {
            return back()->with('error', 'This account has already been deleted.');
        }

        $name = $user->name;
        $user->anonymize();

        return back()->with('success', $name . ' has been deleted.');
    }
}