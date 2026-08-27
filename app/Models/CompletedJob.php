<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompletedJob extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'uuid',
        'queue',
        'display_name',
        'payload',
        'attempts',
        'run_time_ms',
        'completed_at',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
        'payload'      => 'array',
    ];
}