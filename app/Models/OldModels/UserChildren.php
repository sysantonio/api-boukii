<?php

namespace App\Models\OldModels;

use Illuminate\Database\Eloquent\Model;

class UserChildren extends Model
{
    protected $table = 'user_childrens';

    protected $connection = 'old';

protected $fillable = [
        'name',
        'birth_date',
        'user_id'
    ];


    public function toArray()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'birth_date' => $this->birth_date
            // unused as of 2022-11 because Children are always searched by User
            // 'user_id' => $this->user_id
        ];
    }
}
