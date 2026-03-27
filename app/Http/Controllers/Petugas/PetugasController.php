<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use App\Models\Transaksi;
use App\Models\Tarif;
use App\Models\Area;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PetugasController extends Controller
{
    public function index(Request $request)
    {
        $tarifs = Tarif::all();
        $areas = Area::where('is_active', 1)->get();
        $tanggalFilter = $request->input('tanggal', Carbon::today()->toDateString());

        $kendaraanAktif = Transaksi::where('status', 'parkir')
            ->get()
            ->map(function ($item) {
                return [
                    'plat_nomor' => strtoupper($item->plat_nomor),
                    'jam_masuk' => $item->jam_masuk,
                    'jenis_kendaraan' => $item->jenis_kendaraan,
                    'harga_per_jam' => (int) $item->harga_per_jam,
                ];
            });

        // Ambil riwayat berdasarkan tanggal yang dipilih
        $transaksis = Transaksi::whereDate('created_at', $tanggalFilter)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('dashboards.petugas', [
            'tarifs' => $tarifs,
            'areas' => $areas,
            'kendaraanAktif' => $kendaraanAktif,
            'transaksis' => $transaksis,
            'tanggalTerpilih' => $tanggalFilter
        ]);
    }

    public function kendaraanMasuk(Request $request)
    {
        $request->validate([
            'plat_nomor' => 'required|string',
            'tarif_id' => 'required',
            'area_id' => 'required',
        ]);

        $platNomor = strtoupper(str_replace(' ', '', $request->plat_nomor));

        $tarif = Tarif::find($request->tarif_id);
        if (!$tarif) {
            return redirect()->back()->with('error', 'Tarif tidak ditemukan!');
        }

        // Cek apakah kendaraan masih ada di dalam
        $cekParkir = Transaksi::where('plat_nomor', $platNomor)
            ->where('status', 'parkir')
            ->first();

        if ($cekParkir) {
            return redirect()->back()->with('error', 'Kendaraan plat ' . $request->plat_nomor . ' masih ada di dalam!');
        }

        // Cek ketersediaan slot sebelum masuk
        $area = Area::where('area_id', $request->area_id)->first();
        if (!$area || $area->slot_tersedia <= 0) {
            return redirect()->back()->with('error', 'Maaf, slot di area ini sudah penuh!');
        }

        // 1. Simpan transaksi baru (User ID otomatis dari yang login)
        $transaksi = Transaksi::create([
            'plat_nomor' => $platNomor,
            'jenis_kendaraan' => $tarif->jenis_kendaraan,
            'harga_per_jam' => $tarif->harga_per_jam,
            'area_id' => $request->area_id,
          'user_id' => \Illuminate\Support\Facades\Auth::id(),
            'jam_masuk' => Carbon::now(),
            'status' => 'parkir'
        ]);

        // 2. Kurangi slot area (Cukup sekali saja)
        $area->decrement('slot_tersedia');

        return redirect()->back()->with([
            'success' => 'Karcis berhasil dicetak untuk ' . $request->plat_nomor,
            'active_tab' => 'masuk',
            'cetak_id' => $transaksi->id
        ]);
    }

    public function kendaraanKeluar(Request $request)
    {
        $request->validate([
            'plat_nomor' => 'required',
            'total_tagihan' => 'required|numeric',
        ]);

        $platInput = strtoupper(str_replace(' ', '', $request->plat_nomor));

        $transaksi = Transaksi::where('plat_nomor', $platInput)
            ->where('status', 'parkir')
            ->first();

        if ($transaksi) {
            // Update transaksi
            $transaksi->update([
                'jam_keluar' => Carbon::now(),
                'total_bayar' => $request->total_tagihan,
                'status' => 'selesai'
            ]);

            // Tambah kembali slot area
            $area = Area::where('area_id', $transaksi->area_id)->first();
            if ($area) {
                $area->increment('slot_tersedia');
            }

            return redirect()->back()->with([
                'success' => 'Pembayaran Berhasil!',
                'active_tab' => 'keluar',
                'terbayar' => $request->plat_nomor,
                'total_bayar' => $request->total_tagihan
            ]);
        }

        return redirect()->back()->with([
            'error' => 'Data kendaraan tidak ditemukan!',
            'active_tab' => 'keluar'
        ]);
    }

    public function cetakKarcis($id)
    {
        $transaksi = Transaksi::with('area')->findOrFail($id);
        return view('petugas.cetak', compact('transaksi'));
    }
}
