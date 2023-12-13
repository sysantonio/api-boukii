<?php

namespace App\Models\OldModels;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Mail extends Model
{
    protected $connection = 'old';

protected $fillable = [
        'type',
        'subject',
        'body',
        'school_id',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }
}
