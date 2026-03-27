<?php
// FILE: OwnerController.php
// FUNGSI: Controller utama untuk halaman Owner (Pemilik Bisnis Parkir).
//         Mengatur semua fitur pemantauan pendapatan:
//         - Dashboard pendapatan + statistik kendaraan & petugas
//         - Grafik pendapatan 7 hari terakhir (ditampilkan pakai Chart.js)
//         - Filter laporan berdasarkan rentang tanggal
//         - Rincian transaksi yang sudah bayar
//         - Export laporan pendapatan ke PDF (via popup print)

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;   // Class dasar controller
use App\Models\Area;                    // Model → tabel tb_area_parkir
use App\Models\Transaksi;               // Model → tabel tb_transaksi
use App\Models\User;                    // Model → tabel tb_user
use Illuminate\Http\Request;            // Object data dari browser
use Illuminate\Support\Facades\DB;      // Facade DB → untuk query SQL mentah (raw query)
use Carbon\Carbon;                      // Library tanggal & waktu

class OwnerController extends Controller
{
    // --- DASHBOARD OWNER ---
    // Menampilkan ringkasan pendapatan, statistik, grafik, dan rincian transaksi
    // Data bisa difilter berdasarkan rentang tanggal (start_date & end_date)
    public function index(Request $request)
    {
        // Ambil rentang tanggal dari filter URL
        // Default: awal bulan ini sampai hari ini
        // Contoh: ?start_date=2026-03-01&end_date=2026-03-27
        // Kalau tidak ada parameter → pakai default
        $start = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $end = $request->get('end_date', Carbon::now()->format('Y-m-d'));

        // Ambil semua data area (untuk info ketersediaan slot)
        $areas = Area::all();

        // Hitung total petugas yang terdaftar di sistem
        // where('role', 'petugas') → hanya hitung yang role-nya petugas
        $totalPetugas = User::where('role', 'petugas')->count();

        // Ambil 12 log transaksi terbaru (untuk tabel aktivitas di dashboard)
        $logs = Transaksi::with(['kendaraan', 'area'])
                ->latest('waktu_masuk')
                ->take(12)
                ->get();

        // HITUNG TOTAL PENDAPATAN dalam rentang tanggal
        // Hanya hitung yang status 'keluar' (sudah bayar)
        // whereBetween() = WHERE waktu_masuk BETWEEN '2026-03-01 00:00:00' AND '2026-03-27 23:59:59'
        // sum('biaya_total') = jumlahkan semua biaya → hasilnya: 500000 (misalnya)
        $totalPendapatan = Transaksi::where('status', 'keluar')
                ->whereBetween('waktu_masuk', [$start . ' 00:00:00', $end . ' 23:59:59'])
                ->sum('biaya_total');

        // Hitung total SEMUA kendaraan (masuk + keluar) dalam rentang tanggal
        $kendaraanPeriode = Transaksi::whereBetween('waktu_masuk', [$start . ' 00:00:00', $end . ' 23:59:59'])
                ->count();

        // Ambil 10 rincian transaksi terakhir yang sudah bayar
        // Data ini ditampilkan di tabel rincian pendapatan
        $rincianPendapatan = Transaksi::with('kendaraan')
                ->where('status', 'keluar')
                ->whereBetween('waktu_masuk', [$start . ' 00:00:00', $end . ' 23:59:59'])
                ->latest('waktu_keluar')
                ->take(10)
                ->get();

        // --- GRAFIK PENDAPATAN 7 HARI TERAKHIR ---
        // Data ini dikirim ke Chart.js untuk membuat diagram batang
        //
        // Query SQL mentah (via DB::raw):
        //   SELECT DATE(waktu_masuk) as date, SUM(biaya_total) as total
        //   FROM tb_transaksi
        //   WHERE status = 'keluar' AND waktu_masuk >= 7 hari lalu
        //   GROUP BY date
        //   ORDER BY date ASC
        //
        // Hasilnya: [
        //   {date: '2026-03-21', total: 50000},
        //   {date: '2026-03-22', total: 75000},
        //   {date: '2026-03-23', total: 60000},
        //   ...
        // ]
        $dailyStats = Transaksi::select(
                DB::raw('DATE(waktu_masuk) as date'),      // Ambil tanggalnya saja (tanpa jam)
                DB::raw('SUM(biaya_total) as total')        // Jumlahkan biaya per tanggal
            )
            ->where('status', 'keluar')                     // Hanya yang sudah bayar
            ->where('waktu_masuk', '>=', Carbon::now()->subDays(6))  // 7 hari ke belakang
            ->groupBy('date')                                // Kelompokkan per tanggal
            ->orderBy('date', 'ASC')                         // Urutkan dari tanggal terlama
            ->get();

        // Ubah format tanggal jadi label yang mudah dibaca untuk grafik
        // Contoh: "2026-03-25" → "Sel, 25 Mar"
        // translatedFormat() = format tanggal dalam bahasa Indonesia
        $chartLabels = $dailyStats->map(function ($stat) {
            return Carbon::parse($stat->date)->translatedFormat('D, d M');
        })->toArray();

        // Ambil angka pendapatan-nya saja (untuk data Y-axis grafik)
        // pluck('total') = ambil kolom 'total' saja dari collection
        // Contoh: [50000, 75000, 60000]
        $chartData = $dailyStats->pluck('total')->toArray();

        // Kirim semua data ke tampilan owner
        return view('dashboards.owner', compact(
            'areas', 'logs', 'totalPendapatan', 'kendaraanPeriode',
            'totalPetugas', 'chartLabels', 'chartData', 'rincianPendapatan'
        ));
    }

    // --- EXPORT PDF ---
    // Menampilkan halaman laporan pendapatan dalam format siap cetak
    // Halaman ini dibuka via popup window (bukan tab baru)
    // User tinggal klik Ctrl+P atau tombol Print → lalu "Save as PDF"
    public function exportPdf(Request $request)
    {
        // Ambil rentang tanggal dari parameter URL
        $start = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $end = $request->get('end_date', Carbon::now()->format('Y-m-d'));

        // Ambil semua transaksi yang sudah selesai (status = 'keluar')
        // with([...]) = ikut ambil data kendaraan, tarif, area, dan user (petugas)
        // Data ini ditampilkan di tabel laporan PDF
        $transaksis = Transaksi::with(['kendaraan', 'tarif', 'area', 'user'])
            ->where('status', 'keluar')
            ->whereBetween('waktu_masuk', [$start . ' 00:00:00', $end . ' 23:59:59'])
            ->orderBy('waktu_keluar', 'desc')
            ->get();

        // Hitung ringkasan untuk header laporan
        $totalPendapatan = $transaksis->sum('biaya_total');   // Total semua biaya
        $totalKendaraan = $transaksis->count();                // Total jumlah kendaraan

        // Tampilkan halaman laporan_pdf.blade.php (di dalam popup)
        return view('owner.laporan_pdf', compact(
            'transaksis', 'totalPendapatan', 'totalKendaraan', 'start', 'end'
        ));
    }
}
