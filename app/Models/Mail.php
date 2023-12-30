<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
 use Illuminate\Database\Eloquent\SoftDeletes; use Illuminate\Database\Eloquent\Factories\HasFactory;
/**
 * @OA\Schema(
 *      schema="Mail",
 *      required={"type","subject","body","school_id"},
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
 */class Mail extends Model
{
     use SoftDeletes;    use HasFactory;    public $table = 'mails';

    public $fillable = [
        'type',
        'lang',
        'subject',
        'body',
        'school_id'
    ];

    protected $casts = [
        'type' => 'string',
        'lang' => 'string',
        'subject' => 'string',
        'body' => 'string'
    ];

    public static array $rules = [
        'type' => 'required|string|max:125',
        'lang' => 'required|string|max:125',
        'subject' => 'required|string|max:125',
        'body' => 'required|string|max:65535',
        'school_id' => 'required',
        'created_at' => 'nullable',
        'updated_at' => 'nullable'
    ];

    public function school(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\School::class, 'school_id');
    }
}
