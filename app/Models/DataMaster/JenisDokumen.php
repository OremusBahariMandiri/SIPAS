<?php

namespace App\Models\DataMaster;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JenisDokumen extends Model
{
    protected $table = 'a06_jenis_dokumen';

    protected $fillable = [
        'kode_dokumen',
        'kategori_dokumen',
        'jenis_dokumen',
        'departemen_pemilik',
    ];

    // ------------------------------------------------
    // Relasi
    // ------------------------------------------------

    public function departemen(): BelongsTo
    {
        return $this->belongsTo(Departemen::class, 'departemen_pemilik');
    }

    // ------------------------------------------------
    // Scopes
    // ------------------------------------------------

    public function scopeByKategori($query, string $kategori)
    {
        return $query->where('kategori_dokumen', $kategori);
    }

    public function scopeByDepartemen($query, int $idDepartemen)
    {
        return $query->where('departemen_pemilik', $idDepartemen);
    }
}