<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
 use Illuminate\Database\Eloquent\SoftDeletes; use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\LogOptions;

/**
 * @OA\Schema(
 *      schema="Station",
 *      required={"name","country","province","address","image","map","latitude","longitude","num_hanger","num_chairlift","num_cabin","num_cabin_large","num_fonicular","show_details","active"},
 *      @OA\Property(
 *          property="name",
 *          description="",
 *          readOnly=false,
 *          nullable=false,
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="cp",
 *          description="",
 *          readOnly=false,
 *          nullable=true,
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="city",
 *          description="",
 *          readOnly=false,
 *          nullable=true,
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="country",
 *          description="",
 *          readOnly=false,
 *          nullable=false,
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="province",
 *          description="",
 *          readOnly=false,
 *          nullable=false,
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="address",
 *          description="",
 *          readOnly=false,
 *          nullable=false,
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="image",
 *          description="",
 *          readOnly=false,
 *          nullable=false,
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="map",
 *          description="",
 *          readOnly=false,
 *          nullable=false,
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="latitude",
 *          description="",
 *          readOnly=false,
 *          nullable=false,
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="longitude",
 *          description="",
 *          readOnly=false,
 *          nullable=false,
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="show_details",
 *          description="",
 *          readOnly=false,
 *          nullable=false,
 *          type="boolean",
 *      ),
 *      @OA\Property(
 *          property="active",
 *          description="",
 *          readOnly=false,
 *          nullable=false,
 *          type="boolean",
 *      ),
 *      @OA\Property(
 *          property="accuweather",
 *          description="",
 *          readOnly=false,
 *          nullable=true,
 *          type="string",
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
 *          property="created_at",
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
 */class Station extends Model
{
     use SoftDeletes;    use HasFactory;    public $table = 'stations';

    public $fillable = [
        'name',
        'cp',
        'city',
        'country',
        'province',
        'address',
        'image',
        'map',
        'latitude',
        'longitude',
        'num_hanger',
        'num_chairlift',
        'num_cabin',
        'num_cabin_large',
        'num_fonicular',
        'show_details',
        'active',
        'accuweather'
    ];

    protected $casts = [
        'name' => 'string',
        'cp' => 'string',
        'city' => 'string',
        'country' => 'string',
        'province' => 'string',
        'address' => 'string',
        'image' => 'string',
        'map' => 'string',
        'latitude' => 'string',
        'longitude' => 'string',
        'show_details' => 'boolean',
        'active' => 'boolean',
        'accuweather' => 'string'
    ];

    public static array $rules = [
        'name' => 'required|string|max:255',
        'cp' => 'nullable|string|max:65535',
        'city' => 'nullable|string|max:65535',
        'country' => 'required|string|max:65535',
        'province' => 'required|string|max:65535',
        'address' => 'required|string|max:100',
        'image' => 'required|string|max:500',
        'map' => 'required|string|max:500',
        'latitude' => 'required|string|max:100',
        'longitude' => 'required|string|max:100',
        'num_hanger' => 'required',
        'num_chairlift' => 'required',
        'num_cabin' => 'required',
        'num_cabin_large' => 'required',
        'num_fonicular' => 'required',
        'show_details' => 'required|boolean',
        'active' => 'required|boolean',
        'accuweather' => 'nullable|string|max:65535',
        'updated_at' => 'nullable',
        'created_at' => 'nullable',
        'deleted_at' => 'nullable'
    ];

    public function courses(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\Course::class, 'station_id');
    }

    public function monitorNwds(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\MonitorNwd::class, 'station_id');
    }

    public function monitorsSchools(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\MonitorsSchool::class, 'station_id');
    }

    public function stationServices(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\StationService::class, 'station_id');
    }

    public function stationsSchools(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\StationsSchool::class, 'station_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
         return LogOptions::defaults()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('activity');
    }
}
