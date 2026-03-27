<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tarif;
use Illuminate\Http\Request;

class TarifController extends Controller {
    public function index() {
        $tarifs = Tarif::all();
        return view('dashboards.admin', compact('tarifs')); // Kita pakai satu view admin untuk semua master data
    }

    public function store(Request $request) {
        $request->validate([
            'jenis_kendaraan' => 'required',
            'harga_per_jam' => 'required|numeric',
        ]);

        Tarif::create($request->all());
        return redirect()->back()->with('success', 'Tarif berhasil ditambahkan!');
    }

    public function destroy($id) {
        Tarif::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Tarif dihapus!');
    }
}
