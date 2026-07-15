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

        if (! in_array($role, ['all', 'consumer', 'provider', 'job_seeker'], true)) {
            $role = 'all';
        }

        $query = User::whereIn('role', [User::ROLE_CONSUMER, User::ROLE_PROVIDER, User::ROLE_JOB_SEEKER])
            ->latest('id');

        if ($role !== 'all') {
            $query->where('role', $role);
        }

        if ($q !== '') {
            $term = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $q) . '%';
            $query->where(fn ($w) => $w->where('name', 'like', $term)
                ->orWhere('email', 'like', $term)
                ->orWhere('phone', 'like', $term));
        }

        $users = $query->paginate(15)->withQueryString();

        $counts = [
            'total' => User::whereIn('role', [User::ROLE_CONSUMER, User::ROLE_PROVIDER, User::ROLE_JOB_SEEKER])->count(),
            'consumers' => User::where('role', User::ROLE_CONSUMER)->count(),
            'providers' => User::where('role', User::ROLE_PROVIDER)->count(),
            'job_seekers' => User::where('role', User::ROLE_JOB_SEEKER)->count(),
            'suspended' => User::whereNotNull('suspended_at')->count(),
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
}