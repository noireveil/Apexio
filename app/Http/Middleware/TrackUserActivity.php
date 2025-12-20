<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware untuk tracking aktivitas pengguna.
 * 
 * Menyimpan status online pengguna di cache selama 2 menit.
 */
class TrackUserActivity
{
    /**
     * Handle request dan track user activity.
     * 
     * Jika user login, simpan status online di cache dengan TTL 2 menit.
     *
     * @param Request $request Request HTTP yang masuk
     * @param Closure $next Closure untuk melanjutkan request
     * @return Response Response dari aplikasi
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $expiresAt = now()->addMinutes(2);
            Cache::put('user-is-online-' . Auth::user()->id, true, $expiresAt);
        }

        return $next($request);
    }
}