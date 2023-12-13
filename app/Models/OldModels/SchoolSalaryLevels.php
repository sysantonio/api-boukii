<?php

namespace App\Models\OldModels;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SchoolSalaryLevels extends Model
{
    use HasFactory;

    use SoftDeletes;

    protected $connection = 'old';

protected $fillable = [
        'school_id',
        'name',
        'pay',
    ];

    protected $guarded = [];
    protected $hidden = ["updated_at"];
}
