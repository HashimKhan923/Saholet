<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\FirebasePhoneVerifier;
use App\Services\ReferralService;
use App\Support\PakFormat;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /** Pre-flight validation only — no user is created. Call before starting Firebase phone verification, so a taken email/phone doesn't waste an OTP send. */
    public function check(Request $request): JsonResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:20'],
            'role' => ['required', Rule::in(['consumer', 'provider', 'job_seeker'])],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'referral_code' => ['nullable', 'string', 'max:12'],
        ]);

        return response()->json(['ok' => true]);
    }

    /**
     * Register a new account.
     *
     * Roles: consumer, provider, job_seeker. The phone number must already be
     * verified client-side via the Firebase SDK (signInWithPhoneNumber); the
     * resulting ID token is re-verified here before the account is created.
     */
    public function register(Request $request, ReferralService $referrals, FirebasePhoneVerifier $verifier): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:20'],
            'role' => ['required', Rule::in(['consumer', 'provider', 'job_seeker'])],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'referral_code' => ['nullable', 'string', 'max:12'],
            'device_name' => ['required', 'string', 'max:255'],
            'firebase_id_token' => ['required', 'string'],
        ]);

        $verifiedPhone = $verifier->verify($validated['firebase_id_token']);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => PakFormat::phone($verifiedPhone),
            'role' => $validated['role'],
            'password' => $validated['password'],
            'referral_code' => User::generateUniqueReferralCode(),
        ]);

        $user->forceFill(['phone_verified_at' => now()])->save();

        $referrals->captureReferral($user, $validated['referral_code'] ?? null);

        event(new Registered($user));

        $token = $user->createToken($validated['device_name'])->plainTextToken;

        return response()->json([
            'user' => new UserResource($user),
            'token' => $token,
        ], 201);
    }

    /**
     * Log in with email + password, returning a bearer token for this device.
     */
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['required', 'string', 'max:255'],
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['These credentials do not match our records.'],
            ]);
        }

        if ($user->isSuspended()) {
            throw ValidationException::withMessages([
                'email' => ['Your account has been suspended. Please contact support.'],
            ]);
        }

        $token = $user->createToken($credentials['device_name'])->plainTextToken;

        return response()->json([
            'user' => new UserResource($user),
            'token' => $token,
        ]);
    }

    /** Revoke the bearer token used for this request (log out this device only). */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out.']);
    }

    /** Revoke every token for this user (log out all devices). */
    public function logoutAll(Request $request): JsonResponse
    {
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'Logged out of all devices.']);
    }

    /** The authenticated user, with role and provider status if applicable. */
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'user' => new UserResource($request->user()->load('providerProfile')),
        ]);
    }
}
