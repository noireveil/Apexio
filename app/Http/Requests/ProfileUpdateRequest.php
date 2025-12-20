<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Form request untuk update profil pengguna.
 * 
 * Menangani validasi data profil termasuk nama, email, dan avatar.
 */
class ProfileUpdateRequest extends FormRequest
{
    /**
     * Mendapatkan aturan validasi untuk update profil.
     * 
     * Email harus unik kecuali untuk user yang sedang login.
     * Avatar maksimal 2MB dan harus berupa gambar.
     *
     * @return array<string, mixed> Rules validasi
     */
    public function rules(): array
    {
        return [
            'name' => ['string', 'max:255'],
            'email' => ['email', 'max:255', Rule::unique(User::class)->ignore($this->user()->id)],
            'avatar' => ['nullable', 'image', 'max:2048'],
        ];
    }
}