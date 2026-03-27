<?php
// FILE: AdminController.php
// FUNGSI: Controller utama untuk halaman Admin.
//         Mengatur semua fitur yang bisa diakses admin:
//         - Dashboard statistik (pendapatan, slot, transaksi hari ini)
//         - CRUD User → Tambah akun baru, Edit data, Hapus akun, Toggle aktif/nonaktif
//         - CRUD Area Parkir → Tambah area, Edit nama/kapasitas, Toggle enable/disable
//         - CRUD Tarif → Tambah jenis tarif baru, Edit harga per jam
//
// CARA KERJA:
//         Setiap fungsi di controller ini dipanggil oleh route tertentu di web.php
//         Contoh: URL /admin/dashboard → memanggil fungsi index()
//                 URL /admin/users (POST) → memanggil fungsi store()

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;   // Class dasar controller Laravel (semua controller harus extend ini)
use App\Models\User;                    // Model User → untuk akses tabel tb_user di database
use App\Models\Tarif;                   // Model Tarif → untuk akses tabel tb_tarif
use App\Models\Area;                    // Model Area → untuk akses tabel tb_area_parkir
use App\Models\Transaksi;               // Model Transaksi → untuk akses tabel tb_transaksi
use Illuminate\Http\Request;            // Object yang berisi semua data dari browser (form, URL, header, dll)
use Illuminate\Support\Facades\Auth;    // Facade Auth → untuk tahu siapa user yang sedang login

class AdminController extends Controller
{
    // --- DASHBOARD ADMIN ---
    // Fungsi ini dipanggil saat admin buka halaman /admin/dashboard
    // Menyiapkan semua data statistik dan mengirimnya ke tampilan
    public function index()
    {
        // Ambil semua user yang role-nya petugas ATAU owner
        // KECUALI admin yang sedang login (supaya admin gak bisa edit diri sendiri)
        // whereIn() = WHERE role IN ('petugas', 'owner')
        // where('id_user', '!=', Auth::id()) = WHERE id_user != ID admin yang login
        $users = User::whereIn('role', ['petugas', 'owner'])
                    ->where('id_user', '!=', Auth::id())
                    ->get();

        // Ambil SEMUA tarif dan area dari database
        // Tarif::all() = SELECT * FROM tb_tarif
        $tarifs = Tarif::all();
        $areas = Area::all();

        // Ambil 20 transaksi terbaru untuk ditampilkan di tab "Log Aktivitas"
        // with(['kendaraan', 'area', 'user']) = ikut ambil data dari tabel yang berelasi
        //   → jadi kita bisa akses $log->kendaraan->plat_nomor tanpa query tambahan
        // latest('waktu_masuk') = ORDER BY waktu_masuk DESC → yang terbaru dulu
        // take(20) = LIMIT 20 → ambil 20 data saja
        // try-catch = kalau terjadi error (misal tabel kosong), jangan crash, kasih array kosong
        try {
            $logs = Transaksi::with(['kendaraan', 'area', 'user'])->latest('waktu_masuk')->take(20)->get();
        } catch (\Exception $e) {
            $logs = collect();  // collect() = buat Collection kosong (array versi Laravel)
        }

        // Hitung jumlah transaksi HARI INI (untuk stat card "Transaksi Hari Ini")
        // whereDate('waktu_masuk', today()) = WHERE DATE(waktu_masuk) = tanggal hari ini
        // count() = COUNT(*) → hitung jumlah baris
        $totalLogsCount = Transaksi::whereDate('waktu_masuk', today())->count();

        // Hitung total pendapatan HARI INI (untuk stat card "Pendapatan")
        // Hanya hitung yang statusnya 'keluar' (sudah bayar)
        // sum('biaya_total') = SUM(biaya_total) → jumlahkan semua biaya
        $totalPendapatanHariIni = Transaksi::where('status', 'keluar')
            ->whereDate('waktu_masuk', today())
            ->sum('biaya_total');

        // Hitung total slot kosong di SEMUA area (untuk stat card "Sisa Slot")
        // Rumus per area: kapasitas - terisi = slot kosong
        // sum() di sini menjumlahkan hasil perhitungan per area
        // Contoh: Area A (50-10=40) + Area B (30-5=25) = total 65 slot kosong
        $totalSlotKosong = $areas->sum(function ($area) {
            return $area->kapasitas - $area->terisi;
        });

        // Total kapasitas semua area dijumlahkan
        // Contoh: Area A (50) + Area B (30) = 80
        $totalKapasitas = $areas->sum('kapasitas');

        // Kirim semua data ke tampilan admin
        // compact() = buat array dari nama variabel
        // compact('users', 'tarifs') sama saja dengan ['users' => $users, 'tarifs' => $tarifs]
        return view('dashboards.admin', compact(
            'users', 'tarifs', 'areas', 'logs', 'totalLogsCount',
            'totalPendapatanHariIni', 'totalSlotKosong', 'totalKapasitas'
        ));
    }

    // --- TAMBAH USER BARU ---
    // Dipanggil saat admin submit form "Tambah User" (POST /admin/users)
    public function store(Request $request)
    {
        // Validasi input dari form sebelum disimpan
        // Kalau tidak memenuhi syarat → Laravel otomatis kirim error ke halaman
        // 'required' = wajib diisi
        // 'max:100' = maksimal 100 karakter
        // 'unique:tb_user,username' = username harus unik (belum pernah ada di tabel tb_user)
        // 'min:6' = password minimal 6 karakter
        // 'in:petugas,owner' = role hanya boleh 'petugas' atau 'owner' (admin gak boleh bikin admin lagi)
        $request->validate([
            'nama_lengkap' => 'required|string|max:100',
            'username' => 'required|string|max:50|unique:tb_user,username',
            'password' => 'required|string|min:6',
            'role' => 'required|in:petugas,owner',
        ]);

        // Simpan user baru ke database
        // Password di-hash MD5 sebelum disimpan → JANGAN simpan password asli!
        // md5('admin123') → '0192023a7bbd73250516f069df18b500'
        User::create([
            'nama_lengkap' => $request->nama_lengkap,
            'username' => $request->username,
            'password' => md5($request->password),
            'role' => $request->role,
        ]);

        // Redirect balik ke halaman admin + tampilkan pesan sukses hijau
        return redirect()->back()->with('success', 'Akun ' . $request->nama_lengkap . ' berhasil dibuat!');
    }

    // --- EDIT USER ---
    // Dipanggil saat admin submit form edit user (PUT /admin/users/{id})
    // $id = ID user yang mau diedit (dari URL)
    public function update(Request $request, $id)
    {
        // Cari user berdasarkan ID
        // findOrFail() = kalau ID tidak ditemukan → tampilkan halaman error 404
        $user = User::findOrFail($id);

        // Validasi: username harus tetap unik, TAPI boleh sama kalau itu username milik user ini sendiri
        // Contoh: user ID 5 punya username "joko"
        //   → kalau admin edit user ID 5 dan tetap isi "joko" → BOLEH (karena itu miliknya sendiri)
        //   → kalau admin edit user ID 5 dan isi "budi" padahal "budi" sudah dipakai user lain → DITOLAK
        // unique:tb_user,username,$id,id_user = abaikan baris yang id_user-nya = $id
        $request->validate([
            'nama_lengkap' => 'required|string|max:100',
            'username' => 'required|string|max:50|unique:tb_user,username,' . $id . ',id_user',
            'role' => 'required|in:petugas,owner',
        ]);

        // Update data user satu per satu
        $user->nama_lengkap = $request->nama_lengkap;
        $user->username = $request->username;
        $user->role = $request->role;

        // Kalau field password DIISI → update password juga (hash MD5)
        // Kalau password DIKOSONGKAN → password lama tetap dipertahankan
        // filled() = cek apakah field tidak kosong
        if ($request->filled('password')) {
            $request->validate(['password' => 'min:6']);
            $user->password = md5($request->password);
        }

        // Simpan semua perubahan ke database
        $user->save();
        return redirect()->back()->with('success', 'Data user berhasil diperbarui!');
    }

    // --- HAPUS USER ---
    // Dipanggil saat admin klik tombol hapus (DELETE /admin/users/{id})
    public function destroy($id)
    {
        $user = User::findOrFail($id);

        // KEAMANAN: Admin TIDAK BOLEH menghapus akunnya sendiri
        // Auth::id() = ID admin yang sedang login
        if ($user->id_user === Auth::id()) {
            return redirect()->back()->with('error', 'Dilarang menghapus akun sendiri!');
        }

        // Hapus user dari database secara permanen
        $user->delete();
        return redirect()->back()->with('success', 'User berhasil dihapus!');
    }

    // --- TOGGLE STATUS USER (AKTIF ↔ NONAKTIF) ---
    // Dipanggil saat admin klik tombol toggle (PATCH /admin/users/{id}/toggle)
    // User yang dinonaktifkan tidak bisa login (dicek di AuthenticatedSessionController)
    public function toggleStatus($id)
    {
        $user = User::findOrFail($id);

        // Tanda ! = NOT (kebalikan)
        // Kalau status_aktif = 1 (true/aktif) → !1 = 0 (false/nonaktif)
        // Kalau status_aktif = 0 (false/nonaktif) → !0 = 1 (true/aktif)
        $user->status_aktif = !$user->status_aktif;
        $user->save();

        return back()->with('success', 'Status user berhasil diubah!');
    }

    // --- TAMBAH AREA PARKIR BARU ---
    // Dipanggil saat admin submit form tambah area (POST /admin/areas)
    public function storeArea(Request $request)
    {
        $request->validate([
            'nama_area' => 'required|string|max:100',     // Nama area wajib diisi
            'kapasitas' => 'required|integer|min:1',       // Kapasitas minimal 1 slot
        ]);

        // Buat area baru di database
        // terisi = 0 karena area baru belum ada kendaraan sama sekali
        Area::create([
            'nama_area' => $request->nama_area,
            'kapasitas' => $request->kapasitas,
            'terisi' => 0,
        ]);

        // with('active_tab', 'tarif') = supaya setelah redirect, halaman admin
        // tetap menampilkan tab "Tarif & Area" (bukan balik ke tab Users)
        return redirect()->back()->with('success', 'Area ' . $request->nama_area . ' berhasil ditambahkan!')->with('active_tab', 'tarif');
    }

    // --- EDIT AREA ---
    // Mengubah nama dan/atau kapasitas area yang sudah ada
    public function updateArea(Request $request, $id)
    {
        // Cari area, kalau ID tidak ditemukan → error 404
        $area = Area::findOrFail($id);

        $request->validate([
            'nama_area' => 'required|string|max:100',
            'kapasitas' => 'required|integer|min:1',
        ]);

        // Update data area di database
        $area->update([
            'nama_area' => $request->nama_area,
            'kapasitas' => $request->kapasitas,
        ]);

        return redirect()->back()->with('success', 'Area ' . $area->nama_area . ' berhasil diperbarui!')->with('active_tab', 'tarif');
    }

    // --- TOGGLE AREA (ENABLE ↔ DISABLE) ---
    // Mengaktifkan atau menonaktifkan area parkir
    // Area nonaktif: kapasitas diset 0 → tidak bisa dipilih petugas saat input kendaraan masuk
    public function toggleArea($id)
    {
        $area = Area::findOrFail($id);

        // Kalau kapasitas > 0 (area AKTIF) → NONAKTIFKAN
        // Set kapasitas = 0, terisi = 0 → area dianggap tidak beroperasi
        if ($area->kapasitas > 0) {
            $area->update(['kapasitas' => 0, 'terisi' => 0]);
            return back()->with('success', 'Area ' . $area->nama_area . ' dinonaktifkan!')->with('active_tab', 'tarif');
        }
        // Kalau kapasitas = 0 (area NONAKTIF) → AKTIFKAN KEMBALI
        // Set kapasitas = 50 (nilai default)
        else {
            $area->update(['kapasitas' => 50]);
            return back()->with('success', 'Area ' . $area->nama_area . ' diaktifkan kembali!')->with('active_tab', 'tarif');
        }
    }

    // --- TAMBAH TARIF BARU ---
    // Menambahkan jenis kendaraan + harga tarif baru (misal: Truk, Bus, Sepeda)
    public function storeTarif(Request $request)
    {
        // 'unique:tb_tarif,jenis_kendaraan' = jenis kendaraan harus unik
        // Tidak boleh ada 2 tarif "Motor" dalam database
        $request->validate([
            'jenis_kendaraan' => 'required|string|max:50|unique:tb_tarif,jenis_kendaraan',
            'tarif_per_jam' => 'required|numeric|min:0',
        ]);

        Tarif::create([
            'jenis_kendaraan' => $request->jenis_kendaraan,
            'tarif_per_jam' => $request->tarif_per_jam,
        ]);

        return redirect()->back()->with('success', 'Tarif ' . $request->jenis_kendaraan . ' berhasil ditambahkan!')->with('active_tab', 'tarif');
    }

    // --- EDIT HARGA TARIF ---
    // Mengubah harga tarif per jam untuk jenis kendaraan tertentu
    public function updateTarif(Request $request, $id)
    {
        $request->validate([
            'tarif_per_jam' => 'required|numeric|min:0',    // Harga wajib diisi, minimal Rp 0
        ]);

        // Cari tarif berdasarkan ID, kalau gak ada → error 404
        $tarif = Tarif::findOrFail($id);

        // Update harga tarif di database
        $tarif->update([
            'tarif_per_jam' => $request->tarif_per_jam,
        ]);

        return redirect()->back()->with('success', 'Tarif ' . $tarif->jenis_kendaraan . ' berhasil diubah!')->with('active_tab', 'tarif');
    }
}
