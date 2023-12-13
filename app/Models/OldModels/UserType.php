<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\OldModels;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class UserType
 *
 * @property int $id
 * @property string $name
 * @property string $description
 *
 * @property Collection|User[] $users
 *
 * @package App\Models
 */
class UserType extends Model
{
	protected $table = 'user_types';
	public $timestamps = false;

	protected $connection = 'old';

protected $fillable = [
		'name',
		'description'
	];

	public function users()
	{
		return $this->hasMany(User::class, 'user_type');
	}


    // Constant UserType IDs as of 2022-10:
    /** UserType for 'administrator' type */
    const ID_ADMINISTRATOR = 1;
    /** UserType for 'client' type */
    const ID_CLIENT = 2;
    /** UserType for 'monitor' type */
    const ID_MONITOR = 3;
	/** UserType for 'superadmin' type */
	const ID_SUPERADMINISTRATOR = 4;
	/** UserType for 'visualizer' type */
	const ID_VISUALIZER = 5;
	/** UserType for 'school chat' type */
	const ID_SCHOOLCHAT = 6;
}
