<?php

namespace App\Http\Controllers\Consumer;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReferralController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        if (! $user->referral_code) {
            $user->update(['referral_code' => User::generateUniqueReferralCode()]);
        }

        $referredUsers = $user->referredUsers()->latest()->get();

        $rewards = $user->referralRewardsGiven()->latest()->get()->keyBy('referred_user_id');

        return view('consumer.referrals.index', [
            'user' => $user,
            'referredUsers' => $referredUsers,
            'rewards' => $rewards,
            'referralUrl' => route('register', ['ref' => $user->referral_code]),
        ]);
    }
}
