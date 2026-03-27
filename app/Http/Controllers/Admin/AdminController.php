<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Tarif;
use App\Models\Area;
use App\Models\Transaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function index()
    {
        // 1. Ambil data petugas & owner (Kecuali admin yang sedang login)
        $users = User::whereIn('role', ['petugas', 'owner'])
                    ->where('id', '!=', Auth::id())
                    ->get();

        // 2. Ambil data tarif & area
        $tarifs = Tarif::all();

        // Safety: Pastikan slot_tersedia tidak melebihi kapasitas saat ditampilkan
        $areas = Area::all()->map(function($area) {
            if ($area->slot_tersedia > $area->kapasitas) {
                $area->slot_tersedia = $area->kapasitas;
            }
            return $area;
        });

        // 3. Ambil log aktivitas terbaru
        // Gunakan try-catch agar tidak crash jika tabel transaksi belum ada/kosong
        try {
            $logs = Transaksi::with(['user', 'area'])->latest()->take(10)->get();
        } catch (\Exception $e) {
            $logs = collect();
        }

        // 4. Hitung total logs khusus hari ini untuk card statistik
        $totalLogsCount = Transaksi::whereDate('created_at', today())->count();

        return view('dashboards.admin', compact('users', 'tarifs', 'areas', 'logs', 'totalLogsCount'));
    }

    // --- MANAJEMEN USER ---
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'role' => 'required|in:petugas,owner'
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        return redirect()->back()->with('success', 'Akun ' . $request->name . ' berhasil dibuat!');
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'role' => 'required|in:petugas,owner'
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->role = $request->role;

        if ($request->filled('password')) {
            $request->validate(['password' => 'min:6']);
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return redirect()->back()->with('success', 'Data user berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);

        if ($user->id === Auth::id()) {
            return redirect()->back()->with('error', 'Dilarang menghapus akun sendiri!');
        }

        $user->delete();
        return redirect()->back()->with('success', 'User berhasil dihapus!');
    }

    // --- MANAJEMEN TARIF ---
    public function updateTarif(Request $request, $id)
    {
        $request->validate([
            'harga_per_jam' => 'required|numeric|min:0',
        ]);

        $tarif = Tarif::findOrFail($id);
        $tarif->update([
            'harga_per_jam' => $request->harga_per_jam,
        ]);

        return redirect()->back()->with('success', 'Tarif ' . $tarif->jenis_kendaraan . ' berhasil diubah!');
    }

    // --- MANAJEMEN AREA (Opsional jika ingin reset manual lewat tombol) ---
    public function resetArea($id)
    {
        // Menggunakan primary key area_id sesuai database kamu
        $area = Area::where('area_id', $id)->firstOrFail();
        $area->update([
            'slot_tersedia' => $area->kapasitas
        ]);

        return redirect()->back()->with('success', 'Area ' . $area->nama_area . ' berhasil dikosongkan!');
    }

 public function toggleStatus($id) {
    $user = \App\Models\User::findOrFail($id);
    $user->is_active = !$user->is_active;
    $user->save(); // Baris ini tidak akan error lagi kalau langkah nomor 1 sudah sukses

    return back()->with('success', 'Status user berhasil diubah!');
}

public function toggleArea($id) {
    $area = \App\Models\Area::findOrFail($id);
    $area->is_active = !$area->is_active;
    $area->save();

    return back()->with('success', 'Status Area berhasil diubah!');
}
}
