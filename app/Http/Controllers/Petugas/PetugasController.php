<?php
// FILE: PetugasController.php
// FUNGSI: Controller utama untuk halaman Petugas (operator lapangan).
//         Mengatur semua fitur operasional parkir:
//         - Dashboard petugas (jam real-time, tab-tab operasional)
//         - Input Kendaraan Masuk → validasi → cek dobel → cek slot → simpan → cetak karcis
//         - Proses Kendaraan Keluar → cari transaksi → hitung durasi & biaya → bayar → cetak struk
//         - Monitoring kendaraan yang sedang parkir (tab Parkir)
//         - Riwayat transaksi harian (tab Aktivitas)
//         - Cetak karcis masuk dan struk keluar

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;   // Class dasar controller
use App\Models\Transaksi;               // Model → tabel tb_transaksi
use App\Models\Tarif;                   // Model → tabel tb_tarif
use App\Models\Area;                    // Model → tabel tb_area_parkir
use App\Models\Kendaraan;               // Model → tabel tb_kendaraan
use Illuminate\Http\Request;            // Object data dari browser
use Illuminate\Support\Facades\Auth;    // Helper login (untuk ambil ID petugas yang login)
use Carbon\Carbon;                      // Library PHP untuk mengolah tanggal & waktu

class PetugasController extends Controller
{
    // --- DASHBOARD PETUGAS ---
    // Menyiapkan semua data yang dibutuhkan di halaman petugas:
    // tarif (untuk dropdown), area (untuk dropdown & denah), kendaraan aktif, riwayat
    public function index(Request $request)
    {
        // Ambil semua tarif (Motor, Mobil, dll) → untuk dropdown pilihan jenis kendaraan
        $tarifs = Tarif::all();

        // Ambil SEMUA area (termasuk yang penuh/nonaktif) → untuk tab "Area" (denah visual)
        $allAreas = Area::all();

        // Filter area yang TERSEDIA saja → untuk dropdown form "Kendaraan Masuk"
        // Syarat tersedia: terisi < kapasitas (masih ada slot) DAN kapasitas > 0 (tidak dinonaktifkan)
        // fn($a) => ... adalah arrow function (fungsi singkat tanpa keyword 'function')
        $areas = $allAreas->filter(fn($a) => $a->terisi < $a->kapasitas && $a->kapasitas > 0);

        // Ambil tanggal filter dari URL (default = hari ini)
        // Dipakai untuk filter tab "Aktivitas" (riwayat transaksi)
        $tanggalFilter = $request->input('tanggal', Carbon::today()->toDateString());

        // Ambil daftar kendaraan yang SEDANG PARKIR (status masih 'masuk')
        // Data ini diubah formatnya pakai map() supaya gampang dipakai di JavaScript
        // with(['kendaraan', 'tarif']) = ikut ambil data dari tabel kendaraan dan tarif
        $kendaraanAktif = Transaksi::where('status', 'masuk')
            ->with(['kendaraan', 'tarif'])
            ->get()
            ->map(function ($item) {
                return [
                    'id_parkir' => $item->id_parkir,
                    'plat_nomor' => strtoupper($item->kendaraan->plat_nomor ?? ''),
                    'waktu_masuk' => $item->waktu_masuk,
                    'jenis_kendaraan' => $item->kendaraan->jenis_kendaraan ?? '',
                    'tarif_per_jam' => (int) ($item->tarif->tarif_per_jam ?? 0),
                ];
            });

        // Ambil riwayat transaksi pada tanggal yang dipilih
        // Untuk ditampilkan di tab "Aktivitas"
        $transaksis = Transaksi::with(['kendaraan', 'tarif', 'area'])
            ->whereDate('waktu_masuk', $tanggalFilter)
            ->orderBy('waktu_masuk', 'desc')
            ->get();

        // Kirim semua data ke tampilan petugas
        return view('dashboards.petugas', [
            'tarifs' => $tarifs,                   // Dropdown jenis kendaraan
            'areas' => $areas,                     // Dropdown area (yang tersedia)
            'allAreas' => $allAreas,               // Semua area (untuk denah di tab Area)
            'kendaraanAktif' => $kendaraanAktif,   // List kendaraan yang sedang parkir
            'transaksis' => $transaksis,           // Riwayat transaksi harian
            'tanggalTerpilih' => $tanggalFilter,   // Tanggal yang sedang difilter
        ]);
    }

    // --- KENDARAAN MASUK ---
    // Alur lengkap saat petugas input kendaraan baru masuk:
    // 1. Validasi form (plat, tarif, area wajib diisi)
    // 2. Bersihkan plat nomor (hapus spasi, huruf kapital)
    // 3. Cek apakah kendaraan ini masih parkir di dalam (anti dobel)
    // 4. Cek apakah area tujuan masih ada slot kosong
    // 5. Cari/buat data kendaraan di database
    // 6. Buat transaksi baru (status: masuk)
    // 7. Tambah slot terisi +1 di area
    // 8. Redirect + auto-print karcis
    public function kendaraanMasuk(Request $request)
    {
        // Validasi: semua field harus diisi
        $request->validate([
            'plat_nomor' => 'required|string',
            'id_tarif' => 'required',
            'id_area' => 'required',
        ]);

        // Bersihkan plat nomor: hapus semua spasi + jadikan huruf kapital
        // Contoh: "b 1234 abc" → "B1234ABC"
        // Ini supaya format plat konsisten di database
        $platNomor = strtoupper(str_replace(' ', '', $request->plat_nomor));

        // Cari data tarif yang dipilih
        $tarif = Tarif::find($request->id_tarif);
        if (!$tarif) {
            return redirect()->back()->with('error', 'Tarif tidak ditemukan!');
        }

        // CEK DOBEL: Apakah kendaraan dengan plat ini MASIH ADA di dalam parkir?
        // whereHas() = cari transaksi yang punya kendaraan dengan plat tertentu
        // Cara kerjanya: cek tabel tb_transaksi yang relasi ke tb_kendaraan
        // dimana plat_nomor cocok DAN status masih 'masuk'
        // first() = ambil 1 data pertama yang cocok (null kalau gak ada)
        $cekParkir = Transaksi::whereHas('kendaraan', function ($q) use ($platNomor) {
            $q->where('plat_nomor', $platNomor);
        })->where('status', 'masuk')->first();

        // Kalau ketemu → kendaraan masih di dalam → TOLAK, gak bisa masuk 2x
        if ($cekParkir) {
            return redirect()->back()->with('error', 'Kendaraan plat ' . $request->plat_nomor . ' masih ada di dalam!');
        }

        // CEK SLOT: Apakah area yang dipilih masih ada tempat kosong?
        // terisi >= kapasitas berarti PENUH
        $area = Area::find($request->id_area);
        if (!$area || $area->terisi >= $area->kapasitas) {
            return redirect()->back()->with('error', 'Maaf, slot di area ini sudah penuh!');
        }

        // CARI ATAU BUAT DATA KENDARAAN
        // firstOrCreate() = cari kendaraan dengan plat_nomor ini di database
        //   → Kalau SUDAH ADA → pakai data yang sudah ada (gak buat baru)
        //   → Kalau BELUM ADA → buat data kendaraan baru dengan info yang diberikan
        // Gunanya: supaya kendaraan yang sering parkir tidak buat entry baru terus
        $kendaraan = Kendaraan::firstOrCreate(
            ['plat_nomor' => $platNomor],                               // Cari berdasarkan plat
            [                                                            // Data kalau bikin baru:
                'jenis_kendaraan' => $tarif->jenis_kendaraan,           //   Jenis dari tarif (Motor/Mobil)
                'warna' => $request->input('warna', '-'),                //   Warna (default: -)
                'pemilik' => $request->input('pemilik', '-'),            //   Pemilik (default: -)
                'id_user' => Auth::id(),                                  //   ID petugas yang input
            ]
        );

        // BUAT TRANSAKSI BARU dengan status 'masuk'
        // Carbon::now() = ambil tanggal & waktu SEKARANG (misal: 2026-03-27 14:30:00)
        $transaksi = Transaksi::create([
            'id_kendaraan' => $kendaraan->id_kendaraan,
            'id_tarif' => $tarif->id_tarif,
            'id_area' => $request->id_area,
            'id_user' => Auth::id(),           // ID petugas yang sedang login
            'waktu_masuk' => Carbon::now(),     // Waktu sekarang
            'status' => 'masuk',
        ]);

        // TAMBAH SLOT TERISI di area +1
        // increment('terisi') = UPDATE tb_area_parkir SET terisi = terisi + 1
        // Contoh: kalau terisi = 5, setelah increment jadi 6
        $area->increment('terisi');

        // Redirect balik ke halaman petugas
        // cetak_id = ID transaksi untuk auto-print karcis masuk
        return redirect()->back()->with([
            'success' => 'Karcis berhasil dicetak untuk ' . $request->plat_nomor,
            'active_tab' => 'masuk',
            'cetak_id' => $transaksi->id_parkir,
        ]);
    }

    // --- KENDARAAN KELUAR ---
    // Alur lengkap saat petugas proses kendaraan keluar:
    // 1. Cari transaksi aktif berdasarkan plat nomor
    // 2. Hitung durasi parkir (waktu masuk → waktu sekarang)
    // 3. Update transaksi: isi waktu keluar, durasi, biaya, status = 'keluar'
    // 4. Kurangi slot terisi -1 di area
    // 5. Redirect + auto-print struk pembayaran
    public function kendaraanKeluar(Request $request)
    {
        $request->validate([
            'plat_nomor' => 'required',
            'total_tagihan' => 'required|numeric',
        ]);

        $platInput = strtoupper(str_replace(' ', '', $request->plat_nomor));

        // Cari transaksi AKTIF (status = 'masuk') dengan plat nomor ini
        // Kalau gak ketemu → $transaksi = null
        $transaksi = Transaksi::whereHas('kendaraan', function ($q) use ($platInput) {
            $q->where('plat_nomor', $platInput);
        })->where('status', 'masuk')->first();

        if ($transaksi) {
            // HITUNG DURASI PARKIR
            // Carbon::parse() = ubah string tanggal jadi object Carbon
            // diffInHours() = hitung selisih waktu dalam JAM
            // max(1, ...) = minimal 1 jam (kalau parkir 30 menit tetap dihitung 1 jam)
            $waktuMasuk = Carbon::parse($transaksi->waktu_masuk);
            $waktuKeluar = Carbon::now();
            $durasi = max(1, $waktuMasuk->diffInHours($waktuKeluar, true) ?: 1);

            // UPDATE TRANSAKSI → isi data keluar dan ubah status
            // ceil() = bulatkan ke atas (2.3 jam → 3 jam)
            $transaksi->update([
                'waktu_keluar' => $waktuKeluar,
                'durasi_jam' => ceil($durasi),
                'biaya_total' => $request->total_tagihan,
                'status' => 'keluar',              // Status berubah dari 'masuk' → 'keluar'
            ]);

            // KURANGI SLOT TERISI di area -1
            // decrement('terisi') = UPDATE tb_area_parkir SET terisi = terisi - 1
            // Cek dulu: terisi > 0 supaya tidak jadi negatif
            $area = Area::find($transaksi->id_area);
            if ($area && $area->terisi > 0) {
                $area->decrement('terisi');
            }

            // Redirect + data untuk auto-print struk keluar (pembayaran LUNAS)
            return redirect()->back()->with([
                'success' => 'Pembayaran Berhasil!',
                'active_tab' => 'keluar',
                'terbayar' => $request->plat_nomor,
                'total_bayar' => $request->total_tagihan,
                'cetak_keluar_id' => $transaksi->id_parkir,
            ]);
        }

        // Kalau transaksi tidak ditemukan (plat salah / tidak ada di dalam)
        return redirect()->back()->with([
            'error' => 'Data kendaraan tidak ditemukan!',
            'active_tab' => 'keluar',
        ]);
    }

    // --- CETAK KARCIS MASUK ---
    // Menampilkan halaman struk thermal (80mm) untuk kendaraan yang baru masuk
    // Halaman ini otomatis trigger window.print() saat dibuka
    public function cetakKarcis($id)
    {
        // Ambil data transaksi beserta relasi-nya (kendaraan, area, tarif)
        // findOrFail() = kalau ID gak ketemu → error 404
        $transaksi = Transaksi::with(['kendaraan', 'area', 'tarif'])->findOrFail($id);
        return view('petugas.cetak', compact('transaksi'));
    }

    // --- CETAK STRUK KELUAR (STRUK PEMBAYARAN) ---
    // Menampilkan struk pembayaran dengan stempel LUNAS
    // Termasuk info: plat, area, durasi, biaya, petugas yang menangani
    public function cetakStrukKeluar($id)
    {
        $transaksi = Transaksi::with(['kendaraan', 'area', 'tarif', 'user'])->findOrFail($id);
        return view('petugas.cetak_keluar', compact('transaksi'));
    }
}
