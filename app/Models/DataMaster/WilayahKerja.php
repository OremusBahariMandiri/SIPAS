<?php

namespace App\Models\DataMaster;

use Illuminate\Database\Eloquent\Model;

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

    /**
     * Scope: filter berdasarkan wilayah kerja
     */
    public function scopeByWilayah($query, string $wilayah)
    {
        return $query->where('wilayah_kerja', $wilayah);
    }
}