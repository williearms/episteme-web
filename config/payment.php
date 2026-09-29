<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Payment Provider
    |--------------------------------------------------------------------------
    |
    | Which gateway implementation AppServiceProvider binds to App\Payments\PaymentGateway.
    | Only "midtrans" is implemented in Phase 5 — see docs/PROGRESS.md for why, and for
    | the steps to add another provider (e.g. "xendit") without touching any controller.
    |
    */
    'provider' => env('PAYMENT_PROVIDER', 'midtrans'),

    'midtrans' => [
        'server_key' => env('MIDTRANS_SERVER_KEY'),
        'client_key' => env('MIDTRANS_CLIENT_KEY'),
        'is_production' => env('MIDTRANS_IS_PRODUCTION', false),
    ],
];
