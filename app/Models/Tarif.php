<?php
// FILE: Tarif.php (Model)
// FUNGSI: Penghubung ke tabel tb_tarif di database.
//         Menyimpan data tarif parkir per jenis kendaraan (Motor, Mobil, dll).

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tarif extends Model
{
    use HasFactory;

    protected $table = 'tb_tarif';
    protected $primaryKey = 'id_tarif';
    public $timestamps = false;

    protected $fillable = ['jenis_kendaraan', 'tarif_per_jam'];

    // Relasi: 1 tarif bisa dipakai banyak transaksi
    public function transaksis()
    {
        return $this->hasMany(Transaksi::class, 'id_tarif', 'id_tarif');
    }
}
