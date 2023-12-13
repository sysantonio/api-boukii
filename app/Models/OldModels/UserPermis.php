<?php

namespace App\Models\OldModels;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPermis extends Model
{
    use HasFactory;

    protected $connection = 'old';

protected $fillable = [
        'permis1',
        'permis2',
        'permis3',
        'permis4',
        'permis5',
        'permis6',
        'permis7',
        'permis8',
        'permis9',
        'permis10',
        'permis11',
        'permis12',
        'permis13',
        'permis14',
        'permis15',
        'permis16',
        'permis17',
        'permis18',
        'permis19',
        'permis20',
        'permis21',
        'permis22',
        'permis23',
        'permis24',
        'permis25',
        'permis26',
        'permis27',
        'user_id',
    ];

    protected $guarded = [];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'id',
    ];

    /**
     * Helpers
     */

    public static function createForUser(User $user)
    {

        return self::create([
            'permis1' => true,
            'permis2' => true,
            'permis3' => true,
            'permis4' => true,
            'permis5' => true,
            'permis6' => true,
            'permis7' => true,
            'permis8' => true,
            'permis9' => true,
            'permis10' => true,
            'permis11' => true,
            'permis12' => true,
            'permis13' => true,
            'permis14' => true,
            'permis15' => true,
            'permis16' => true,
            'permis17' => true,
            'permis18' => true,
            'permis19' => true,
            'permis20' => true,
            'permis21' => true,
            'permis22' => true,
            'permis23' => true,
            'permis24' => true,
            'permis25' => true,
            'permis26' => true,
            'permis27' => true,
            'user_id' => $user->id,
        ]);

    }

}
