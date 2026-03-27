<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    use HasFactory;

    protected $table = 'transaksis';

    // Kita masukkan semua kolom yang benar-benar ada di database kamu
   protected $fillable = [
    'plat_nomor',
    'jenis_kendaraan',
    'harga_per_jam',
    'area_id',
    'user_id',
    'jam_masuk',
    'jam_keluar',  // Harus ada ini
    'total_bayar', // Harus ada ini
    'status'       // Harus ada ini
];

    /**
     * Relasi ke Area tetap dipertahankan karena area_id biasanya sudah ada
     */
    public function area()
    {
        return $this->belongsTo(Area::class, 'area_id', 'area_id');
    }

    // FUNGSI RELASI TARIF DIHAPUS
    // Karena kita sudah pakai kolom jenis_kendaraan & harga_per_jam langsung di tabel ini
}
