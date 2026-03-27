<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('area_parkirs', function (Blueprint $table) {
            // Tambahkan baris ini untuk membuat kolom sisa slot
            $table->integer('slot_tersedia')->default(0)->after('kapasitas');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('area_parkirs', function (Blueprint $table) {
            // Tambahkan baris ini untuk menghapus kolom jika migration dibatalkan
            $table->dropColumn('slot_tersedia');
        });
    }
};
