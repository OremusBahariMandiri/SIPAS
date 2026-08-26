<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class ActivityLog extends Model
{
    protected $table = 'activity_logs';

    protected $fillable = [
        'user_id',
        'user_nrk',
        'user_name',
        'module',
        'action',
        'subject_type',
        'subject_id',
        'subject_label',
        'before',
        'after',
        'ip_address',
        'user_agent',
        'notes',
    ];

    protected $casts = [
        'before' => 'array',
        'after'  => 'array',
    ];

    // ──────────────────────────────────────────────────────────
    // Relasi
    // ──────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // ──────────────────────────────────────────────────────────
    // Scopes
    // ──────────────────────────────────────────────────────────

    public function scopeByModule(Builder $query, string $module): Builder
    {
        return $query->where('module', $module);
    }

    public function scopeByAction(Builder $query, string $action): Builder
    {
        return $query->where('action', $action);
    }

    public function scopeByUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeDateFrom(Builder $query, string $date): Builder
    {
        return $query->whereDate('created_at', '>=', $date);
    }

    public function scopeDateTo(Builder $query, string $date): Builder
    {
        return $query->whereDate('created_at', '<=', $date);
    }

    // ──────────────────────────────────────────────────────────
    // Helpers — label & badge
    // ──────────────────────────────────────────────────────────

    /**
     * Label modul yang ramah baca
     */
    public function getModuleLabelAttribute(): string
    {
        return match ($this->module) {
            'auth'       => 'Authentication',
            'users'      => 'User Management',
            'submission' => 'Submission',
            'approval'   => 'Approval',
            'tte'        => 'TTE / E-Signature',
            default      => ucfirst($this->module),
        };
    }

    /**
     * Label aksi yang ramah baca
     */
    public function getActionLabelAttribute(): string
    {
        return match ($this->action) {
            'login'      => 'Login',
            'logout'     => 'Logout',
            'create'     => 'Create',
            'update'     => 'Update',
            'delete'     => 'Delete',
            'approve'    => 'Approve',
            'reject'     => 'Reject',
            'resubmit'   => 'Resubmit',
            'tte_placed' => 'TTE Placed',
            'tte_signed' => 'TTE Signed',
            default      => ucfirst(str_replace('_', ' ', $this->action)),
        };
    }

    /**
     * CSS class untuk badge warna berdasarkan aksi
     */
    public function getActionBadgeClassAttribute(): string
    {
        return match ($this->action) {
            'login'      => 'badge-success',
            'logout'     => 'badge-muted',
            'create'     => 'badge-primary',
            'update'     => 'badge-warning',
            'delete'     => 'badge-danger',
            'approve'    => 'badge-success',
            'reject'     => 'badge-danger',
            'resubmit'   => 'badge-warning',
            'tte_placed' => 'badge-info',
            'tte_signed' => 'badge-primary',
            default      => 'badge-muted',
        };
    }

    /**
     * CSS class badge modul
     */
    public function getModuleBadgeClassAttribute(): string
    {
        return match ($this->module) {
            'auth'       => 'badge-muted',
            'users'      => 'badge-primary',
            'submission' => 'badge-info',
            'approval'   => 'badge-warning',
            'tte'        => 'badge-success',
            default      => 'badge-muted',
        };
    }

    // ──────────────────────────────────────────────────────────
    // Konstanta untuk referensi
    // ──────────────────────────────────────────────────────────

    public const MODULES = [
        'auth'       => 'Authentication',
        'users'      => 'User Management',
        'submission' => 'Submission',
        'approval'   => 'Approval',
        'tte'        => 'TTE / E-Signature',
    ];

    public const ACTIONS = [
        'login'      => 'Login',
        'logout'     => 'Logout',
        'create'     => 'Create',
        'update'     => 'Update',
        'delete'     => 'Delete',
        'approve'    => 'Approve',
        'reject'     => 'Reject',
        'resubmit'   => 'Resubmit',
        'tte_placed' => 'TTE Placed',
        'tte_signed' => 'TTE Signed',
    ];
}