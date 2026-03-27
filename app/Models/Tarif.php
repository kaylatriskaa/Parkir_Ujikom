<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tarif extends Model
{
    use HasFactory;

    // Biar nggak error kalau tabelnya nggak ada created_at/updated_at
    public $timestamps = false;

    // Kolom yang bisa diisi
    protected $fillable = ['jenis_kendaraan', 'harga_per_jam'];

    // KITA HAPUS RELASI transaksis() DI SINI
    // Karena database kamu nggak punya kolom tarif_id
}
