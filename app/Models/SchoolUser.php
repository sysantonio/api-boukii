<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
 use Illuminate\Database\Eloquent\SoftDeletes; use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @OA\Schema(
 *      schema="SchoolUser",
 *      required={"school_id","user_id"},
 *      @OA\Property(
 *           property="school_id",
 *           description="ID of the school",
 *           type="integer",
 *           format="int64",
 *           example=1
 *       ),
 *      @OA\Property(
 *           property="user_id",
 *           description="ID of the user",
 *           type="integer",
 *           format="int64",
 *           example=2
 *       ),
 *      @OA\Property(
 *          property="created_at",
 *          description="",
 *          readOnly=true,
 *          nullable=true,
 *          type="string",
 *          format="date-time"
 *      ),
 *      @OA\Property(
 *          property="updated_at",
 *          description="",
 *          readOnly=true,
 *          nullable=true,
 *          type="string",
 *          format="date-time"
 *      ),
 *      @OA\Property(
 *          property="deleted_at",
 *          description="",
 *          readOnly=true,
 *          nullable=true,
 *          type="string",
 *          format="date-time"
 *      )
 * )
 */class SchoolUser extends Model
{
      use LogsActivity, SoftDeletes, HasFactory;     public $table = 'school_users';

    public $fillable = [
        'school_id',
        'user_id'
    ];

    protected $casts = [

    ];

    public static array $rules = [
        'school_id' => 'required',
        'user_id' => 'required',
        'created_at' => 'nullable',
        'updated_at' => 'nullable',
        'deleted_at' => 'nullable'
    ];

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    public function school(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\School::class, 'school_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
         return LogOptions::defaults();
    }
}
