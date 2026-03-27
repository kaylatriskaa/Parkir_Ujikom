<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        // 1. Cek apakah user sudah login
        if (!Auth::check()) {
            return redirect('login');
        }

        // 2. Cek apakah role user ada dalam daftar role yang diizinkan
        if (in_array(Auth::user()->role, $roles)) {
            return $next($request);
        }

        // 3. Jika tidak punya akses, lempar ke halaman sesuai rolenya
        return redirect()->route(Auth::user()->role . '.dashboard')
                         ->with('error', 'Anda tidak memiliki akses ke halaman tersebut.');
    }
}
