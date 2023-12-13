<?php

namespace App\Models\OldModels;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Nationality
 *
 * @property int $id
 * @property string $name
 *
 * @package App\Models
 */
class Nationality extends Model
{
    protected $table = 'nationalities';
    public $timestamps = false;
}
