<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\ReferralReward;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Word-of-mouth growth loop: a referred user's first cleared payment pays out
 * a one-time credit to both sides. Idempotent via the unique referred_user_id
 * column on referral_rewards — a payment can never trigger a double reward
 * even if finalization is retried.
 */
class ReferralService
{
    public function captureReferral(User $newUser, ?string $code): void
    {
        if (! $code) {
            return;
        }

        $referrer = User::where('referral_code', strtoupper($code))->first();

        if (! $referrer || $referrer->id === $newUser->id) {
            return;
        }

        $newUser->update(['referred_by' => $referrer->id]);
    }

    public function rewardFirstPayment(Payment $payment): void
    {
        $payment->loadMissing('consumer');
        $consumer = $payment->consumer;

        if (! $consumer || ! $consumer->referred_by) {
            return;
        }

        if (ReferralReward::where('referred_user_id', $consumer->id)->exists()) {
            return;
        }

        $referrer = User::find($consumer->referred_by);

        if (! $referrer) {
            return;
        }

        $referrerReward = (float) config('referrals.referrer_reward');
        $referredReward = (float) config('referrals.referred_reward');

        DB::transaction(function () use ($referrer, $consumer, $referrerReward, $referredReward) {
            ReferralReward::create([
                'referrer_id' => $referrer->id,
                'referred_user_id' => $consumer->id,
                'referrer_reward' => $referrerReward,
                'referred_reward' => $referredReward,
            ]);

            $referrer->increment('credit_balance', $referrerReward);
            $consumer->increment('credit_balance', $referredReward);
        });

        app(Notifier::class)->notify(
            $referrer,
            'referral',
            'Referral reward earned',
            "You've earned PKR {$referrerReward} credit — your referral just made their first payment.",
            route('consumer.referrals.index')
        );
    }
}
