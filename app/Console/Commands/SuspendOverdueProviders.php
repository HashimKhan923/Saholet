<?php

namespace App\Console\Commands;

use App\Models\LedgerEntry;
use App\Models\Wallet;
use App\Services\Notifier;
use Illuminate\Console\Command;

class SuspendOverdueProviders extends Command
{
    protected $signature = 'providers:suspend-overdue';

    protected $description = 'Suspend providers whose cash-commission debt has gone unpaid past the grace period';

    public function handle(Notifier $notifier): int
    {
        $graceDays = (int) config('payments.settlement_grace_days');
        $cutoff = now()->subDays($graceDays);

        $negativeWallets = Wallet::where('available_balance', '<', 0)
            ->with('user.providerProfile')
            ->get();

        $suspended = 0;

        foreach ($negativeWallets as $wallet) {
            $profile = $wallet->user?->providerProfile;

            if (! $profile || $profile->isSuspended()) {
                continue;
            }

            $hasOverdueDebt = LedgerEntry::where('wallet_id', $wallet->id)
                ->where('type', 'cash_commission_due')
                ->where('created_at', '<=', $cutoff)
                ->exists();

            if (! $hasOverdueDebt) {
                continue;
            }

            $profile->update([
                'suspended_at' => now(),
                'suspension_reason' => "Unpaid cash-commission balance older than {$graceDays} days.",
            ]);

            $notifier->notify(
                $profile->user,
                'account',
                'Account paused',
                "Your account has been paused because of an unpaid commission balance older than {$graceDays} days. Settle it to resume accepting new jobs.",
                route('provider.settlements.index')
            );

            $suspended++;
        }

        $this->info("Suspended {$suspended} provider(s) for unpaid commission.");

        return self::SUCCESS;
    }
}
