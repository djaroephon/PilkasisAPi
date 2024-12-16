<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Candidate extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama',
        'role',
        'jurusan',
        'kelas',
        'foto',
        'visi_misi',
    ];

    public function votes()
    {
        return $this->hasMany(Vote::class, 'candidate_id');
    }
}

