<?php

namespace App\Models\OldModels;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Communication extends Model
{
    use HasFactory, SoftDeletes;

    protected $connection = 'old';

protected $fillable = [
        'title',
        'text',
        'image',
        'communication_reason_id',
        'communication_receiver_id',
        'communication_school_id',
        'communication_station_id',
    ];

    protected $guarded = [];
    protected $hidden = ["updated_at"];
}
