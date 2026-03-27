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
        $start = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $end = $request->get('end_date', Carbon::now()->format('Y-m-d'));

        $areas = Area::all();
        $totalPetugas = User::where('role', 'petugas')->count();

        // Log Aktivitas
        $logs = Transaksi::with(['kendaraan', 'area'])
                ->latest('waktu_masuk')
                ->take(12)
                ->get();

        // Statistik
        $totalPendapatan = Transaksi::where('status', 'keluar')
                ->whereBetween('waktu_masuk', [$start . ' 00:00:00', $end . ' 23:59:59'])
                ->sum('biaya_total');

        $kendaraanPeriode = Transaksi::whereBetween('waktu_masuk', [$start . ' 00:00:00', $end . ' 23:59:59'])
                ->count();

        // Rincian
        $rincianPendapatan = Transaksi::with('kendaraan')
                ->where('status', 'keluar')
                ->whereBetween('waktu_masuk', [$start . ' 00:00:00', $end . ' 23:59:59'])
                ->latest('waktu_keluar')
                ->take(10)
                ->get();

        // Grafik 7 hari
        $dailyStats = Transaksi::select(
                DB::raw('DATE(waktu_masuk) as date'),
                DB::raw('SUM(biaya_total) as total')
            )
            ->where('status', 'keluar')
            ->where('waktu_masuk', '>=', Carbon::now()->subDays(6))
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->get();

        $chartLabels = $dailyStats->map(function ($stat) {
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
