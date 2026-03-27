<?php
// FILE: Transaksi.php (Model)
// FUNGSI: Penghubung ke tabel tb_transaksi di database.
//         Menyimpan data setiap transaksi parkir (masuk/keluar).
//         Terhubung ke 4 tabel lain: kendaraan, tarif, area, dan user.

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    use HasFactory;

    protected $table = 'tb_transaksi';      // Nama tabel di database
    protected $primaryKey = 'id_parkir';     // Primary key (bukan 'id' default Laravel)
    public $timestamps = false;              // Tabel ini tidak pakai created_at/updated_at

    // Kolom yang boleh diisi dari form (mass assignment protection)
    protected $fillable = [
        'id_kendaraan',
        'id_tarif',
        'id_area',
        'id_user',
        'waktu_masuk',
        'waktu_keluar',
        'durasi_jam',
        'biaya_total',
        'status',
    ];

    // --- RELASI ---
    // Setiap transaksi DIMILIKI oleh 1 kendaraan
    // Cara pakai: $transaksi->kendaraan->plat_nomor
    public function kendaraan()
    {
        return $this->belongsTo(Kendaraan::class, 'id_kendaraan', 'id_kendaraan');
    }

    // Setiap transaksi punya 1 tarif (Motor/Mobil)
    public function tarif()
    {
        return $this->belongsTo(Tarif::class, 'id_tarif', 'id_tarif');
    }

    // Setiap transaksi terjadi di 1 area parkir
    public function area()
    {
        return $this->belongsTo(Area::class, 'id_area', 'id_area');
    }

    // Setiap transaksi dicatat oleh 1 user (petugas)
    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }
}
