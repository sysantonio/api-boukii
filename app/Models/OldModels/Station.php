<?php


namespace App\Models\OldModels;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

use App\Http\AccuweatherHelpers;

/**
 * Class Station
 *
 * @property int $id
 * @property string $name
 * @property string $address
 * @property string $cp
 * @property string $city
 * @property int $country_id
 * @property int $province_id
 * @property string $image
 * @property string $latitude
 * @property string $longitude
 * @property int $percha
 * @property int $telesilla
 * @property int $cabina
 * @property int $cabina_grande
 * @property int $fonicular
 * @property bool $show_details
 * @property bool $active
 * @property string $accuweather
 * @property Carbon|null $updated_at
 * @property Carbon|null $created_at
 *
 * @property Country $country
 * @property Province $province
 * @property Collection|Activity[] $activities
 * @property Collection|Event[] $events
 * @property Collection|StationService[] $station_services
 * @property Collection|School[] $schools
 * @property Collection|School[] $schools_active
 * @property Collection|User[] $users_favorite
 *
 * @package App\Models
 */
class Station extends Model
{
	protected $table = 'stations';

	protected $casts = [
		'country_id' => 'int',
		'province_id' => 'int',
		'percha' => 'int',
		'telesilla' => 'int',
		'cabina' => 'int',
		'cabina_grande' => 'int',
		'fonicular' => 'int',
		'show_details' => 'bool',
		'active' => 'bool'
	];

	protected $connection = 'old';

protected $fillable = [
		'name',
        'address',
        'cp',
        'city',
		'country_id',
		'province_id',
        'latitude',
		'longitude',
		'image',
        'map',
		'percha',
		'telesilla',
		'cabina',
		'cabina_grande',
		'fonicular',
		'show_details',
		'active'
	];

    /**
     * Relations
     */

	public function country()
	{
		return $this->belongsTo(Country::class);
	}

	public function province()
	{
		return $this->belongsTo(Province::class);
	}

	public function activities()
	{
		return $this->hasMany(Activity::class);
	}

	public function events()
	{
		return $this->hasMany(Event::class);
	}

	public function station_services()
	{
		return $this->hasMany(StationServices::class)->orderBy('name', 'asc');
	}

	public function schools()
	{
		return $this->belongsToMany(School::class, 'stations_schools')
                    ->orderBy('name', 'asc')
					->withPivot('id');
	}

    public function schools_active()
	{
		return $this->belongsToMany(School::class, 'stations_schools')
                    ->orderBy('name', 'asc')
					->withPivot('id', 'active')
                    ->where('schools.active', '=', 1);
	}

	public function users_favorite()
	{
		return $this->belongsToMany(User::class, 'user_favorite_stations')
					->withPivot('id')
					->withTimestamps();
	}



    public function toArray()
    {
        $stationArray = [
            'id' => $this->id,
            'name' => $this->name,
            'address' => $this->address,
            'cp' => $this->cp,
            'city' => $this->city,
            'province_id' => $this->province_id,
            'province_name' => $this->province->name,
            'country_id' => $this->country_id,
            'latitude' => floatval($this->latitude),
            'longitude' => floatval($this->longitude),
            'image' => $this->image,

            'percha' => $this->percha,
            'telesilla' => $this->telesilla,
            'cabina' => $this->cabina,
            'cabina_grande' => $this->cabina_grande,
            'fonicular' => $this->fonicular,
        ];

        // Optional: full details of all its Schools, or just their count
        if ($this->relationLoaded('schools'))
        {
            $stationArray['schools'] = $this->schools->toArray();
        }
        else if (isset($this->schools_count))
        {
            $stationArray['schools_count'] = $this->schools_count;
        }

        // Optional: full list of all its Services
        if ($this->relationLoaded('station_services'))
        {
            $stationArray['services'] = $this->station_services->toArray();
        }

        return $stationArray;
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


    /**
     * Compute distance from a Station to some coordinates,
     *
     * @return int in Kilometers; -1 if error
     */
    public static function computeDistanceByID($stationID, $latitude, $longitude)
    {
        $station = self::find($stationID);
        if (!$station)
        {
            return -1;
        }
        else
        {
            return $station->computeDistance($latitude, $longitude);
        }
    }
    public function computeDistance($latitude, $longitude)
    {
        $latitude = floatval($latitude);
        $longitude = floatval($longitude);
        if ($latitude == 0 && $longitude == 0)
        {
            return -1;
        }
        else
        {
            $distance = 6371 *
                acos(
                    sin(deg2rad($this->latitude)) *
                    sin(deg2rad($latitude)) +
                    cos(deg2rad($this->longitude - $longitude)) *
                    cos(deg2rad($this->latitude)) *
                    cos(deg2rad($latitude))
                );

            return round($distance);
        }
    }
}
