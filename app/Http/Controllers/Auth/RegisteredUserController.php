<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\FirebasePhoneVerifier;
use App\Services\ReferralService;
use App\Support\PakFormat;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(Request $request): View
    {
        return view('auth.register', ['ref' => $request->query('ref')]);
    }

    /** Pre-flight validation only — no user is created. Lets the frontend catch a taken email/phone before it spends an OTP send on Firebase. */
    public function check(Request $request): JsonResponse
    {
        $this->baseRules($request);

        return response()->json(['ok' => true]);
    }

    /** Called only after the frontend has already confirmed the phone via Firebase — {@see FirebasePhoneVerifier}. */
    public function store(Request $request, ReferralService $referrals, FirebasePhoneVerifier $verifier): JsonResponse
    {
        $validated = $this->baseRules($request);
        $validated['firebase_id_token'] = $request->validate([
            'firebase_id_token' => ['required', 'string'],
        ])['firebase_id_token'];

        $verifiedPhone = $verifier->verify($validated['firebase_id_token']);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => PakFormat::phone($verifiedPhone),
            'role' => $validated['role'],
            'password' => $validated['password'], // hashed via model cast
            'referral_code' => User::generateUniqueReferralCode(),
        ]);

        $user->forceFill(['phone_verified_at' => now()])->save();

        $referrals->captureReferral($user, $validated['referral_code'] ?? null);

        event(new Registered($user));

        Auth::login($user);

        return response()->json(['redirect' => route($user->dashboardRoute())]);
    }

    /** @return array{name: string, email: string, phone: string, role: string, password: string, referral_code: ?string} */
    private function baseRules(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:20'],
            'role' => ['required', 'in:consumer,provider,job_seeker'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'referral_code' => ['nullable', 'string', 'max:12'],
        ]);
    }
}