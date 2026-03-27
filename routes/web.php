<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Petugas\PetugasController;
use App\Http\Controllers\Owner\OwnerController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// 1. Pengalihan Halaman Utama
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

// 2. Profile User (Middleware Auth Umum)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// 3. Grup Route PETUGAS
Route::middleware(['auth'])->prefix('petugas')->name('petugas.')->group(function () {
    Route::get('/dashboard', [PetugasController::class, 'index'])->name('dashboard');
    Route::post('/kendaraan-masuk', [PetugasController::class, 'kendaraanMasuk'])->name('masuk');
    Route::post('/kendaraan-keluar', [PetugasController::class, 'kendaraanKeluar'])->name('keluar');
    Route::get('/cetak/{id}', [PetugasController::class, 'cetakKarcis'])->name('cetak');
});

// 4. Grup Route ADMIN
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard Utama
    Route::get('/dashboard', [AdminController::class, 'index'])->name('dashboard');

    // User Management
    Route::prefix('users')->name('users.')->group(function() {
        Route::post('/', [AdminController::class, 'store'])->name('store');
        Route::put('/{id}', [AdminController::class, 'update'])->name('update');
        Route::delete('/{id}', [AdminController::class, 'destroy'])->name('destroy');

        // PERBAIKAN DI SINI: Tidak perlu tulis /admin/users lagi karena sudah di dalam grup
        Route::patch('/{id}/toggle', [AdminController::class, 'toggleStatus'])->name('toggle');
    });

    // Tarif Management
    Route::prefix('tarifs')->name('tarifs.')->group(function() {
        Route::put('/{id}', [AdminController::class, 'updateTarif'])->name('update');
    });

    // Area Management
    Route::prefix('areas')->name('areas.')->group(function() {
        Route::put('/{id}', [AdminController::class, 'updateArea'])->name('update');
        // Tambahkan ini kalau mau ada fitur aktif/nonaktif area juga nanti
        Route::patch('/{id}/toggle', [AdminController::class, 'toggleArea'])->name('toggle');
    });
});

// 5. Grup Route OWNER
Route::middleware(['auth'])->prefix('owner')->name('owner.')->group(function () {
    Route::get('/dashboard', [OwnerController::class, 'index'])->name('dashboard');
});

require __DIR__.'/auth.php';
