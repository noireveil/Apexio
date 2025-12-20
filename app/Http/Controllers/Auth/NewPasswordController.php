<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

/**
 * Controller untuk reset password pengguna.
 * 
 * Menangani proses reset password melalui link yang dikirim via email.
 */
class NewPasswordController extends Controller
{
    /**
     * Menampilkan halaman form reset password.
     *
     * @param Request $request Request yang berisi token dan email
     * @return View Halaman view untuk form reset password
     */
    public function create(Request $request): View
    {
        return view('auth.reset-password', ['request' => $request]);
    }

    /**
     * Memproses request reset password baru.
     * 
     * Validasi token dan email, update password, dan trigger event PasswordReset.
     *
     * @param Request $request Request yang berisi token, email, dan password baru
     * @return RedirectResponse Redirect dengan status reset password
     * @throws \Illuminate\Validation\ValidationException Jika validasi gagal
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($request->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        return $status == Password::PASSWORD_RESET
                    ? redirect()->route('login')->with('status', __($status))
                    : back()->withInput($request->only('email'))
                        ->withErrors(['email' => __($status)]);
    }
}