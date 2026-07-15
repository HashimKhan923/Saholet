<?php

namespace App\Observers;

use App\Events\EmergencyRequestStatusUpdated;
use App\Models\EmergencyRequest;

class EmergencyRequestObserver
{
    public function updated(EmergencyRequest $emergency): void
    {
        if (! $emergency->wasChanged('status')) {
            return;
        }

        try {
            broadcast(new EmergencyRequestStatusUpdated($emergency));
        } catch (\Throwable $e) {
            report($e);
        }
    }
}