<?php

namespace App\Models\OldModels;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Block extends Model
{
    use HasFactory;

    use SoftDeletes;

    protected $connection = 'old';

protected $fillable = [
        'client_id',
        'school_id',
        'reason',
    ];

    protected $guarded = [];
    protected $hidden = ["updated_at"];
}
