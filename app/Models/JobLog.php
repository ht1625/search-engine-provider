<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Enums\LogLevel;

class JobLog extends Model
{
    protected $fillable = [
        'job_name',
        'message',
        'level',
        'context',
    ];

    protected $casts = [
        'level' => LogLevel::class,
        'context' => 'array',
    ];
}
