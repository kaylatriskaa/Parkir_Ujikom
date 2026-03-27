<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
   public function store(LoginRequest $request): RedirectResponse
{
    // 1. Cek email dan password
    $request->authenticate();

    // 2. CEK STATUS AKTIF/NONAKTIF
    // Jika user yang baru login statusnya tidak aktif (0)
    if ($request->user()->is_active == 0) {
        Auth::guard('web')->logout(); // Logout paksa

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Lempar balik ke login dengan pesan error
        return redirect()->route('login')->withErrors([
            'email' => 'Akun Anda telah dinonaktifkan oleh Admin.',
        ]);
    }

    // 3. Jika aktif, lanjut buat session
    $request->session()->regenerate();

    $role = $request->user()->role;

    return match ($role) {
        'admin' => redirect()->route('admin.dashboard'),
        'petugas' => redirect()->route('petugas.dashboard'),
        'owner' => redirect()->route('owner.dashboard'),
        default => redirect('/'),
    };
}

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
