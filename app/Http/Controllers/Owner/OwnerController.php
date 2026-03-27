<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Transaksi;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class OwnerController extends Controller
{
    public function index(Request $request)
    {
        // 1. Ambil Filter Tanggal untuk Statistik Card & Tabel Rincian
        // Default: Dari awal bulan ini sampai hari ini
        $start = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $end = $request->get('end_date', Carbon::now()->format('Y-m-d'));

        // 2. Data Master & Log
        $areas = Area::all();
        $totalPetugas = User::where('role', 'petugas')->count();

        // Log Aktivitas (Semua traffic: masuk & keluar)
        $logs = Transaksi::leftJoin('area_parkirs', 'transaksis.area_id', '=', 'area_parkirs.area_id')
                ->select('transaksis.*', 'area_parkirs.nama_area')
                ->latest('transaksis.created_at')
                ->take(12)
                ->get();

        // 3. Statistik Card (Sinkron dengan Filter Tanggal)
        $totalPendapatan = Transaksi::where('status', 'selesai')
                ->whereBetween('created_at', [$start . ' 00:00:00', $end . ' 23:59:59'])
                ->sum('total_bayar');

        $kendaraanPeriode = Transaksi::whereBetween('created_at', [$start . ' 00:00:00', $end . ' 23:59:59'])
                ->count();

        // 4. Rincian Pendapatan (Tabel di samping grafik)
        $rincianPendapatan = Transaksi::where('status', 'selesai')
                ->whereBetween('created_at', [$start . ' 00:00:00', $end . ' 23:59:59'])
                ->latest()
                ->take(10)
                ->get();

        // 5. QUERY GRAFIK (7 Hari Terakhir)
        // Ini yang bikin grafiknya jadi 7 batang supaya Owner bisa liat tren harian
        $dailyStats = Transaksi::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total_bayar) as total')
            )
            ->where('status', 'selesai')
            ->where('created_at', '>=', Carbon::now()->subDays(6)) // Ambil 7 hari ke belakang
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->get();

        // Format Label:
        $chartLabels = $dailyStats->map(function($stat) {
            return Carbon::parse($stat->date)->translatedFormat('D, d M');
        })->toArray();

        $chartData = $dailyStats->pluck('total')->toArray();

        return view('dashboards.owner', compact(
            'areas',
            'logs',
            'totalPendapatan',
            'kendaraanPeriode',
            'totalPetugas',
            'chartLabels',
            'chartData',
            'rincianPendapatan'
        ));
    }
}
