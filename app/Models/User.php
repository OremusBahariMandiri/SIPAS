<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\DataMaster\Perusahaan;
use App\Models\DataMaster\Departemen;
use App\Models\DataMaster\Tte;
use Illuminate\Database\Eloquent\Relations\HasOne;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'nrk',
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

    /**
     * Relasi ke Perusahaan
     */
    public function perusahaan(): BelongsTo
    {
        return $this->belongsTo(Perusahaan::class, 'id_perusahaan');
    }

    /**
     * Relasi ke Departemen
     */
    public function departemen(): BelongsTo
    {
        return $this->belongsTo(Departemen::class, 'id_departemen');
    }

    /**
     * Relasi ke UsersAccess
     */
    public function akses(): HasMany
    {
        return $this->hasMany(UsersAccess::class, 'id_users');
    }

    /**
     * Cek apakah user adalah admin
     */
    public function isAdmin(): bool
    {
        return $this->is_admin === 1;
    }

    /**
     * Cek apakah user punya akses tertentu pada menu tertentu
     */
    public function hasAccess(string $menu, string $tipeAkses): bool
    {
        return $this->akses()
            ->where('menu_access', $menu)
            ->where($tipeAkses, 1)
            ->exists();
    }
    public function tte(): HasOne
    {
        return $this->hasOne(Tte::class, 'id_user');
    }

    public function ttes(): HasMany
    {
        return $this->hasMany(\App\Models\DataMaster\Tte::class, 'id_user');
    }

    /**
     * TTE aktif milik user untuk perusahaan tertentu
     */
    public function tteForPerusahaan(int $idPerusahaan): ?\App\Models\DataMaster\Tte
    {
        return $this->ttes()
            ->where('id_perusahaan', $idPerusahaan)
            ->valid()
            ->first();
    }
}
