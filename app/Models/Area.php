<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    use HasFactory;

    // 1. Tambahkan ini untuk mematikan created_at & updated_at otomatis
    public $timestamps = false;

    // Kasih tahu Laravel nama tabelnya (karena bukan 'areas')
    protected $table = 'area_parkirs';

    protected $primaryKey = 'area_id';

    // Kolom yang boleh diisi
    protected $fillable = [
        'nama_area',
        'slot_total',
        'slot_tersedia',
        'is_active',
    ];

    // Relasi ke Transaksi (Satu area punya banyak transaksi)
    public function transaksis()
    {
        return $this->hasMany(Transaksi::class, 'area_id');
    }
}
