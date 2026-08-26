<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request as RequestFacade;

class ActivityLogService
{
    // ──────────────────────────────────────────────────────────────────────────
    // Core recorder
    // ──────────────────────────────────────────────────────────────────────────

    public static function record(
        string $module,
        string $action,
        array  $options = [],
        ?User  $actor   = null,
    ): ActivityLog {
        $actor ??= auth()->user();

        return ActivityLog::create([
            'user_id'       => $actor?->id,
            'user_nrk'      => $actor?->nrk,
            'user_name'     => $actor?->nama_karyawan,
            'module'        => $module,
            'action'        => $action,
            'subject_type'  => isset($options['subject'])
                ? get_class($options['subject'])
                : ($options['subject_type'] ?? null),
            'subject_id'    => isset($options['subject'])
                ? $options['subject']->getKey()
                : ($options['subject_id'] ?? null),
            'subject_label' => $options['subject_label'] ?? null,
            'before'        => isset($options['before']) ? self::sanitize($options['before']) : null,
            'after'         => isset($options['after'])  ? self::sanitize($options['after'])  : null,
            'ip_address'    => RequestFacade::ip(),
            'user_agent'    => RequestFacade::userAgent(),
            'notes'         => $options['notes'] ?? null,
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Auth
    // ──────────────────────────────────────────────────────────────────────────

    public static function login(User $user): void
    {
        self::record('auth', 'login', [
            'subject'       => $user,
            'subject_label' => "{$user->nrk} – {$user->nama_karyawan}",
            'notes'         => 'Login via NRK.',
        ], $user);
    }

    public static function logout(User $user): void
    {
        self::record('auth', 'logout', [
            'subject'       => $user,
            'subject_label' => "{$user->nrk} – {$user->nama_karyawan}",
        ], $user);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Generic Master CRUD  ← dipakai oleh semua DataMaster controller
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Log CREATE untuk semua entitas master.
     *
     * @param string $module  Nama modul, misal 'master.perusahaan'
     * @param Model  $model   Eloquent model yang baru dibuat
     * @param string $label   Label singkat, misal 'PT Contoh (CONT)'
     * @param array  $fields  Field yang ingin di-snapshot (kosong = semua)
     */
    public static function masterCreated(
        string $module,
        Model  $model,
        string $label,
        array  $fields = [],
    ): void {
        $after = $fields
            ? array_intersect_key($model->toArray(), array_flip($fields))
            : self::sanitize($model->toArray());

        self::record($module, 'create', [
            'subject'       => $model,
            'subject_label' => $label,
            'after'         => $after,
            'notes'         => "New {$module} record created.",
        ]);
    }

    /**
     * Log UPDATE untuk semua entitas master.
     *
     * @param string $module    Nama modul
     * @param Model  $model     Eloquent model SETELAH diupdate
     * @param array  $original  Data SEBELUM update (dari $model->toArray() sebelum update)
     * @param string $label     Label singkat
     * @param array  $fields    Field yang ingin di-diff (kosong = semua)
     */
    public static function masterUpdated(
        string $module,
        Model  $model,
        array  $original,
        string $label,
        array  $fields = [],
    ): void {
        $after  = $fields
            ? array_intersect_key($model->toArray(), array_flip($fields))
            : self::sanitize($model->toArray());

        $before = $fields
            ? array_intersect_key($original, array_flip($fields))
            : self::sanitize($original);

        $changed = self::diffKeys($before, $after);

        if (empty($changed)) return; // tidak ada yang berubah, skip

        self::record($module, 'update', [
            'subject'       => $model,
            'subject_label' => $label,
            'before'        => array_intersect_key($before, array_flip($changed)),
            'after'         => array_intersect_key($after,  array_flip($changed)),
            'notes'         => 'Fields changed: ' . implode(', ', $changed),
        ]);
    }

    /**
     * Log DELETE untuk semua entitas master.
     */
    public static function masterDeleted(
        string $module,
        Model  $model,
        string $label,
        array  $fields = [],
    ): void {
        $before = $fields
            ? array_intersect_key($model->toArray(), array_flip($fields))
            : self::sanitize($model->toArray());

        self::record($module, 'delete', [
            'subject'       => $model,
            'subject_label' => $label,
            'before'        => $before,
            'notes'         => "Record permanently deleted.",
        ]);
    }

    /**
     * Log aksi custom TTE (regenerate, toggle).
     */
    public static function masterAction(
        string $module,
        string $action,
        Model  $model,
        string $label,
        string $notes = '',
        array  $after = [],
    ): void {
        self::record($module, $action, [
            'subject'       => $model,
            'subject_label' => $label,
            'after'         => $after ?: null,
            'notes'         => $notes,
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Users CRUD
    // ──────────────────────────────────────────────────────────────────────────

    public static function userCreated(User $target): void
    {
        self::record('users', 'create', [
            'subject'       => $target,
            'subject_label' => "{$target->nrk} – {$target->nama_karyawan}",
            'after'         => self::userSnapshot($target),
            'notes'         => 'New user created.',
        ]);
    }

    public static function userUpdated(User $target, array $original): void
    {
        $after   = self::userSnapshot($target);
        $before  = self::userSnapshot($target, $original);
        $changed = self::diffKeys($before, $after);

        if (empty($changed)) return;

        self::record('users', 'update', [
            'subject'       => $target,
            'subject_label' => "{$target->nrk} – {$target->nama_karyawan}",
            'before'        => array_intersect_key($before, array_flip($changed)),
            'after'         => array_intersect_key($after,  array_flip($changed)),
            'notes'         => 'Fields changed: ' . implode(', ', $changed),
        ]);
    }

    public static function userDeleted(User $target): void
    {
        self::record('users', 'delete', [
            'subject'       => $target,
            'subject_label' => "{$target->nrk} – {$target->nama_karyawan}",
            'before'        => self::userSnapshot($target),
            'notes'         => 'User permanently deleted.',
        ]);
    }

    public static function userAccessUpdated(User $target, array $newAccess): void
    {
        self::record('users', 'update', [
            'subject'       => $target,
            'subject_label' => "{$target->nrk} – {$target->nama_karyawan}",
            'after'         => ['access_rights' => $newAccess],
            'notes'         => 'Access rights updated.',
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Submission CRUD
    // ──────────────────────────────────────────────────────────────────────────

    public static function submissionCreated(\App\Models\Data\PengajuanSurat $s, bool $isDraft): void
    {
        self::record('submission', 'create', [
            'subject'       => $s,
            'subject_label' => $s->nomor_surat . ' – ' . $s->perihal,
            'after'         => self::submissionSnapshot($s),
            'notes'         => $isDraft ? 'Saved as draft.' : 'Submitted for approval.',
        ]);
    }

    public static function submissionUpdated(\App\Models\Data\PengajuanSurat $s, array $original, bool $isDraft, string $statusBefore): void
    {
        $after   = self::submissionSnapshot($s);
        $before  = self::submissionSnapshot($s, $original);
        $changed = self::diffKeys($before, $after);

        $notes = match (true) {
            !$isDraft && $statusBefore === 'rejected' => 'Resubmitted after rejection.',
            !$isDraft => 'Draft submitted for approval.',
            default   => 'Draft updated. Fields changed: ' . implode(', ', $changed),
        };

        self::record(
            'submission',
            !$isDraft && $statusBefore === 'rejected' ? 'resubmit' : 'update',
            [
                'subject'       => $s,
                'subject_label' => $s->nomor_surat . ' – ' . $s->perihal,
                'before'        => array_intersect_key($before, array_flip($changed)),
                'after'         => array_intersect_key($after,  array_flip($changed)),
                'notes'         => $notes,
            ]
        );
    }

    public static function submissionDeleted(\App\Models\Data\PengajuanSurat $s): void
    {
        self::record('submission', 'delete', [
            'subject'       => $s,
            'subject_label' => $s->nomor_surat . ' – ' . $s->perihal,
            'before'        => self::submissionSnapshot($s),
            'notes'         => 'Submission permanently deleted.',
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Approval
    // ──────────────────────────────────────────────────────────────────────────

    public static function approved(\App\Models\Data\PengajuanSurat $s, string $tahap, ?string $catatan = null): void
    {
        self::record('approval', 'approve', [
            'subject'       => $s,
            'subject_label' => $s->nomor_surat . ' – ' . $s->perihal,
            'after'         => ['status' => $s->status, 'tahap' => $tahap],
            'notes'         => $catatan
                ? "Approved at stage [{$tahap}]. Note: {$catatan}"
                : "Approved at stage [{$tahap}].",
        ]);
    }

    public static function rejected(\App\Models\Data\PengajuanSurat $s, string $tahap, string $reason): void
    {
        self::record('approval', 'reject', [
            'subject'       => $s,
            'subject_label' => $s->nomor_surat . ' – ' . $s->perihal,
            'after'         => ['status' => 'rejected', 'tahap' => $tahap],
            'notes'         => "Rejected at stage [{$tahap}]. Reason: {$reason}",
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // TTE
    // ──────────────────────────────────────────────────────────────────────────

    public static function ttePlaced(\App\Models\Data\PengajuanSurat $s, string $tahap, int $count): void
    {
        self::record('tte', 'tte_placed', [
            'subject'       => $s,
            'subject_label' => $s->nomor_surat . ' – ' . $s->perihal,
            'after'         => ['tahap' => $tahap, 'placement_count' => $count],
            'notes'         => "TTE placement added: {$count} stamp(s) at stage [{$tahap}].",
        ]);
    }

    public static function tteSigned(\App\Models\Data\PengajuanSurat $s, \App\Models\Data\PengajuanTtePlacement $placement): void
    {
        self::record('tte', 'tte_signed', [
            'subject'       => $s,
            'subject_label' => $s->nomor_surat . ' – ' . $s->perihal,
            'after'         => [
                'tahap'        => $placement->tahap,
                'placement_id' => $placement->id,
                'halaman'      => $placement->halaman,
                'pos_x'        => $placement->pos_x,
                'pos_y'        => $placement->pos_y,
                'signed_at'    => now()->toDateTimeString(),
            ],
            'notes' => "TTE signed at stage [{$placement->tahap}], page {$placement->halaman}.",
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Private helpers
    // ──────────────────────────────────────────────────────────────────────────

    private static function sanitize(array $data): array
    {
        $hidden = ['password', 'password_confirmation', 'private_key', 'token', 'remember_token'];
        return array_diff_key($data, array_flip($hidden));
    }

    private static function userSnapshot(User $user, ?array $override = null): array
    {
        $data = $override ?? $user->toArray();
        return array_intersect_key($data, array_flip([
            'nrk', 'nama_karyawan', 'email', 'id_perusahaan',
            'id_departemen', 'jabatan', 'wilker', 'is_admin',
        ]));
    }

    private static function submissionSnapshot(\App\Models\Data\PengajuanSurat $s, ?array $override = null): array
    {
        $data = $override ?? $s->toArray();
        return array_intersect_key($data, array_flip([
            'nomor_surat', 'perihal', 'tanggal_surat', 'id_perusahaan',
            'id_kepada', 'id_jenis_dokumen', 'id_sifat_surat',
            'status', 'require_tte_pengaju', 'require_tte_kepada',
        ]));
    }

    private static function diffKeys(array $before, array $after): array
    {
        $changed = [];
        foreach ($after as $key => $val) {
            if (!array_key_exists($key, $before) || $before[$key] != $val) {
                $changed[] = $key;
            }
        }
        return $changed;
    }
}