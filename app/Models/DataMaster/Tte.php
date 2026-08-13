<?php

namespace App\Models\DataMaster;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class Tte extends Model
{
    use SoftDeletes;

    protected $table = 'a05_tte';

    protected $fillable = [
        'id_user',
        'id_perusahaan',
        'private_key',
        'public_key',
        'verify_token',
        'is_active',
        'expired_at',
        'created_by',
        'updated_by',
    ];

    protected $hidden = [
        'private_key',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'expired_at' => 'date',
    ];

    // ── Relasi ───────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function perusahaan(): BelongsTo
    {
        return $this->belongsTo(Perusahaan::class, 'id_perusahaan');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // ── Helpers ──────────────────────────────────────────────

    public function isValid(): bool
    {
        if (!$this->is_active) return false;
        if ($this->expired_at !== null && $this->expired_at->isPast()) return false;
        return true;
    }

    public function isExpired(): bool
    {
        return $this->expired_at !== null && $this->expired_at->isPast();
    }

    // ── Scopes ───────────────────────────────────────────────

    public function scopeValid($query)
    {
        return $query->where('is_active', true)
                     ->where(function ($q) {
                         $q->whereNull('expired_at')
                           ->orWhere('expired_at', '>=', now()->toDateString());
                     });
    }

    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    public function scopeExpired($query)
    {
        return $query->whereNotNull('expired_at')
                     ->where('expired_at', '<', now()->toDateString());
    }
}