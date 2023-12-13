<?php

namespace App\Models\OldModels;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class AdminRestrictionsAction
 */
class AdminRestrictionAction extends Model
{
	protected $table = 'admin_restriction_actions';
	public $timestamps = false;

	protected $connection = 'old';

protected $fillable = [
		'name'
	];


    // Constant AdminRestrictionAction IDs as of 2023-03:
    /** RestrictionAction for 'create' action */
    const ID_CREATE = 1;
     /** RestrictionAction for 'edit' action */
    const ID_EDIT = 2;
     /** RestrictionAction for 'show' action */
    const ID_SHOW = 3;
     /** RestrictionAction for 'delete' action */
    const ID_DELETE = 4;
}
