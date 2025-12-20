<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * Controller untuk mengelola profil pengguna.
 * 
 * Menangani tampilan, update, dan penghapusan profil pengguna.
 */
class ProfileController extends Controller
{
    /**
     * Menampilkan halaman edit profil.
     *
     * @param Request $request Request HTTP yang berisi user yang sedang login
     * @return View Halaman edit profil
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', ['user' => $request->user()]);
    }

    /**
     * Menampilkan halaman edit password.
     *
     * @param Request $request Request HTTP yang berisi user yang sedang login
     * @return View Halaman edit password
     */
    public function editPassword(Request $request): View
    {
        return view('profile.password', ['user' => $request->user()]);
    }

    /**
     * Menampilkan halaman danger zone (hapus akun).
     *
     * @param Request $request Request HTTP yang berisi user yang sedang login
     * @return View Halaman danger zone
     */
    public function editDangerZone(Request $request): View
    {
        return view('profile.danger-zone', ['user' => $request->user()]);
    }

    /**
     * Update informasi profil pengguna.
     * 
     * Menangani update nama, email, dan avatar. Jika email berubah, reset verifikasi.
     *
     * @param ProfileUpdateRequest $request Request yang sudah tervalidasi
     * @return RedirectResponse Redirect dengan status update
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->safe()->except(['avatar']);

        if ($request->boolean('remove_photo')) {
            if ($user->avatar_path && Storage::disk('public')->exists($user->avatar_path)) {
                Storage::disk('public')->delete($user->avatar_path);
            }
            $user->avatar_path = null;
        }
        elseif ($request->hasFile('avatar')) {
            if ($user->avatar_path && Storage::disk('public')->exists($user->avatar_path)) {
                Storage::disk('public')->delete($user->avatar_path);
            }
            $path = $request->file('avatar')->store('avatars', 'public');
            $user->avatar_path = $path;
        }

        $user->fill($data);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Menghapus akun pengguna secara permanen.
     * 
     * Validasi password, logout, hapus avatar, hapus user, dan destroy session.
     *
     * @param Request $request Request yang berisi password konfirmasi
     * @return RedirectResponse Redirect ke halaman utama setelah akun dihapus
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        if ($user->avatar_path) {
            Storage::disk('public')->delete($user->avatar_path);
        }
        
        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
