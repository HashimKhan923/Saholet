<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Kreait\Firebase\Contract\Auth as FirebaseAuth;
use Kreait\Firebase\Exception\Auth\FailedToVerifyToken;

/**
 * Verifies a Firebase phone-auth ID token server-side and extracts the phone
 * number it proves. The frontend/mobile client does the actual OTP send +
 * confirm via the Firebase SDK; this is the one thing the backend must
 * independently re-check rather than trusting the client's word for it.
 */
class FirebasePhoneVerifier
{
    public function __construct(private readonly FirebaseAuth $auth) {}

    /** @return string the verified phone number, in E.164 format (e.g. "+923001234567") */
    public function verify(string $idToken): string
    {
        try {
            $token = $this->auth->verifyIdToken($idToken);
        } catch (FailedToVerifyToken $e) {
            Log::info('Firebase phone verification failed', ['reason' => $e->getMessage()]);

            throw ValidationException::withMessages([
                'firebase_id_token' => ['Phone verification failed or expired. Please verify your number again.'],
            ]);
        }

        $phone = $token->claims()->get('phone_number');

        if (! $phone) {
            throw ValidationException::withMessages([
                'firebase_id_token' => ['This verification is not tied to a phone number.'],
            ]);
        }

        return $phone;
    }
}
