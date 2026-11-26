<?php

namespace App\Providers;

use App\Payments\MidtransGateway;
use App\Payments\PaymentGateway;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Vite;
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
                    // Never let a stray env var redirect real payments somewhere else.
                    snapUrl: app()->isProduction() ? null : config('payment.midtrans.snap_url'),
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
        $this->configureRateLimiting();

        // The E2E server uses the built assets even while a developer's Vite server is running:
        // point Laravel at a "hot" file that never exists, and at its own build folder.
        if ($this->app->environment('e2e')) {
            Vite::useHotFile(storage_path('framework/e2e-vite.hot'));
            Vite::useBuildDirectory('build-e2e');
        }
    }

    /**
     * One *named* limiter per action. Laravel's numeric `throttle:10,1` keys every route by just the
     * user id (or domain+IP for guests), so all of them share a single counter: five login
     * attempts would lock the same visitor out of "forgot password", and a few cart clicks could
     * 429 checkout. Prefixing the key with the limiter name keeps the buckets independent.
     */
    private function configureRateLimiting(): void
    {
        $perMinute = [
            'login' => 10,
            'register' => 10,
            'forgot-password' => 5,
            'reset-password' => 10,
            'verification' => 6,
            'cart' => 60,
            'checkout' => 10, // also starts a payment with the gateway on every call
            'lesson-progress' => 60,
            'quiz' => 20,
            'profile' => 20,
            'password-change' => 10,
        ];

        foreach ($perMinute as $name => $limit) {
            RateLimiter::for($name, fn (Request $request) => Limit::perMinute($limit)
                ->by($name.'|'.($request->user()?->getAuthIdentifier() ?? $request->ip())));
        }
    }
}
