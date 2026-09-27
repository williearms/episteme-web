<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate(['email' => ['required', 'string', 'email']]);

        $status = Password::sendResetLink($request->only('email'));

        // Laravel's broker reveals whether the email is registered via its
        // status string (e.g. "We can't find a user with that email
        // address"). To avoid account enumeration we always show the same
        // generic confirmation — except throttling, which is safe to surface.
        if ($status === Password::RESET_THROTTLED) {
            return back()->withErrors(['email' => __($status)]);
        }

        return back()->with('status', 'If an account exists for that email address, we have emailed a password reset link.');
    }
}
