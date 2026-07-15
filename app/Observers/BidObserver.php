<?php

namespace App\Observers;

use App\Events\JobBidUpdated;
use App\Models\Bid;

class BidObserver
{
    public function created(Bid $bid): void
    {
        $this->broadcast($bid);
    }

    public function updated(Bid $bid): void
    {
        if (! $bid->wasChanged('status')) {
            return;
        }

        $this->broadcast($bid);
    }

    private function broadcast(Bid $bid): void
    {
        try {
            broadcast(new JobBidUpdated($bid));
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
