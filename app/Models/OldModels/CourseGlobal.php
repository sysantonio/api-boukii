<?php

namespace App\Models\OldModels;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseGlobal extends Model
{
    use HasFactory;

    protected $with = ['priceRanges'];

    protected $connection = 'old';

protected $fillable = [
        'date_start_global',
        'date_end_global',
        'name_global',
        'price_global',
        'short_description_global',
        'description_global',
        'dates_global',
        'is_definitive'
    ];

    protected $casts = [
        'dates_global' => 'json', // Esto indica que el campo "dates_global" debe ser tratado como JSON
    ];
    public function courses()
    {
        return $this->hasMany(Course2::class, 'group_id');
    }

    public function priceRanges()
    {
        return $this->hasMany(PriceRange::class, 'group_id');
    }
}
