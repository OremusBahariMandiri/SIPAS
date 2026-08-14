<?php

namespace App\Models\Settings;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class SmtpSetting extends Model
{
    protected $table = 'smtp_settings';

    protected $fillable = [
        'mailer',
        'host',
        'port',
        'encryption',
        'username',
        'password',
        'from_address',
        'from_name',
        'is_active',
        'tested_at',
        'test_result',
    ];

    protected $casts = [
        'is_active'   => 'boolean',
        'test_result' => 'boolean',
        'tested_at'   => 'datetime',
        'port'        => 'integer',
    ];

    // ── Enkripsi password saat disimpan ───────────────────────
    public function setPasswordAttribute(string $value): void
    {
        // Hanya enkripsi ulang jika nilainya berubah (bukan placeholder)
        $this->attributes['password'] = Crypt::encryptString($value);
    }

    // ── Dekripsi password saat dibaca ─────────────────────────
    public function getPasswordAttribute(string $value): string
    {
        try {
            return Crypt::decryptString($value);
        } catch (\Exception) {
            return '';
        }
    }

    // ── Ambil config aktif (singleton) ────────────────────────
    public static function active(): ?self
    {
        return static::where('is_active', true)->latest()->first();
    }

    // ── Terapkan ke Mail config runtime ───────────────────────
    public function applyToMailer(): void
    {
        config([
            'mail.default'                       => $this->mailer,
            'mail.mailers.smtp.host'             => $this->host,
            'mail.mailers.smtp.port'             => $this->port,
            'mail.mailers.smtp.encryption'       => $this->encryption === 'none' ? null : $this->encryption,
            'mail.mailers.smtp.username'         => $this->username,
            'mail.mailers.smtp.password'         => $this->password,
            'mail.from.address'                  => $this->from_address,
            'mail.from.name'                     => $this->from_name,
        ]);
    }
}