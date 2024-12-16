<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens,HasFactory, Notifiable;

    // Kolom yang bisa diisi
    protected $fillable = [
        'nama', 'role', 'jurusan', 'kelas', 'password',
    ];

    // Menyembunyikan kolom tertentu saat serialisasi (misalnya untuk API responses)
    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Kolom yang perlu di-cast (misalnya untuk type casting)
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',  // Memastikan password disimpan dalam bentuk hash
    ];

    // Mendefinisikan enum untuk 'role'
    const ROLE_ADMIN = 'admin';
    const ROLE_GURU = 'guru';
    const ROLE_SISWA = 'siswa';

    protected $enum = [
        'role' => ['admin', 'guru', 'siswa'],
    ];

    /**
     * Cek apakah user adalah admin.
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    /**
     * Cek apakah user adalah guru.
     *
     * @return bool
     */
    public function isGuru(): bool
    {
        return $this->role === self::ROLE_GURU;
    }

    /**
     * Cek apakah user adalah siswa.
     *
     * @return bool
     */
    public function isSiswa(): bool
    {
        return $this->role === self::ROLE_SISWA;
    }
}
