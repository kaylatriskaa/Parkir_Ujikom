<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use App\Models\Transaksi;
use App\Models\Tarif;
use App\Models\Area;
use App\Models\Kendaraan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PetugasController extends Controller
{
    public function index(Request $request)
    {
        $tarifs = Tarif::all();
        $areas = Area::whereRaw('terisi < kapasitas')->get();
        $tanggalFilter = $request->input('tanggal', Carbon::today()->toDateString());

        // Kendaraan aktif (yang masih parkir)
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

        // Riwayat transaksi
        $transaksis = Transaksi::with(['kendaraan', 'tarif', 'area'])
            ->whereDate('waktu_masuk', $tanggalFilter)
            ->orderBy('waktu_masuk', 'desc')
            ->get();

        return view('dashboards.petugas', [
            'tarifs' => $tarifs,
            'areas' => $areas,
            'kendaraanAktif' => $kendaraanAktif,
            'transaksis' => $transaksis,
            'tanggalTerpilih' => $tanggalFilter,
        ]);
    }

    public function kendaraanMasuk(Request $request)
    {
        $request->validate([
            'plat_nomor' => 'required|string',
            'id_tarif' => 'required',
            'id_area' => 'required',
        ]);

        $platNomor = strtoupper(str_replace(' ', '', $request->plat_nomor));

        $tarif = Tarif::find($request->id_tarif);
        if (!$tarif) {
            return redirect()->back()->with('error', 'Tarif tidak ditemukan!');
        }

        // Cek kendaraan masih parkir
        $cekParkir = Transaksi::whereHas('kendaraan', function ($q) use ($platNomor) {
            $q->where('plat_nomor', $platNomor);
        })->where('status', 'masuk')->first();

        if ($cekParkir) {
            return redirect()->back()->with('error', 'Kendaraan plat ' . $request->plat_nomor . ' masih ada di dalam!');
        }

        // Cek slot
        $area = Area::find($request->id_area);
        if (!$area || $area->terisi >= $area->kapasitas) {
            return redirect()->back()->with('error', 'Maaf, slot di area ini sudah penuh!');
        }

        // Buat / cari data kendaraan
        $kendaraan = Kendaraan::firstOrCreate(
            ['plat_nomor' => $platNomor],
            [
                'jenis_kendaraan' => $tarif->jenis_kendaraan,
                'warna' => $request->input('warna', '-'),
                'pemilik' => $request->input('pemilik', '-'),
                'id_user' => Auth::id(),
            ]
        );

        // Buat transaksi
        $transaksi = Transaksi::create([
            'id_kendaraan' => $kendaraan->id_kendaraan,
            'id_tarif' => $tarif->id_tarif,
            'id_area' => $request->id_area,
            'id_user' => Auth::id(),
            'waktu_masuk' => Carbon::now(),
            'status' => 'masuk',
        ]);

        // Tambah slot terisi
        $area->increment('terisi');

        return redirect()->back()->with([
            'success' => 'Karcis berhasil dicetak untuk ' . $request->plat_nomor,
            'active_tab' => 'masuk',
            'cetak_id' => $transaksi->id_parkir,
        ]);
    }

    public function kendaraanKeluar(Request $request)
    {
        $request->validate([
            'plat_nomor' => 'required',
            'total_tagihan' => 'required|numeric',
        ]);

        $platInput = strtoupper(str_replace(' ', '', $request->plat_nomor));

        $transaksi = Transaksi::whereHas('kendaraan', function ($q) use ($platInput) {
            $q->where('plat_nomor', $platInput);
        })->where('status', 'masuk')->first();

        if ($transaksi) {
            $waktuMasuk = Carbon::parse($transaksi->waktu_masuk);
            $waktuKeluar = Carbon::now();
            $durasi = max(1, $waktuMasuk->diffInHours($waktuKeluar, true) ?: 1);

            $transaksi->update([
                'waktu_keluar' => $waktuKeluar,
                'durasi_jam' => ceil($durasi),
                'biaya_total' => $request->total_tagihan,
                'status' => 'keluar',
            ]);

            // Kurangi slot terisi
            $area = Area::find($transaksi->id_area);
            if ($area && $area->terisi > 0) {
                $area->decrement('terisi');
            }

            return redirect()->back()->with([
                'success' => 'Pembayaran Berhasil!',
                'active_tab' => 'keluar',
                'terbayar' => $request->plat_nomor,
                'total_bayar' => $request->total_tagihan,
            ]);
        }

        return redirect()->back()->with([
            'error' => 'Data kendaraan tidak ditemukan!',
            'active_tab' => 'keluar',
        ]);
    }

    public function cetakKarcis($id)
    {
        $transaksi = Transaksi::with(['kendaraan', 'area', 'tarif'])->findOrFail($id);
        return view('petugas.cetak', compact('transaksi'));
    }
}
