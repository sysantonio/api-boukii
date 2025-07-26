<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BookingDraft extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'booking_drafts';

    protected $fillable = [
        'user_id',
        'session_id',
        'data',
        'expires_at',
    ];

    protected $casts = [
        'data' => 'array',
        'expires_at' => 'datetime',
    ];

    public static array $rules = [
        'user_id' => 'nullable|integer',
        'session_id' => 'required|string',
        'data' => 'required|array',
        'expires_at' => 'nullable|date',
    ];
}
