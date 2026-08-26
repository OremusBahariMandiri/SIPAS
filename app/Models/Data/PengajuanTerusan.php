<?php

namespace App\Models\Data;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class PengajuanTerusan extends Model
{
    protected $table = 'b02_pengajuan_terusan';

    protected $fillable = [
        'id_pengajuan',
        'id_user',          // ← ganti dari id_departemen
        'urutan',
        'require_tte',
        'require_tte_count',
        'status',
        'approved_by',
        'approved_at',
        'catatan',
    ];

    protected $casts = [
        'require_tte' => 'boolean',
        'approved_at' => 'datetime',
    ];

    public function pengajuan(): BelongsTo
    {
        return $this->belongsTo(PengajuanSurat::class, 'id_pengajuan');
    }

    // Relasi ke user tujuan terusan
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    // Relasi ke user yang menyetujui
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function approvals(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PengajuanApproval::class, 'id_ref')
            ->where('tahap', 'terusan');
    }

    public function isWaiting(): bool
    {
        return $this->status === 'waiting';
    }
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }
}
