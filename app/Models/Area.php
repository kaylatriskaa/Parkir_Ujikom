<?php
// FILE: Area.php (Model)
// FUNGSI: Penghubung ke tabel tb_area_parkir di database.
//         Menyimpan data area/zona parkir beserta kapasitas dan jumlah terisi.

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    use HasFactory;

    protected $table = 'tb_area_parkir';
    protected $primaryKey = 'id_area';
    public $timestamps = false;

    protected $fillable = [
        'nama_area',
        'kapasitas',
        'terisi',
    ];

    // Relasi: 1 area bisa punya banyak transaksi
    public function transaksis()
    {
        return $this->hasMany(Transaksi::class, 'id_area', 'id_area');
    }
}
