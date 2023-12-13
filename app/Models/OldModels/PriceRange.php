<?php

namespace App\Models\OldModels;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PriceRange extends Model
{
    use HasFactory, SoftDeletes;

    protected $connection = 'old';

protected $fillable = [
        'course_id',
        'school_id',
        'time_interval',
        'num_pax',
        'price',
        'percentage',
        'is_default',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function scopeIsDefault($query, $isDefault)
    {
        if (is_null($isDefault)) {
            return $query;
        } else {
            return $query->where('is_default', $isDefault);
        }
    }

    public function scopeByCourseId($query, $courseId)
    {
        if (is_null($courseId)) {
            return $query;
        } else {
            return $query->where('course_id', $courseId);
        }
    }
}
