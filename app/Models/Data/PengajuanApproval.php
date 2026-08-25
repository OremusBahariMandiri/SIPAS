<?php

namespace App\Models\Data;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class PengajuanApproval extends Model
{
    protected $table = 'b03_pengajuan_approval';

    protected $fillable = [
        'id_pengajuan',
        'tahap',
        'id_ref',
        'id_approver',
        'aksi',
        'catatan',
        'acted_at',
        'file_snapshot',
    ];

    protected $casts = [
        'acted_at' => 'datetime',
    ];

    public function pengajuan(): BelongsTo
    {
        return $this->belongsTo(PengajuanSurat::class, 'id_pengajuan');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_approver');
    }
}