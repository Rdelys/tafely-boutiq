<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OtpRequestLog extends Model
{
    protected $fillable = [
        'email',
        'contexte',
        'ip',
    ];
}