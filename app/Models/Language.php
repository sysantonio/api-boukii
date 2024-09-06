<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @OA\Schema(
 *      schema="Language",
 *      required={"code", "name"},
 *      @OA\Property(
 *          property="code",
 *          description="ISO language code (e.g., 'en' for English, 'es' for Spanish)",
 *          type="string",
 *          nullable=false
 *      ),
 *      @OA\Property(
 *          property="name",
 *          description="Full name of the language (e.g., 'English', 'Spanish')",
 *          type="string",
 *          nullable=false
 *      ),
 *      @OA\Property(
 *          property="created_at",
 *          description="Creation timestamp of the language record",
 *          type="string",
 *          format="date-time",
 *          nullable=true
 *      ),
 *      @OA\Property(
 *          property="updated_at",
 *          description="Last update timestamp of the language record",
 *          type="string",
 *          format="date-time",
 *          nullable=true
 *      ),
 *      @OA\Property(
 *          property="deleted_at",
 *          description="Deletion timestamp of the language record",
 *          type="string",
 *          format="date-time",
 *          nullable=true
 *      )
 * )
 */
class Language extends Model
{
      use LogsActivity, SoftDeletes, HasFactory;     public $table = 'languages';

    public $fillable = [
        'code',
        'name'
    ];

    protected $casts = [
        'code' => 'string',
        'name' => 'string'
    ];

    public static array $rules = [
        'code' => 'required|string|max:10',
        'name' => 'required|string|max:255',
        'created_at' => 'nullable',
        'updated_at' => 'nullable',
        'deleted_at' => 'nullable'
    ];

    public function clients(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\Client::class, 'language3_id');
    }

    public function client1s(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\Client::class, 'language1_id');
    }

    public function client2s(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\Client::class, 'language2_id');
    }

    public function monitors(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\Monitor::class, 'language2_id');
    }

    public function monitor3s(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\Monitor::class, 'language3_id');
    }

    public function monitor4s(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\Monitor::class, 'language1_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
         return LogOptions::defaults()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('activity');
    }
}
