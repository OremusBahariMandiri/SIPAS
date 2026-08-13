<?php

namespace App\Models\Data;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\DataMaster\Tte;

class PengajuanTtePlacement extends Model
{
    protected $table = 'b04_pengajuan_tte_placement';

    protected $fillable = [
        'id_pengajuan',
        'id_tte',
        'tahap',
        'id_ref',
        'halaman',
        'pos_x',
        'pos_y',
        'lebar',
        'tinggi',
        'qr_token',
        'signed_at',
    ];

    protected $casts = [
        'signed_at' => 'datetime',
    ];

    public function pengajuan(): BelongsTo
    {
        return $this->belongsTo(PengajuanSurat::class, 'id_pengajuan');
    }

    public function tte(): BelongsTo
    {
        return $this->belongsTo(Tte::class, 'id_tte');
    }

    public function isSigned(): bool
    {
        return $this->signed_at !== null;
    }
}