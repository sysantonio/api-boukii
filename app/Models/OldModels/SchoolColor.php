<?php

namespace App\Models\OldModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SchoolColor extends Model
{
    use SoftDeletes;

    public $timestamps = true;

    protected $connection = 'old';

protected $fillable = [
        'name', 'color', 'school_id'
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }
}
