<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

/**
 * Controller untuk verifikasi email pengguna.
 * 
 * Menandai email pengguna sebagai terverifikasi setelah klik link verifikasi.
 */
class VerifyEmailController extends Controller
{
    /**
     * Menandai email pengguna sebagai terverifikasi.
     * 
     * Cek status verifikasi, tandai sebagai verified, dan trigger event.
     *
     * @param EmailVerificationRequest $request Request verifikasi dengan signature yang valid
     * @return RedirectResponse Redirect ke dashboard dengan parameter verified
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard', absolute: false).'?verified=1');
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        return redirect()->intended(route('dashboard', absolute: false).'?verified=1');
    }
}
