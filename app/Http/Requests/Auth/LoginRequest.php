<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Form request untuk login pengguna.
 * 
 * Menangani validasi, autentikasi, dan rate limiting untuk login.
 */
class LoginRequest extends FormRequest
{
    /**
     * Menentukan apakah user berhak membuat request ini.
     *
     * @return bool Selalu return true karena login terbuka untuk semua
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Mendapatkan aturan validasi untuk request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string> Rules validasi
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Mencoba autentikasi kredensial dari request.
     * 
     * Cek rate limit, attempt login, dan handle failed attempts.
     *
     * @return void
     * @throws ValidationException Jika autentikasi gagal atau rate limited
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Memastikan request login tidak terkena rate limit.
     * 
     * Maksimal 5 percobaan login per throttle key.
     *
     * @return void
     * @throws ValidationException Jika terlalu banyak percobaan
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Mendapatkan rate limiting throttle key untuk request.
     * 
     * Key berdasarkan kombinasi email dan IP address.
     *
     * @return string Throttle key unik untuk user dan IP
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }
}