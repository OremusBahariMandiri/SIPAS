<?php

namespace App\Models\DataMaster;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Perusahaan extends Model
{
    protected $table = 'a01_perusahaan';

    protected $fillable = [
        'nama',
        'singkatan',
        'logo',
        'status',
    ];

    protected $casts = [
        'status' => 'integer',
    ];

    /**
     * Relasi ke users
     */
    public function users(): HasMany
    {
        return $this->hasMany(\App\Models\User::class, 'id_perusahaan');
    }

    /**
     * Scope: hanya yang aktif
     */
    public function scopeAktif($query)
    {
        return $query->where('status', 1);
    }
}