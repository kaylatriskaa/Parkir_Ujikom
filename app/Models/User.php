<?php
// FILE: User.php (Model)
// FUNGSI: Penghubung ke tabel tb_user di database.
//         Menyimpan data akun pengguna (admin, petugas, owner).
//         Extends Authenticatable karena dipakai untuk sistem login Laravel.

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'tb_user';
    protected $primaryKey = 'id_user';
    public $timestamps = false;

    protected $fillable = [
        'nama_lengkap',
        'username',
        'password',
        'role',
        'status_aktif',
    ];

    // Password disembunyikan dari output JSON (keamanan)
    protected $hidden = [
        'password',
    ];
}
