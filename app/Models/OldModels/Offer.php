<?php

namespace App\Models\OldModels;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Offer extends Model
{
    use HasFactory;

    protected $connection = 'old';

protected $fillable = [
        'title',
        'description',
    ];

    protected $guarded = [];
    protected $hidden = ["updated_at"];
}
