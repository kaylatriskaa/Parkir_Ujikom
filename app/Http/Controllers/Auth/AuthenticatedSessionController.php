<?php
// FILE: AuthenticatedSessionController.php
// FUNGSI: Controller yang mengatur 3 hal utama:
//         1. Menampilkan halaman login (form username + password)
//         2. Memproses data setelah user klik tombol LOGIN
//            → cek akun aktif/nonaktif → redirect ke dashboard sesuai role
//         3. Memproses logout (keluar dari sistem)
//         File ini bekerja sama dengan LoginRequest.php untuk proses autentikasi.

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;              // Class dasar controller Laravel
use App\Http\Requests\Auth\LoginRequest;           // Class yang menghandle validasi + proses login MD5
use Illuminate\Http\RedirectResponse;              // Tipe return: redirect/pindah ke halaman lain
use Illuminate\Http\Request;                       // Object yang berisi semua data dari browser (form, URL, dll)
use Illuminate\Support\Facades\Auth;               // Facade Auth: fungsi login, logout, cek user yang sedang login
use Illuminate\View\View;                          // Tipe return: menampilkan halaman HTML

class AuthenticatedSessionController extends Controller
{
    // --- TAMPILKAN HALAMAN LOGIN ---
    // Dipanggil saat user buka URL: /login (method GET)
    // Mengembalikan file tampilan: resources/views/auth/login.blade.php
    // Di file itu ada form dengan input username, password, dan tombol LOGIN
    public function create(): View
    {
        return view('auth.login');
    }

    // --- PROSES SETELAH USER KLIK TOMBOL LOGIN ---
    // Dipanggil saat form login di-submit (method POST)
    // Parameter $request bertipe LoginRequest → artinya validasi di LoginRequest
    // akan OTOMATIS dijalankan sebelum masuk ke fungsi ini
    // Kalau validasi gagal → user dikembalikan ke halaman login + pesan error
    public function store(LoginRequest $request): RedirectResponse
    {
        // STEP 1: Jalankan proses autentikasi dari LoginRequest
        // Di dalam fungsi authenticate() inilah username + password dicek ke database
        // Kalau gagal → dilempar error dan user kembali ke halaman login
        // Kalau berhasil → lanjut ke step berikutnya
        $request->authenticate();

        // STEP 2: Cek apakah akun yang login statusnya AKTIF atau NONAKTIF
        // $request->user() = data user yang baru saja login
        // status_aktif == 0 artinya akun sudah dinonaktifkan oleh Admin
        // Kenapa dicek? Karena admin bisa menonaktifkan akun petugas/owner
        // tanpa menghapusnya dari database
        if ($request->user()->status_aktif == 0) {
            // Paksa logout karena akunnya nonaktif → user tidak boleh masuk
            Auth::guard('web')->logout();

            // Hapus session lama → data login dihapus dari browser
            $request->session()->invalidate();

            // Buat token CSRF baru → untuk keamanan form berikutnya
            // CSRF = Cross-Site Request Forgery (serangan pemalsuan form)
            $request->session()->regenerateToken();

            // Redirect ke halaman login + tampilkan pesan error merah
            return redirect()->route('login')->withErrors([
                'username' => 'Akun Anda telah dinonaktifkan oleh Admin.',
            ]);
        }

        // STEP 3: Regenerasi session → buat ID session baru
        // Ini untuk keamanan: mencegah "session fixation attack"
        // yaitu serangan dimana hacker mencuri session ID lama
        $request->session()->regenerate();

        // STEP 4: Ambil role user yang sedang login
        // Contoh: $role = 'admin', atau 'petugas', atau 'owner'
        $role = $request->user()->role;

        // STEP 5: Redirect ke dashboard yang sesuai berdasarkan role
        // match() = mirip switch-case tapi lebih ringkas
        // 'admin'   → buka halaman /admin/dashboard
        // 'petugas' → buka halaman /petugas/dashboard
        // 'owner'   → buka halaman /owner/dashboard
        // default   → kalau role tidak dikenal, buka halaman utama /
        return match ($role) {
            'admin' => redirect()->route('admin.dashboard'),
            'petugas' => redirect()->route('petugas.dashboard'),
            'owner' => redirect()->route('owner.dashboard'),
            default => redirect('/'),
        };
    }

    // --- PROSES LOGOUT ---
    // Dipanggil saat user klik tombol "Logout" di sidebar/navbar
    // Menghapus semua data login dari browser dan mengembalikan ke halaman login
    public function destroy(Request $request): RedirectResponse
    {
        // Logout dari sistem → hapus data user dari session
        Auth::guard('web')->logout();

        // Hapus SEMUA data session (termasuk cart, preferences, dll kalau ada)
        $request->session()->invalidate();

        // Buat token CSRF baru untuk keamanan
        $request->session()->regenerateToken();

        // Kembali ke halaman utama / → yang akan redirect ke /login
        return redirect('/');
    }
}
