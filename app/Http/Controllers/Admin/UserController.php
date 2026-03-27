<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    // Tampilan daftar user
    public function index()
    {
        $users = User::all();
        return view('dashboards.admin', compact('users'));
    }

    // Simpan user baru ke database
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|min:8',
            'role' => 'required'
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role, // Menggunakan kolom role yang kamu buat di migration
        ]);

        return redirect()->back()->with('success', 'User berhasil ditambahkan!');
    }

   public function toggleStatus($id) {
    $user = \App\Models\User::findOrFail($id);
    $user->is_active = !$user->is_active;
    $user->save(); // Baris ini tidak akan error lagi kalau langkah nomor 1 sudah sukses

    return back()->with('success', 'Status user berhasil diubah!');
}
}
