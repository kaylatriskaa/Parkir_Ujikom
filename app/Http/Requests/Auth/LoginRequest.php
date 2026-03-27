<?php
// FILE: LoginRequest.php
// FUNGSI: Mengatur seluruh proses autentikasi login pengguna.
//         Di sini terjadi: validasi input form, pencocokan password dengan MD5,
//         dan pembatasan percobaan login (rate limiting) agar tidak bisa dicoba terus-terusan.
//         File ini dipanggil oleh AuthenticatedSessionController saat user klik tombol LOGIN.

namespace App\Http\Requests\Auth;

// Import class-class yang dibutuhkan dari Laravel
use App\Models\User;                              // Model User → untuk akses data di tabel tb_user
use Illuminate\Auth\Events\Lockout;               // Event yang aktif saat akun terkunci karena terlalu banyak gagal login
use Illuminate\Foundation\Http\FormRequest;        // Class dasar Laravel untuk menangani validasi form secara otomatis
use Illuminate\Support\Facades\Auth;               // Facade Auth → fungsi bawaan Laravel untuk login, logout, cek siapa yang login
use Illuminate\Support\Facades\RateLimiter;        // Facade RateLimiter → untuk membatasi jumlah percobaan login (anti brute force)
use Illuminate\Support\Str;                        // Helper String → untuk manipulasi teks (lowercase, transliterasi)
use Illuminate\Validation\ValidationException;     // Exception/error khusus → dilempar saat validasi gagal (misal: password salah)

// Class LoginRequest mewarisi FormRequest
// FormRequest = class bawaan Laravel yang otomatis jalankan validasi sebelum masuk ke controller
class LoginRequest extends FormRequest
{
    // authorize() → Menentukan siapa yang boleh kirim form ini
    // return true artinya: SEMUA orang boleh kirim form login (termasuk yang belum login)
    // Kalau kita return false, maka tidak ada yang bisa login sama sekali
    public function authorize(): bool
    {
        return true;
    }

    // --- ATURAN VALIDASI INPUT ---
    // Fungsi ini menentukan syarat-syarat yang harus dipenuhi sebelum data diproses
    // Kalau tidak memenuhi syarat → Laravel otomatis kirim pesan error ke halaman login
    public function rules(): array
    {
        return [
            // 'required' = field ini WAJIB diisi, tidak boleh kosong
            // 'string'   = isinya harus berupa teks (bukan angka/file)
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    // --- PROSES UTAMA LOGIN ---
    // Fungsi ini yang menjalankan pencocokan username + password ke database
    // Dipanggil dari AuthenticatedSessionController → $request->authenticate()
    public function authenticate(): void
    {
        // STEP 1: Cek dulu apakah user ini sudah terlalu banyak gagal login
        // Kalau sudah 5x gagal → akan dilempar error "Terlalu banyak percobaan"
        // Fungsi ini didefinisikan di bawah (ensureIsNotRateLimited)
        $this->ensureIsNotRateLimited();

        // STEP 2: Cari data user di tabel tb_user berdasarkan username yang diketik
        // Cara kerja:
        //   User::where('username', 'admin') → SELECT * FROM tb_user WHERE username = 'admin'
        //   ->first() → ambil 1 baris data pertama yang cocok
        //   Kalau tidak ada yang cocok → $user = null (kosong)
        $user = User::where('username', $this->username)->first();

        // STEP 3: Bandingkan password yang diketik dengan yang ada di database
        // Penjelasan:
        //   $this->password = password yang diketik user di form (misalnya: "admin123")
        //   md5($this->password) = hash password tsb → hasilnya: "0192023a7bbd73250516f069df18b500"
        //   $user->password = password yang sudah di-hash di database (juga "0192023a7bbd73250516f069df18b500")
        //
        //   Jadi kita TIDAK membandingkan password asli, tapi membandingkan HASH-nya
        //   Kalau hash-nya sama → password benar
        //   Kalau hash-nya beda → password salah
        //
        // Kondisi gagal (salah satu TRUE = login gagal):
        //   !$user → user dengan username itu tidak ditemukan di database
        //   md5(...) !== $user->password → password yang diketik tidak cocok
        if (!$user || md5($this->password) !== $user->password) {

            // Catat percobaan gagal ke rate limiter (+1)
            // Setelah 5x gagal, akun akan diblokir sementara
            RateLimiter::hit($this->throttleKey());

            // Lempar error ke halaman login → tampilkan "Username atau password salah"
            // trans('auth.failed') = ambil pesan error dari file bahasa Laravel
            throw ValidationException::withMessages([
                'username' => trans('auth.failed'),
            ]);
        }

        // STEP 4: Kalau sampai sini berarti LOGIN BERHASIL!
        // Auth::login() = masukkan data user ke session browser
        // Setelah ini, Auth::user() akan mengembalikan data user yang login
        // $this->boolean('remember') = cek apakah user centang checkbox "Ingat Saya"
        Auth::login($user, $this->boolean('remember'));

        // Hapus catatan percobaan gagal (reset counter ke 0)
        // Supaya kalau login lagi nanti, counter-nya mulai dari awal
        RateLimiter::clear($this->throttleKey());
    }

    // --- PEMBATASAN PERCOBAAN LOGIN (RATE LIMITING) ---
    // Fungsi keamanan: mencegah serangan brute force (coba-coba password terus sampai ketemu)
    // Cara kerja: hitung berapa kali gagal login, kalau sudah 5x → blokir sementara
    public function ensureIsNotRateLimited(): void
    {
        // tooManyAttempts() cek: apakah sudah gagal lebih dari 5 kali?
        // Kalau BELUM 5x → return (lanjutkan proses login seperti biasa)
        // Kalau SUDAH 5x → lanjut ke bawah (blokir)
        if (!RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        // Kirim event Lockout → Laravel mencatat bahwa akun ini sedang diblokir
        event(new Lockout($this));

        // Hitung berapa DETIK lagi user bisa coba login
        // Contoh: availableIn() return 45 → artinya harus tunggu 45 detik lagi
        $seconds = RateLimiter::availableIn($this->throttleKey());

        // Lempar error → "Terlalu banyak percobaan login, silakan coba lagi dalam X detik"
        throw ValidationException::withMessages([
            'username' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),   // ceil() = bulatkan ke atas (45 detik → 1 menit)
            ]),
        ]);
    }

    // --- KUNCI UNIK RATE LIMITER ---
    // Membuat kunci unik gabungan username + IP address
    // Contoh: "admin|192.168.1.1"
    // Kenapa digabung? Supaya batasan berlaku per username per IP
    // Jadi kalau orang A coba login "admin" 5x dari IP A → diblokir
    // Tapi orang B dari IP B masih bisa coba
    public function throttleKey(): string
    {
        // Str::lower() = huruf kecil semua
        // Str::transliterate() = ubah karakter khusus jadi ASCII biasa
        // $this->ip() = alamat IP browser yang mengirim request
        return Str::transliterate(Str::lower($this->string('username')) . '|' . $this->ip());
    }
}
