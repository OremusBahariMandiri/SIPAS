<?php

namespace App\Models\DataMaster;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Data\PengajuanSurat;

class SifatSurat extends Model
{
    protected $table = 'a07_sifat_surat';

    protected $fillable = [
        'kode',
        'nama',
        'keterangan',
        'status',
    ];

    protected $casts = [
        'status' => 'integer',
    ];

    public function pengajuans(): HasMany
    {
        return $this->hasMany(PengajuanSurat::class, 'id_sifat_surat');
    }

    public function scopeAktif($query)
    {
        return $query->where('status', 1);
    }
}