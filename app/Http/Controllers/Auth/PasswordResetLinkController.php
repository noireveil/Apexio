<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

/**
 * Controller untuk request link reset password.
 * 
 * Mengirim link reset password ke email pengguna.
 */
class PasswordResetLinkController extends Controller
{
    /**
     * Menampilkan halaman request link reset password.
     *
     * @return View Halaman view untuk form forgot password
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Mengirim link reset password ke email pengguna.
     * 
     * Validasi email dan kirim link reset via email.
     *
     * @param Request $request Request yang berisi email pengguna
     * @return RedirectResponse Redirect dengan status pengiriman link
     * @throws \Illuminate\Validation\ValidationException Jika validasi gagal
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status == Password::RESET_LINK_SENT
                    ? back()->with('status', __($status))
                    : back()->withInput($request->only('email'))
                        ->withErrors(['email' => __($status)]);
    }
}