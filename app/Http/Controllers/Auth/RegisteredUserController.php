<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ReferralService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(Request $request): View
    {
        return view('auth.register', ['ref' => $request->query('ref')]);
    }

    public function store(Request $request, ReferralService $referrals): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:20'],
            'role' => ['required', 'in:consumer,provider,job_seeker'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'referral_code' => ['nullable', 'string', 'max:12'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'role' => $validated['role'],
            'password' => $validated['password'], // hashed via model cast
            'referral_code' => User::generateUniqueReferralCode(),
        ]);

        $referrals->captureReferral($user, $validated['referral_code'] ?? null);

        event(new Registered($user));

        Auth::login($user);

        return redirect()->route($user->dashboardRoute());
    }
}