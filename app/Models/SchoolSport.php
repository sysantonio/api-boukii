<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
 use Illuminate\Database\Eloquent\SoftDeletes; use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\LogOptions;

/**
 * @OA\Schema(
 *      schema="SchoolSport",
 *      required={"school_id","sport_id"},
 *      @OA\Property(
 *           property="school_id",
 *           description="ID of the school",
 *           type="integer",
 *           format="int64",
 *           example=1
 *       ),
 *       @OA\Property(
 *           property="sport_id",
 *           description="ID of the sport",
 *           type="integer",
 *           format="int64",
 *           example=1
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
 */class SchoolSport extends Model
{
     use SoftDeletes;    use HasFactory;    public $table = 'school_sports';
    use \Awobaz\Compoships\Compoships;

    public $fillable = [
        'school_id',
        'sport_id'
    ];

    protected $casts = [

    ];

    public static array $rules = [
        'school_id' => 'required',
        'sport_id' => 'required',
        'created_at' => 'nullable',
        'updated_at' => 'nullable',
        'deleted_at' => 'nullable'
    ];

    public function school(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\School::class, 'school_id');
    }

    public function sport(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Sport::class, 'sport_id');
    }

    public function degrees()
    {
        return $this->hasMany(
            Degree::class,
          [  'school_id',
            'sport_id'],
         [  'school_id',
             'sport_id']

        );
    }

    public function getActivitylogOptions(): LogOptions
    {
         return LogOptions::defaults()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('activity');
    }
}
