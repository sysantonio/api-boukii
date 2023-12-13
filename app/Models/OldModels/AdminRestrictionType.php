<?php

namespace App\Models\OldModels;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class AdminRestrictionsAction
 */
class AdminRestrictionType extends Model
{
	protected $table = 'admin_restriction_types';
	public $timestamps = false;

	protected $connection = 'old';

protected $fillable = [
		'name'
	];

    // Constant AdminRestrictionType IDs as of 2023-03:
    /** RestrictionType for 'course' type */
    const ID_COURSE = 1;
     /** RestrictionType for 'booking' type */
    const ID_BOOKING = 2;
     /** RestrictionType for 'client' type */
    const ID_CLIENT = 3;
     /** RestrictionType for 'teacher' type */
    const ID_TEACHER = 4;
}
