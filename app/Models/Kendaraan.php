<?php
// FILE: Kendaraan.php (Model)
// FUNGSI: Penghubung ke tabel tb_kendaraan di database.
//         Menyimpan data kendaraan yang pernah parkir (plat, jenis, warna, pemilik).

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kendaraan extends Model
{
    use HasFactory;

    protected $table = 'tb_kendaraan';
    protected $primaryKey = 'id_kendaraan';
    public $timestamps = false;

    protected $fillable = [
        'plat_nomor',
        'jenis_kendaraan',
        'warna',
        'pemilik',
        'id_user',
    ];

    // Relasi: kendaraan ini dicatat oleh 1 user (petugas)
    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    // Relasi: 1 kendaraan bisa punya banyak transaksi parkir
    public function transaksis()
    {
        return $this->hasMany(Transaksi::class, 'id_kendaraan', 'id_kendaraan');
    }
}
