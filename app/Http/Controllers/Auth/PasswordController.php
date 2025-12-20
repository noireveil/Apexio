<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

/**
 * Controller untuk update password pengguna yang sedang login.
 * 
 * Memungkinkan pengguna mengubah password mereka dari halaman profil.
 */
class PasswordController extends Controller
{
    /**
     * Update password pengguna.
     * 
     * Validasi password lama, hash password baru, dan simpan ke database.
     *
     * @param Request $request Request yang berisi password lama dan baru
     * @return RedirectResponse Redirect dengan status update password
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('status', 'password-updated');
    }
}
