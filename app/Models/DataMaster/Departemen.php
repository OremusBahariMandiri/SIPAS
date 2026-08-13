<?php

namespace App\Models\DataMaster;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Departemen extends Model
{
    protected $table = 'a02_departemen';

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
     * Relasi ke users
     */
    public function users(): HasMany
    {
        return $this->hasMany(\App\Models\User::class, 'id_departemen');
    }

    /**
     * Scope: hanya yang aktif
     */
    public function scopeAktif($query)
    {
        return $query->where('status', 1);
    }
}