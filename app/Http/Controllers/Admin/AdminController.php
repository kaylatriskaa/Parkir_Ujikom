<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Tarif;
use App\Models\Area;
use App\Models\Transaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    public function index()
    {
        $users = User::whereIn('role', ['petugas', 'owner'])
                    ->where('id_user', '!=', Auth::id())
                    ->get();

        $tarifs = Tarif::all();
        $areas = Area::all();

        try {
            $logs = Transaksi::with(['kendaraan', 'area', 'user'])->latest('waktu_masuk')->take(10)->get();
        } catch (\Exception $e) {
            $logs = collect();
        }

        $totalLogsCount = Transaksi::whereDate('waktu_masuk', today())->count();

        return view('dashboards.admin', compact('users', 'tarifs', 'areas', 'logs', 'totalLogsCount'));
    }

    // --- MANAJEMEN USER ---

    public function store(Request $request)
    {
        $request->validate([
            'nama_lengkap' => 'required|string|max:100',
            'username' => 'required|string|max:50|unique:tb_user,username',
            'password' => 'required|string|min:6',
            'role' => 'required|in:petugas,owner',
        ]);

        User::create([
            'nama_lengkap' => $request->nama_lengkap,
            'username' => $request->username,
            'password' => md5($request->password),
            'role' => $request->role,
        ]);

        return redirect()->back()->with('success', 'Akun ' . $request->nama_lengkap . ' berhasil dibuat!');
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'nama_lengkap' => 'required|string|max:100',
            'username' => 'required|string|max:50|unique:tb_user,username,' . $id . ',id_user',
            'role' => 'required|in:petugas,owner',
        ]);

        $user->nama_lengkap = $request->nama_lengkap;
        $user->username = $request->username;
        $user->role = $request->role;

        if ($request->filled('password')) {
            $request->validate(['password' => 'min:6']);
            $user->password = md5($request->password);
        }

        $user->save();

        return redirect()->back()->with('success', 'Data user berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);

        if ($user->id_user === Auth::id()) {
            return redirect()->back()->with('error', 'Dilarang menghapus akun sendiri!');
        }

        $user->delete();
        return redirect()->back()->with('success', 'User berhasil dihapus!');
    }

    public function toggleStatus($id)
    {
        $user = User::findOrFail($id);
        $user->status_aktif = !$user->status_aktif;
        $user->save();

        return back()->with('success', 'Status user berhasil diubah!');
    }

    // --- MANAJEMEN TARIF ---

    public function updateTarif(Request $request, $id)
    {
        $request->validate([
            'tarif_per_jam' => 'required|numeric|min:0',
        ]);

        $tarif = Tarif::findOrFail($id);
        $tarif->update([
            'tarif_per_jam' => $request->tarif_per_jam,
        ]);

        return redirect()->back()->with('success', 'Tarif ' . $tarif->jenis_kendaraan . ' berhasil diubah!');
    }

    // --- MANAJEMEN AREA ---

    public function updateArea(Request $request, $id)
    {
        $area = Area::findOrFail($id);

        $request->validate([
            'nama_area' => 'required|string|max:100',
            'kapasitas' => 'required|integer|min:1',
        ]);

        $area->update([
            'nama_area' => $request->nama_area,
            'kapasitas' => $request->kapasitas,
        ]);

        return redirect()->back()->with('success', 'Area ' . $area->nama_area . ' berhasil diperbarui!');
    }
}
