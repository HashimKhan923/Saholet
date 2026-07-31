<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\OtpPasswordResetMail;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;

/**
 * Mirrors the web app's OTP-based reset (see Auth\PasswordResetLinkController
 * and Auth\NewPasswordController) so mobile shares the same password_reset_tokens
 * table, email, and rate limiting instead of a link-based flow that needs a browser.
 */
class PasswordResetController extends Controller
{
    private const OTP_EXPIRE_MINUTES = 15;
    private const MAX_ATTEMPTS = 5;

    /** Email a 6-digit OTP to reset the account's password. */
    public function sendOtp(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'email' => ["We can't find a user with that email address."],
            ]);
        }

        $code = (string) random_int(100000, 999999);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            ['token' => Hash::make($code), 'created_at' => now()]
        );

        Mail::to($user->email)->send(new OtpPasswordResetMail($code, self::OTP_EXPIRE_MINUTES));

        return response()->json([
            'message' => 'We sent a 6-digit code to your email.',
        ]);
    }

    /** Verify the OTP and set a new password. */
    public function reset(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email'],
            'otp' => ['required', 'digits:6'],
            'password' => ['required', 'string', 'confirmed', PasswordRule::min(8)],
        ]);

        $throttleKey = 'password-otp:' . mb_strtolower($validated['email']) . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, self::MAX_ATTEMPTS)) {
            throw ValidationException::withMessages([
                'otp' => ['Too many attempts. Please request a new code and try again shortly.'],
            ]);
        }

        $record = DB::table('password_reset_tokens')->where('email', $validated['email'])->first();
        $expired = ! $record || Carbon::parse($record->created_at)->addMinutes(self::OTP_EXPIRE_MINUTES)->isPast();

        if (! $record || $expired || ! Hash::check($validated['otp'], $record->token)) {
            RateLimiter::hit($throttleKey, 300);

            throw ValidationException::withMessages([
                'otp' => ['That code is invalid or has expired.'],
            ]);
        }

        $user = User::where('email', $validated['email'])->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'email' => ["We can't find a user with that email address."],
            ]);
        }

        RateLimiter::clear($throttleKey);

        $user->forceFill([
            'password' => Hash::make($validated['password']),
            'remember_token' => Str::random(60),
        ])->save();

        DB::table('password_reset_tokens')->where('email', $validated['email'])->delete();

        event(new PasswordReset($user));

        return response()->json(['message' => 'Your password has been reset — please log in.']);
    }
}
