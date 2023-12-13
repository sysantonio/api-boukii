<?php

namespace App\Models\OldModels;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Language
 *
 * @property int $id
 * @property string $name
 * @property string $code
 *
 * @package App\Models
 */
class Language extends Model
{
    protected $table = 'languages';
    public $timestamps = false;
}
