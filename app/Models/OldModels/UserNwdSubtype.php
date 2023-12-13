<?php

namespace App\Models\OldModels;

use Illuminate\Database\Eloquent\Model;

class UserNwdSubtype extends Model
{
	protected $table = 'user_nwd_aubtypes';
	public $timestamps = false;

	protected $connection = 'old';

protected $fillable = [
		'name'
	];


    // Constant UserNwdSubtype IDs as of 2022-11:
    /** UserNwdSubtype for 'simple NWD' */
    const ID_SIMPLE_NWD = 1;
    /** UserNwdSubtype for 'paid blockage' */
    const ID_PAID_BLOCKAGE = 2;
    /** UserNwdSubtype for 'unpaid blockage' */
    const ID_UNPAID_BLOCKAGE = 3;

    // Default is 'simple NWD'
    const ID_DEFAULT = self::ID_SIMPLE_NWD;
}
