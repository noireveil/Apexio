<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Controller untuk menampilkan prompt verifikasi email.
 * 
 * Menampilkan halaman yang meminta pengguna untuk memverifikasi email.
 */
class EmailVerificationPromptController extends Controller
{
    /**
     * Menampilkan halaman prompt verifikasi email.
     * 
     * Jika sudah terverifikasi, redirect ke dashboard. Jika belum, tampilkan halaman verifikasi.
     *
     * @param Request $request Request dari pengguna yang sedang login
     * @return RedirectResponse|View Redirect atau view verifikasi email
     */
    public function __invoke(Request $request): RedirectResponse|View
    {
        return $request->user()->hasVerifiedEmail()
                    ? redirect()->intended(route('dashboard', absolute: false))
                    : view('auth.verify-email');
    }
}