<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Controller untuk mengelola sesi autentikasi pengguna.
 * 
 * Menangani login, logout, dan regenerasi sesi untuk keamanan aplikasi.
 */
class AuthenticatedSessionController extends Controller
{
    /**
     * Menampilkan halaman login.
     *
     * @return View Halaman view untuk form login
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Memproses request autentikasi dari pengguna.
     * 
     * Memvalidasi kredensial, membuat sesi baru, dan redirect ke dashboard.
     *
     * @param LoginRequest $request Request yang berisi kredensial login
     * @return RedirectResponse Redirect ke halaman dashboard setelah login berhasil
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Menghancurkan sesi autentikasi pengguna (logout).
     * 
     * Melakukan logout, invalidasi sesi, dan regenerasi token CSRF.
     *
     * @param Request $request Request HTTP yang berisi sesi aktif
     * @return RedirectResponse Redirect ke halaman utama setelah logout
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}