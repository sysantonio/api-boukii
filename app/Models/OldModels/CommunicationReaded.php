<?php

namespace App\Models\OldModels;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommunicationReaded extends Model
{
    use HasFactory;

    protected $connection = 'old';

protected $fillable = [
        'communication_id',
        'school_id',
    ];

    protected $guarded = [];
    protected $hidden = ["updated_at"];
}
