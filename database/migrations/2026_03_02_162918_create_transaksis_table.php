<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaksis', function (Blueprint $table) {
            $table->id();
            $table->string('plat_nomor');
            $table->string('jenis_kendaraan');
            $table->integer('harga_per_jam');

            // Ini untuk menghubungkan ke tabel areas (Lantai 1, Lantai 2, dll)
            $table->foreignId('area_id')->constrained('area_parkirs')->onDelete('cascade');

            $table->timestamp('jam_masuk');
            $table->timestamp('jam_keluar')->nullable(); // Kosong saat baru masuk
            $table->integer('total_bayar')->nullable();  // Kosong saat baru masuk
            $table->enum('status', ['parkir', 'selesai'])->default('parkir');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaksis');
    }
};
