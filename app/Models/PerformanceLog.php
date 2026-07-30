<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PerformanceLog extends Model
{

    protected $fillable = [

        'method',
        'url',
        'status_code',
        'response_time',
        'memory_usage',
        'ip_address',
        'user_id',
        'is_slow',

    ];


    protected $casts = [

        'is_slow'=>'boolean'

    ];

}