<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Controller untuk konfirmasi password pengguna.
 * 
 * Digunakan untuk memvalidasi ulang password sebelum aksi sensitif.
 */
class ConfirmablePasswordController extends Controller
{
    /**
     * Menampilkan halaman konfirmasi password.
     *
     * @return View Halaman view untuk form konfirmasi password
     */
    public function show(): View
    {
        return view('auth.confirm-password');
    }

    /**
     * Memvalidasi password pengguna yang sedang login.
     * 
     * Menyimpan timestamp konfirmasi di sesi untuk mencegah re-validasi berulang.
     *
     * @param Request $request Request yang berisi password untuk divalidasi
     * @return RedirectResponse Redirect ke halaman yang dimaksud setelah konfirmasi
     * @throws ValidationException Jika password tidak valid
     */
    public function store(Request $request): RedirectResponse
    {
        if (! Auth::guard('web')->validate([
            'email' => $request->user()->email,
            'password' => $request->password,
        ])) {
            throw ValidationException::withMessages([
                'password' => __('auth.password'),
            ]);
        }

        $request->session()->put('auth.password_confirmed_at', time());

        return redirect()->intended(route('dashboard', absolute: false));
    }
}