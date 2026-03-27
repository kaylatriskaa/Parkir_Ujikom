<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// app/Models/LogAktivitas.php
class LogAktivitas extends Model
{
    protected $table = 'log_aktivitas';
    public $timestamps = false; // Ini WAJIB karena kita pakai kolom 'waktu' manual

    protected $fillable = ['user_id', 'aktivitas', 'waktu'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
