<?php

namespace App\Models\DataMaster;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class WilayahKerja extends Model
{
    protected $table = 'a04_wilayah_kerja';

    protected $fillable = [
        'kode',
        'wilayah_kerja',
        'skt_wilayah_kerja',
        'area_kerja',
        'skt_area_kerja',
    ];

    public function scopeByWilayah($query, string $wilayah)
    {
        return $query->where('wilayah_kerja', $wilayah);
    }

    public function users()
    {
        return $this->hasMany(User::class, 'wilker', 'kode');
    }
}