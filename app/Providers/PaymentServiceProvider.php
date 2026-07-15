<?php

namespace App\Providers;

use App\Payments\EasypaisaGateway;
use App\Payments\JazzCashGateway;
use App\Payments\MockGateway;
use App\Payments\PaymentManager;
use Illuminate\Support\ServiceProvider;

class PaymentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PaymentManager::class, function () {
            return new PaymentManager([
                new MockGateway(),
                new JazzCashGateway(),
                new EasypaisaGateway(),
            ]);
        });
    }

    public function boot(): void
    {
        //
    }
}