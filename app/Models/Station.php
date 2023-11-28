<?php

namespace App\Models;

use App\Http\Utils\AccuweatherHelpers;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
 use Illuminate\Database\Eloquent\SoftDeletes; use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\LogOptions;

/**
 * @OA\Schema(
 *      schema="Station",
 *      required={"name","country","province","address","image","map","latitude","longitude","num_hanger","num_chairlift","num_cabin","num_cabin_large","num_fonicular","show_details","active"},
 *      @OA\Property(
 *           property="name",
 *           description="Name of the station",
 *           type="string"
 *       ),
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
 *       @OA\Property(
 *           property="num_hanger",
 *           description="Number of hangers",
 *           type="integer"
 *       ),
 *       @OA\Property(
 *           property="num_chairlift",
 *           description="Number of chairlifts",
 *           type="integer"
 *       ),
 *       @OA\Property(
 *           property="num_cabin",
 *           description="Number of cabins",
 *           type="integer"
 *       ),
 *       @OA\Property(
 *           property="num_cabin_large",
 *           description="Number of large cabins",
 *           type="integer"
 *       ),
 *       @OA\Property(
 *           property="num_fonicular",
 *           description="Number of funiculars",
 *           type="integer"
 *       ),
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
 */
class Station extends Model
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

    /**
     * Get "this" Station weather forecast;
     * this takes a while (and costs X money per Y queries),
     * so store in database as cache.
     */
    public function downloadAccuweatherData()
    {
        $data = $this->accuweather ? json_decode($this->accuweather, true) : [];
        $ah = new AccuweatherHelpers();

        // We need its LocationKey inside Accuweather
        $locationKey = $data['LocationKey'] ?? '';
        if (empty($locationKey))
        {
            $locationKey = $ah->getLocationKeyByCoords($this->latitude, $this->longitude);
            $data['LocationKey'] = $locationKey;
        }

        if (!empty($locationKey))
        {
            // 2. Get the forecast: 12 hours, 5 days
            // As of 2022-11 this project API-key doesn't allow more days
            $data['12HoursForecast'] = [];
            foreach ($ah->get12HourForecast($locationKey) as $line)
            {
                $data['12HoursForecast'][] = [
                    'time' => Carbon::parse($line['DateTime'])->format('H:i'),
                    'temperature' => $line['Temperature']['Value'],
                    'icon' => $line['WeatherIcon']
                ];
            }

            $data['5DaysForecast'] = [];
            foreach ($ah->getDailyForecast($locationKey, 5) as $line)
            {
                $data['5DaysForecast'][] = [
                    'day' => Carbon::parse($line['Date'])->format('Y-m-d'),
                    'temperature_min' => $line['Temperature']['Minimum']['Value'],
                    'temperature_max' => $line['Temperature']['Maximum']['Value'],
                    'icon' => $line['Day']['Icon']
                ];
            }
        }

        $this->accuweather = json_encode($data);
        $this->save();
    }


    /**
     * Get all Stations weather forecast.
     */
    public static function downloadAllAccuweatherData()
    {
        foreach (Station::get() as $s)
        {
            $s->downloadAccuweatherData();
        }
    }

    public function getActivitylogOptions(): LogOptions
    {
         return LogOptions::defaults()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('activity');
    }
}
