<?php

namespace App\Models\OldModels;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseGroups extends Model
{
    use HasFactory;

    protected $connection = 'old';

protected $fillable = [
        'course_id',
        'degree_id',
        'recommended_age',
        'teachers_min',
        'teachers_max',
        'observations',
        'teacher_min_degree',
        'auto',
        'deleted',
    ];

    protected $guarded = [];
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
    ];

}
