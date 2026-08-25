<?php

namespace App\Models\DataMaster;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Jabatan extends Model
{
    protected $table = 'a03_jabatan';

    protected $fillable = [
        'kode',
        'nama',
        'singkatan',
        'status',
    ];

    protected $casts = [
        'status' => 'integer',
    ];

    public function scopeAktif($query)
    {
        return $query->where('status', 1);
    }

    public function users()
    {
        return $this->hasMany(User::class, 'jabatan', 'nama');
    }
}