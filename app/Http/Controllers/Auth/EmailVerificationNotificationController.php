<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Controller untuk mengirim ulang notifikasi verifikasi email.
 * 
 * Menangani pengiriman ulang link verifikasi ke email pengguna.
 */
class EmailVerificationNotificationController extends Controller
{
    /**
     * Mengirim notifikasi verifikasi email baru.
     * 
     * Cek apakah email sudah terverifikasi, jika belum kirim link baru.
     *
     * @param Request $request Request dari pengguna yang meminta verifikasi
     * @return RedirectResponse Redirect dengan status pengiriman
     */
    public function store(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard', absolute: false));
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'verification-link-sent');
    }
}
