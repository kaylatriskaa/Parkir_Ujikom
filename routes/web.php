<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Petugas\PetugasController;
use App\Http\Controllers\Owner\OwnerController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// Pengalihan Halaman Utama
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

// Profile User
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Grup Route PETUGAS
Route::middleware(['auth', 'role:petugas'])->prefix('petugas')->name('petugas.')->group(function () {
    Route::get('/dashboard', [PetugasController::class, 'index'])->name('dashboard');
    Route::post('/kendaraan-masuk', [PetugasController::class, 'kendaraanMasuk'])->name('masuk');
    Route::post('/kendaraan-keluar', [PetugasController::class, 'kendaraanKeluar'])->name('keluar');
    Route::get('/cetak/{id}', [PetugasController::class, 'cetakKarcis'])->name('cetak');
});

// Grup Route ADMIN
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'index'])->name('dashboard');

    Route::prefix('users')->name('users.')->group(function () {
        Route::post('/', [AdminController::class, 'store'])->name('store');
        Route::put('/{id}', [AdminController::class, 'update'])->name('update');
        Route::delete('/{id}', [AdminController::class, 'destroy'])->name('destroy');
        Route::patch('/{id}/toggle', [AdminController::class, 'toggleStatus'])->name('toggle');
    });

    Route::prefix('tarifs')->name('tarifs.')->group(function () {
        Route::put('/{id}', [AdminController::class, 'updateTarif'])->name('update');
    });

    Route::prefix('areas')->name('areas.')->group(function () {
        Route::put('/{id}', [AdminController::class, 'updateArea'])->name('update');
    });
});

// Grup Route OWNER
Route::middleware(['auth', 'role:owner'])->prefix('owner')->name('owner.')->group(function () {
    Route::get('/dashboard', [OwnerController::class, 'index'])->name('dashboard');
});

require __DIR__.'/auth.php';
