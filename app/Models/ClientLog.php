<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ClientLog extends Model
{
    use HasFactory;

    public $table = 'client_logs';

    public $timestamps = false;

    protected $fillable = [
        'level',
        'message',
        'context',
        'user_id',
        'school_id',
        'created_at',
    ];

    protected $casts = [
        'context' => 'array',
        'created_at' => 'datetime',
    ];
}
