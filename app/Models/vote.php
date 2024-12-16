<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class vote extends Model
{
    use HasApiTokens,HasFactory, Notifiable;

    protected $fillable = [
        'user_id', 'candidate_id', 'role',
    ];

    // Relasi dengan User (pemilih)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relasi dengan Candidate (kandidat yang dipilih)
    public function candidate()
    {
        return $this->belongsTo(Candidate::class);
    }
}
