<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
 use Illuminate\Database\Eloquent\SoftDeletes; use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @OA\Schema(
 *      schema="Mail",
 *      required={"type","school_id"},
 *      @OA\Property(
 *          property="type",
 *          description="",
 *          readOnly=false,
 *          nullable=false,
 *          type="string",
 *      ),
 *     @OA\Property(
 *           property="lang",
 *           description="",
 *           readOnly=false,
 *           nullable=false,
 *           type="string",
 *       ),
 *      @OA\Property(
 *          property="subject",
 *          description="",
 *          readOnly=false,
 *          nullable=false,
 *          type="string",
 *      ),
 *      @OA\Property(
 *           property="title",
 *           description="",
 *           readOnly=false,
 *           nullable=false,
 *           type="string",
 *       ),
 *      @OA\Property(
 *          property="body",
 *          description="",
 *          readOnly=false,
 *          nullable=false,
 *          type="string",
 *      ),
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
 *      )
 * )
 */

class Mail extends Model
{
       use LogsActivity, SoftDeletes, HasFactory;     public $table = 'mails';

    public $fillable = [
        'type',
        'lang',
        'subject',
        'title',
        'body',
        'school_id'
    ];

    protected $casts = [
        'type' => 'string',
        'lang' => 'string',
        'subject' => 'string',
        'title' => 'string',
        'body' => 'string'
    ];

    public static array $rules = [
        'type' => 'nullable|string|max:125',
        'lang' => 'nullable|string|max:125',
        'subject' => 'nullable|string',
        'title' => 'nullable|string',
        'body' => 'nullable|string',
        'school_id' => 'required',
        'created_at' => 'nullable',
        'updated_at' => 'nullable'
    ];

    public function school(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\School::class, 'school_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
         return LogOptions::defaults()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('activity');
    }
}
