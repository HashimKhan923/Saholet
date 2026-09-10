<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(Request $request): View
    {
        // A plain "Log in" link (as opposed to auth middleware bouncing a guest
        // off a protected page, which already sets this automatically) can
        // carry ?redirect=/wherever so login sends them back afterwards.
        // Only ever a same-site relative path — never an absolute/external URL.
        $redirect = $request->query('redirect');
        if (is_string($redirect) && Str::startsWith($redirect, '/') && ! Str::startsWith($redirect, '//')) {
            $request->session()->put('url.intended', url($redirect));
        }

        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => 'These credentials do not match our records.',
            ]);
        }

        if (Auth::user()->isSuspended()) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                'email' => 'Your account has been suspended. Please contact support.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route(Auth::user()->dashboardRoute()));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}