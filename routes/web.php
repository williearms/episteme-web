<?php

use App\Http\Controllers\Account\DashboardController;
use App\Http\Controllers\Account\OrderController as AccountOrderController;
use App\Http\Controllers\Account\ProfileController;
use App\Http\Controllers\Account\SettingsController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LearningController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentWebhookController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');

Route::get('/categories/{category}', [CategoryController::class, 'show'])->name('categories.show');

Route::middleware('auth')->group(function () {
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/items', [CartController::class, 'store'])->name('cart.items.store');
    Route::patch('/cart/items/{product}', [CartController::class, 'update'])->whereNumber('product')->name('cart.items.update');
    Route::delete('/cart/items/{product}', [CartController::class, 'destroy'])->whereNumber('product')->name('cart.items.destroy');

    Route::get('/checkout', [CheckoutController::class, 'show'])->name('checkout.show');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');

    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{order}/retry-payment', [OrderController::class, 'retryPayment'])->name('orders.retry-payment');

    Route::get('/learning/{slug}', [LearningController::class, 'show'])->name('learning.show');
    Route::get('/learning/{slug}/lessons/{lesson}', [LearningController::class, 'lesson'])->whereNumber('lesson')->name('learning.lessons.show');
    Route::post('/learning/{slug}/lessons/{lesson}/complete', [LearningController::class, 'complete'])->whereNumber('lesson')->name('learning.lessons.complete');
    Route::delete('/learning/{slug}/lessons/{lesson}/complete', [LearningController::class, 'uncomplete'])->whereNumber('lesson')->name('learning.lessons.uncomplete');
    Route::get('/learning/{slug}/quizzes/{quiz}', [LearningController::class, 'quiz'])->whereNumber('quiz')->name('learning.quizzes.show');
    Route::post('/learning/{slug}/quizzes/{quiz}/attempts', [LearningController::class, 'submitQuiz'])->whereNumber('quiz')->name('learning.quizzes.attempts.store');
    Route::get('/learning/{slug}/quizzes/{quiz}/attempts/{attempt}', [LearningController::class, 'attempt'])->whereNumber(['quiz', 'attempt'])->name('learning.quizzes.attempts.show');
    Route::get('/learning/{slug}/download', [LearningController::class, 'download'])->name('learning.download');
});

Route::middleware('auth')->prefix('account')->name('account.')->group(function () {
    Route::get('/', DashboardController::class)->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/settings', [SettingsController::class, 'edit'])->name('settings');
    Route::put('/settings/password', [SettingsController::class, 'updatePassword'])->name('settings.password');
    Route::get('/orders', [AccountOrderController::class, 'index'])->name('orders.index');
    Route::get('/learning', [LearningController::class, 'index'])->name('learning.index');
});

// No session/CSRF token exists for a provider-originated request — authenticity
// is instead established by verifying the provider's own signature inside the
// controller (see PaymentWebhookController + bootstrap/app.php's CSRF exception).
Route::post('/payments/webhook/midtrans', [PaymentWebhookController::class, 'handle'])->name('payments.webhook.midtrans');

require __DIR__.'/auth.php';
