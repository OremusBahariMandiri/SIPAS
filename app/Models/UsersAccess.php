<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UsersAccess extends Model
{
    protected $table = 'users_access';

    protected $fillable = [
        'id_users',
        'menu_access',
        'index_access',
        'create_access',
        'update_access',
        'show_access',
        'delete_access',
        'download_access',
        'export_pdf_access',
        'export_excel_access',
        'approval_access',
    ];

    protected $casts = [
        'index_access'        => 'integer',
        'create_access'       => 'integer',
        'update_access'       => 'integer',
        'show_access'         => 'integer',
        'delete_access'       => 'integer',
        'download_access'     => 'integer',
        'export_pdf_access'   => 'integer',
        'export_excel_access' => 'integer',
        'approval_access'     => 'integer',
    ];

    /**
     * Relasi ke User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_users');
    }

    /**
     * Scope: filter berdasarkan nama menu
     */
    public function scopeMenu($query, string $menu)
    {
        return $query->where('menu_access', $menu);
    }
}