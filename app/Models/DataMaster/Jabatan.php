<?php

namespace App\Models\DataMaster;

use Illuminate\Database\Eloquent\Model;

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

    /**
     * Scope: hanya yang aktif
     */
    public function scopeAktif($query)
    {
        return $query->where('status', 1);
    }
}