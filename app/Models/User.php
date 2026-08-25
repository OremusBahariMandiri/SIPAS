<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\DataMaster\Perusahaan;
use App\Models\DataMaster\Departemen;
use App\Models\DataMaster\Tte;
use App\Models\Data\PengajuanSurat;
use App\Models\Data\PengajuanTerusan;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'nrk',
        'email',
        'nama_karyawan',
        'password',
        'id_perusahaan',
        'id_departemen',
        'jabatan',
        'wilker',
        'is_admin',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'is_admin' => 'integer',
    ];

    // ── Relasi ───────────────────────────────────────────────

    public function perusahaan(): BelongsTo
    {
        return $this->belongsTo(Perusahaan::class, 'id_perusahaan');
    }

    public function departemen(): BelongsTo
    {
        return $this->belongsTo(Departemen::class, 'id_departemen');
    }

    public function akses(): HasMany
    {
        return $this->hasMany(UsersAccess::class, 'id_users');
    }

    public function tte(): HasOne
    {
        return $this->hasOne(Tte::class, 'id_user');
    }

    public function ttes(): HasMany
    {
        return $this->hasMany(Tte::class, 'id_user');
    }

    public function tteList(): HasMany
    {
        return $this->hasMany(Tte::class, 'id_user');
    }

    public function pengajuanSurats(): HasMany
    {
        return $this->hasMany(PengajuanSurat::class, 'id_user');
    }

    public function pengajuanTerusans(): HasMany
    {
        return $this->hasMany(PengajuanTerusan::class, 'id_user');
    }

    // ── Helpers ──────────────────────────────────────────────

    public function isAdmin(): bool
    {
        return $this->is_admin === 1;
    }

    public function hasAccess(string $menu, string $tipeAkses): bool
    {
        return $this->akses()
            ->where('menu_access', $menu)
            ->where($tipeAkses, 1)
            ->exists();
    }

    public function tteForPerusahaan(int $idPerusahaan): ?Tte
    {
        return $this->ttes()
            ->where('id_perusahaan', $idPerusahaan)
            ->valid()
            ->first();
    }
}
