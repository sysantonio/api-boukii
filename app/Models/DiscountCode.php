<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
 use Illuminate\Database\Eloquent\SoftDeletes; use Illuminate\Database\Eloquent\Factories\HasFactory;
/**
 * @OA\Schema(
 *      schema="DiscountCode",
 *      required={"code","school_id"},
 *      @OA\Property(
 *          property="code",
 *          description="",
 *          readOnly=false,
 *          nullable=false,
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="quantity",
 *          description="",
 *          readOnly=false,
 *          nullable=true,
 *          type="number",
 *          format="number"
 *      ),
 *      @OA\Property(
 *          property="percentage",
 *          description="",
 *          readOnly=false,
 *          nullable=true,
 *          type="number",
 *          format="number"
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
 */class DiscountCode extends Model
{
     use SoftDeletes;    use HasFactory;    public $table = 'discounts_codes';

    public $fillable = [
        'code',
        'quantity',
        'percentage',
        'school_id',
        'total',
        'remaining'
    ];

    protected $casts = [
        'code' => 'string',
        'quantity' => 'float',
        'percentage' => 'float'
    ];

    public static array $rules = [
        'code' => 'required|string|max:255',
        'quantity' => 'nullable|numeric',
        'percentage' => 'nullable|numeric',
        'school_id' => 'required',
        'total' => 'nullable',
        'remaining' => 'nullable',
        'created_at' => 'nullable',
        'updated_at' => 'nullable',
        'deleted_at' => 'nullable'
    ];

    public function school(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\School::class, 'school_id');
    }
}
