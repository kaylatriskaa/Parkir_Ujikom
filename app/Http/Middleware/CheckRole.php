<?php
// FILE: CheckRole.php
// FUNGSI: Middleware (filter keamanan) yang mengecek hak akses berdasarkan role.
//         Middleware ini berjalan SEBELUM halaman dibuka.
//         Analoginya seperti satpam di gedung: sebelum boleh masuk,
//         satpam cek dulu "Kamu siapa? Role apa? Boleh masuk ke ruangan ini gak?"
//
// CARA PAKAI DI ROUTES (web.php):
//   Route::middleware('role:admin')          → hanya admin yang boleh akses
//   Route::middleware('role:petugas')        → hanya petugas yang boleh akses
//   Route::middleware('role:admin,owner')    → admin DAN owner boleh akses
//
// CONTOH ALUR:
//   User login sebagai 'petugas' → coba buka /admin/dashboard
//   → CheckRole cek: 'petugas' ada di daftar ['admin']? → TIDAK
//   → User ditendang balik ke /petugas/dashboard

namespace App\Http\Middleware;

use Closure;                                       // Closure = fungsi anonim yang bisa diteruskan
use Illuminate\Http\Request;                       // Object berisi data request dari browser
use Illuminate\Support\Facades\Auth;               // Facade untuk cek siapa yang sedang login

class CheckRole
{
    // --- FUNGSI UTAMA MIDDLEWARE ---
    // $request = data permintaan dari browser (URL yang dibuka, data form, dll)
    // $next    = fungsi untuk melanjutkan ke halaman tujuan (kalau lolos pengecekan)
    // ...$roles = daftar role yang DIIZINKAN akses halaman ini
    //            Tanda ... artinya bisa menerima banyak parameter
    //            Contoh: handle($request, $next, 'admin', 'owner')
    //            Maka $roles = ['admin', 'owner']
    public function handle(Request $request, Closure $next, ...$roles)
    {
        // PENGECEKAN 1: Apakah user sudah login?
        // Auth::check() → return true kalau sudah login, false kalau belum
        // Kalau BELUM login → langsung redirect ke halaman login
        if (!Auth::check()) {
            return redirect('login');
        }

        // PENGECEKAN 2: Apakah role user yang login ADA di daftar role yang diizinkan?
        // Auth::user()->role → ambil role user yang sedang login (misal: 'petugas')
        // in_array('petugas', ['admin', 'petugas']) → return TRUE → boleh akses
        // in_array('owner', ['admin', 'petugas'])   → return FALSE → tidak boleh
        if (in_array(Auth::user()->role, $roles)) {
            // LOLOS! User punya akses → lanjutkan buka halaman yang diminta
            return $next($request);
        }

        // PENGECEKAN 3: User TIDAK punya akses ke halaman ini
        // Redirect ke dashboard sesuai role-nya sendiri + pesan error
        // Contoh: user 'owner' coba buka /admin/dashboard
        //   → redirect ke /owner/dashboard + pesan "Anda tidak memiliki akses"
        return redirect()->route(Auth::user()->role . '.dashboard')
                         ->with('error', 'Anda tidak memiliki akses ke halaman tersebut.');
    }
}
