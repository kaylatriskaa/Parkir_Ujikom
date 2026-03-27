<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tarif; // Pastikan memanggil Model Tarif

class TarifSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ini data contoh, kamu bisa ganti harganya sesuka hati
        Tarif::create([
            'jenis_kendaraan' => 'Mobil',
            'harga_per_jam' => 5000,
        ]);

        Tarif::create([
            'jenis_kendaraan' => 'Motor',
            'harga_per_jam' => 2000,
        ]);

        Tarif::create([
            'jenis_kendaraan' => 'Lainnya',
            'harga_per_jam' => 10000,
        ]);
    }
}
