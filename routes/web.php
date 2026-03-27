<?php
// FILE: web.php
// FUNGSI: Peta URL aplikasi. Menentukan URL mana ditangani oleh Controller mana.
//         Semua route dilindungi middleware: 'auth' (harus login) dan 'role' (harus role tertentu).

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Petugas\PetugasController;
use App\Http\Controllers\Owner\OwnerController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// --- HALAMAN UTAMA ---
// Kalau sudah login, redirect ke dashboard sesuai role
// Kalau belum login, redirect ke halaman login
Route::get('/', function () {
    if (Auth::check()) {
        $role = Auth::user()->role;
        return match($role) {
            'admin'   => redirect()->route('admin.dashboard'),
            'petugas' => redirect()->route('petugas.dashboard'),
            'owner'   => redirect()->route('owner.dashboard'),
            default   => redirect()->route('login'),
        };
    }
    return redirect()->route('login');
});

// --- PROFILE USER ---
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// --- ROUTE PETUGAS ---
// Semua URL diawali /petugas/... dan hanya bisa diakses oleh role 'petugas'
Route::middleware(['auth', 'role:petugas'])->prefix('petugas')->name('petugas.')->group(function () {
    Route::get('/dashboard', [PetugasController::class, 'index'])->name('dashboard');
    Route::post('/kendaraan-masuk', [PetugasController::class, 'kendaraanMasuk'])->name('masuk');
    Route::post('/kendaraan-keluar', [PetugasController::class, 'kendaraanKeluar'])->name('keluar');
    Route::get('/cetak/{id}', [PetugasController::class, 'cetakKarcis'])->name('cetak');
    Route::get('/cetak-keluar/{id}', [PetugasController::class, 'cetakStrukKeluar'])->name('cetak.keluar');
});

// --- ROUTE ADMIN ---
// Semua URL diawali /admin/... dan hanya bisa diakses oleh role 'admin'
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'index'])->name('dashboard');

    // Manajemen User: tambah, edit, hapus, toggle aktif
    Route::prefix('users')->name('users.')->group(function () {
        Route::post('/', [AdminController::class, 'store'])->name('store');
        Route::put('/{id}', [AdminController::class, 'update'])->name('update');
        Route::delete('/{id}', [AdminController::class, 'destroy'])->name('destroy');
        Route::patch('/{id}/toggle', [AdminController::class, 'toggleStatus'])->name('toggle');
    });

    // Manajemen Tarif: tambah, edit harga
    Route::prefix('tarifs')->name('tarifs.')->group(function () {
        Route::post('/', [AdminController::class, 'storeTarif'])->name('store');
        Route::put('/{id}', [AdminController::class, 'updateTarif'])->name('update');
    });

    // Manajemen Area: tambah, edit, toggle aktif/nonaktif
    Route::prefix('areas')->name('areas.')->group(function () {
        Route::post('/', [AdminController::class, 'storeArea'])->name('store');
        Route::put('/{id}', [AdminController::class, 'updateArea'])->name('update');
        Route::patch('/{id}/toggle', [AdminController::class, 'toggleArea'])->name('toggle');
    });
});

// --- ROUTE OWNER ---
// Semua URL diawali /owner/... dan hanya bisa diakses oleh role 'owner'
Route::middleware(['auth', 'role:owner'])->prefix('owner')->name('owner.')->group(function () {
    Route::get('/dashboard', [OwnerController::class, 'index'])->name('dashboard');
    Route::get('/export-pdf', [OwnerController::class, 'exportPdf'])->name('export.pdf');
});

require __DIR__.'/auth.php';
