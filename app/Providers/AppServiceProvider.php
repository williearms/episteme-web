<?php

namespace App\Providers;

use App\Payments\MidtransGateway;
use App\Payments\PaymentGateway;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(PaymentGateway::class, function () {
            return match (config('payment.provider')) {
                'midtrans' => new MidtransGateway(
                    serverKey: config('payment.midtrans.server_key'),
                    isProduction: (bool) config('payment.midtrans.is_production'),
                ),
                default => throw new \RuntimeException(
                    'Unsupported PAYMENT_PROVIDER: '.config('payment.provider')
                ),
            };
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
