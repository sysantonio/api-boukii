<?php

namespace App\Models\OldModels;

use Illuminate\Database\Eloquent\Model;

class UserObservations extends Model
{
    protected $table = 'user_observations';

    protected $connection = 'old';

protected $fillable = [
        'general',
        'notes',
        'historical',
        'user_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function toArray()
    {
        return [
            'general' => $this->general ?? '',
            'notes' => $this->notes ?? '',
            'historical' => $this->historical ?? ''
        ];
    }
}
