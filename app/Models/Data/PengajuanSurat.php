<?php

namespace App\Models\Data;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;
use App\Models\DataMaster\Perusahaan;
use App\Models\DataMaster\JenisDokumen;

class PengajuanSurat extends Model
{
    protected $table = 'b01_pengajuan_surat';

    protected $fillable = [
        'tanggal_surat',
        'id_perusahaan',
        'id_kepada',
        'nomor_surat',
        'id_jenis_dokumen',
        'id_sifat_surat',
        'perihal',
        'file_original',
        'file_current',
        'file_signed',
        'require_tte_pengaju',
        'require_tte_kepada',
        'status',
        'id_user',
    ];

    protected $casts = [
        'tanggal_surat' => 'datetime',
    ];

    // ------------------------------------------------
    // Relasi
    // ------------------------------------------------

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function sifatSurat(): BelongsTo
    {
        return $this->belongsTo(\App\Models\DataMaster\SifatSurat::class, 'id_sifat_surat');
    }

    public function perusahaan(): BelongsTo
    {
        return $this->belongsTo(Perusahaan::class, 'id_perusahaan');
    }

    public function kepada(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_kepada');
    }

    public function jenisDokumen(): BelongsTo
    {
        return $this->belongsTo(JenisDokumen::class, 'id_jenis_dokumen');
    }

    public function terusans(): HasMany
    {
        return $this->hasMany(PengajuanTerusan::class, 'id_pengajuan')->orderBy('urutan');
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(PengajuanApproval::class, 'id_pengajuan')->orderBy('acted_at');
    }

    public function ttePlacements(): HasMany
    {
        return $this->hasMany(PengajuanTtePlacement::class, 'id_pengajuan');
    }

    // ------------------------------------------------
    // Helpers
    // ------------------------------------------------

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }
    public function isWaiting(): bool
    {
        return $this->status === 'waiting';
    }
    public function isInReview(): bool
    {
        return $this->status === 'in_review';
    }
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function isEditable(): bool
    {
        return in_array($this->status, ['draft', 'rejected']);
    }

    /**
     * Terusan yang sedang aktif (urutan berikutnya yang belum diproses)
     */
    public function activeTerusan(): ?PengajuanTerusan
    {
        return $this->terusans()->where('status', 'waiting')->orderBy('urutan')->first();
    }

    // ------------------------------------------------
    // Scopes
    // ------------------------------------------------

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByUser($query, int $userId)
    {
        return $query->where('id_user', $userId);
    }
}
