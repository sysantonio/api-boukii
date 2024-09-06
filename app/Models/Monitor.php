<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @OA\Schema(
 *      schema="Monitor",
 *      required={"first_name","last_name","birth_date","avs","work_license","bank_details","children"},
 *      @OA\Property(
 *          property="email",
 *          description="",
 *          readOnly=false,
 *          nullable=true,
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="first_name",
 *          description="",
 *          readOnly=false,
 *          nullable=false,
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="last_name",
 *          description="",
 *          readOnly=false,
 *          nullable=false,
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="birth_date",
 *          description="",
 *          readOnly=false,
 *          nullable=false,
 *          type="string",
 *          format="date"
 *      ),
 *      @OA\Property(
 *          property="phone",
 *          description="",
 *          readOnly=false,
 *          nullable=true,
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="telephone",
 *          description="",
 *          readOnly=false,
 *          nullable=true,
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="address",
 *          description="",
 *          readOnly=false,
 *          nullable=true,
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
 *          property="province",
 *          description="",
 *          readOnly=false,
 *          nullable=true,
 *          type="integer",
 *      ),
 *      @OA\Property(
 *          property="country",
 *          description="",
 *          readOnly=false,
 *          nullable=true,
 *          type="integer",
 *      ),
 *      @OA\Property(
 *           property="world_country",
 *           description="",
 *           readOnly=false,
 *           nullable=true,
 *           type="integer",
 *       ),
 *      @OA\Property(
 *          property="image",
 *          description="",
 *          readOnly=false,
 *          nullable=true,
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="avs",
 *          description="",
 *          readOnly=false,
 *          nullable=false,
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="work_license",
 *          description="",
 *          readOnly=false,
 *          nullable=false,
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="bank_details",
 *          description="",
 *          readOnly=false,
 *          nullable=false,
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="children",
 *          description="",
 *          readOnly=false,
 *          nullable=false,
 *          type="integer",
 *      ),
 *      @OA\Property(
 *          property="civil_status",
 *          description="",
 *          readOnly=false,
 *          nullable=true,
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="family_allowance",
 *          description="",
 *          readOnly=false,
 *          nullable=true,
 *          type="boolean",
 *      ),
 *      @OA\Property(
 *          property="partner_work_license",
 *          description="",
 *          readOnly=false,
 *          nullable=true,
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="partner_works",
 *          description="",
 *          readOnly=false,
 *          nullable=true,
 *          type="boolean",
 *      ),
 *      @OA\Property(
 *           property="language1_id",
 *           description="ID of the first language",
 *           type="integer",
 *           nullable=true
 *       ),
 *       @OA\Property(
 *           property="language2_id",
 *           description="ID of the second language",
 *           type="integer",
 *           nullable=true
 *       ),
 *       @OA\Property(
 *           property="language3_id",
 *           description="ID of the third language",
 *           type="integer",
 *           nullable=true
 *       ),
 *       @OA\Property(
 *           property="language4_id",
 *           description="ID of the fourth language",
 *           type="integer",
 *           nullable=true
 *       ),
 *       @OA\Property(
 *           property="language5_id",
 *           description="ID of the fifth language",
 *           type="integer",
 *           nullable=true
 *       ),
 *       @OA\Property(
 *           property="language6_id",
 *           description="ID of the sixth language",
 *           type="integer",
 *           nullable=true
 *       ),
 *        @OA\Property(
 *            property="active_school",
 *            description="ID of the active school",
 *            type="integer",
 *            nullable=true
 *        ),
 *            @OA\Property(
 *            property="active_station",
 *            description="ID of the active station",
 *            type="integer",
 *            nullable=true
 *        ),
 *       @OA\Property(
 *           property="partner_percentaje",
 *           description="Percentage of partner's work",
 *           type="string",
 *           nullable=true
 *       ),
 *       @OA\Property(
 *           property="user_id",
 *           description="User ID associated with the monitor",
 *           type="integer",
 *           nullable=true
 *       ),
 *      @OA\Property(
 *           property="active",
 *           description="",
 *           readOnly=false,
 *           nullable=false,
 *           type="boolean",
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
 */
class Monitor extends Model
{
      use LogsActivity, SoftDeletes, HasFactory;     public $table = 'monitors';

    public $fillable = [
        'email',
        'first_name',
        'last_name',
        'birth_date',
        'phone',
        'telephone',
        'address',
        'cp',
        'city',
        'province',
        'country',
        'world_country',
        'language1_id',
        'language2_id',
        'language3_id',
        'language4_id',
        'language5_id',
        'language6_id',
        'image',
        'avs',
        'work_license',
        'bank_details',
        'children',
        'civil_status',
        'family_allowance',
        'partner_work_license',
        'partner_works',
        'partner_percentaje',
        'user_id',
        'active_school',
        'active',
        'old_id',
        'active_station'
    ];

    protected $casts = [
        'email' => 'string',
        'first_name' => 'string',
        'last_name' => 'string',
        'birth_date' => 'date',
        'phone' => 'string',
        'telephone' => 'string',
        'address' => 'string',
        'cp' => 'string',
        'city' => 'string',
        'province' => 'string',
        'country' => 'string',
        'world_country' => 'string',
        'image' => 'string',
        'avs' => 'string',
        'work_license' => 'string',
        'bank_details' => 'string',
        'civil_status' => 'string',
        'family_allowance' => 'boolean',
        'partner_work_license' => 'string',
        'partner_works' => 'boolean'
    ];

    public static array $rules = [
        'email' => 'nullable|string|max:100',
        'first_name' => 'required|string|max:255',
        'last_name' => 'required|string|max:255',
        'birth_date' => 'required',
        'phone' => 'nullable|string|max:255',
        'telephone' => 'nullable|string|max:255',
        'address' => 'nullable|string|max:255',
        'cp' => 'nullable|string|max:100',
        'city' => 'nullable|string|max:65535',
        'province' => 'nullable',
        'country' => 'nullable',
        'world_country' => 'nullable',
        'language1_id' => 'nullable',
        'language2_id' => 'nullable',
        'language3_id' => 'nullable',
        'language4_id' => 'nullable',
        'language5_id' => 'nullable',
        'language6_id' => 'nullable',
        'image' => 'nullable|string',
        'avs' => 'nullable|string|max:255',
        'work_license' => 'nullable|string|max:255',
        'bank_details' => 'nullable|string|max:255',
        'children' => 'nullable',
        'civil_status' => 'nullable|string',
        'family_allowance' => 'nullable|boolean',
        'partner_work_license' => 'nullable|string|max:255',
        'partner_works' => 'nullable|boolean',
        'partner_percentaje' => 'nullable',
        'user_id' => 'nullable',
        'active_school' => 'nullable',
        'active_station' => 'nullable',
        'active' => 'nullable|boolean',
        'created_at' => 'nullable',
        'updated_at' => 'nullable',
        'deleted_at' => 'nullable'
    ];

    protected $appends = ['monitor_sports_degrees_details'];

    public function language2(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Language::class, 'language2_id');
    }

    public function activeSchool(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\School::class, 'active_school');
    }

    public function activeStation(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Station::class, 'active_station');
    }

    public function language3(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Language::class, 'language3_id');
    }

    public function language4(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Language::class, 'language4_id');
    }

    public function language5(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Language::class, 'language5_id');
    }

    public function language6(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Language::class, 'language6_id');
    }

    public function language1(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Language::class, 'language1_id');
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    public function bookingUsers(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\BookingUser::class, 'monitor_id');
    }

    public function courseSubgroups(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\CourseSubgroup::class, 'monitor_id');
    }

    public function monitorNwds(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\MonitorNwd::class, 'monitor_id');
    }

    public function monitorObservations(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\MonitorObservation::class, 'monitor_id');
    }

    public function monitorSportsDegrees(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(MonitorSportsDegree::class, 'monitor_id')
            ->with(['sport', 'degree']); // Cargar deportes y niveles relacionados
    }

    // ...

    public function getMonitorSportsDegreesDetailsAttribute()
    {
        return $this->monitorSportsDegrees->map(function ($monitorSportsDegree) {
            return [
                'sport_name' => $monitorSportsDegree->sport->name,
                'sport_icon_selected' => $monitorSportsDegree->sport->icon_selected,
                'sport_icon_unselected' => $monitorSportsDegree->sport->icon_unselected,
                'school_id' => $monitorSportsDegree->school_id,
                'sport_id' => $monitorSportsDegree->sport_id,
                'degree' => $monitorSportsDegree->degree,
                'monitor_sport_authorized_degrees' => $monitorSportsDegree->monitorSportAuthorizedDegrees ? $monitorSportsDegree->monitorSportAuthorizedDegrees->reverse() : [],
            ];
        });
    }

    public function sports(): \Illuminate\Database\Eloquent\Relations\HasManyThrough
    {
        return $this->hasManyThrough(
            \App\Models\Sport::class, // Modelo final al que quieres llegar
            \App\Models\MonitorSportsDegree::class, // Modelo intermedio
            'monitor_id', // Clave foránea en el modelo intermedio
            'id', // Clave foránea en el modelo final
            'id', // Clave local en el modelo inicial
            'sport_id' // Clave local en el modelo intermedio
        );
    }

    public function monitorsSchools(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\MonitorsSchool::class, 'monitor_id');
    }

    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }


    public static function isMonitorBusy($monitorId, $date, $startTime, $endTime, $excludeId = null)
    {
        // Verificar si el monitor está ocupado en la fecha y horario especificados
        $isBooked = BookingUser::where('monitor_id', $monitorId)
            ->whereDate('date', $date)
            ->where(function ($query) use ($startTime, $endTime) {
                if ($startTime && $endTime) {

                    $query->whereTime('hour_start', '<', $endTime)
                        ->whereTime('hour_end', '>', $startTime);
                }

            })->where('status', 1)
            ->exists();

        $hasFullDayNwd = MonitorNwd::where('monitor_id', $monitorId)
            ->whereDate('start_date', '<=', $date)
            ->whereDate('end_date', '>=', $date)
            ->where('full_day', true)
            ->where(function ($query) use ($excludeId) {
                if ($excludeId !== null) {
                    $query->where('id', '!=', $excludeId);
                }
            })
            ->exists();

        // Verificar si el monitor está ocupado en la fecha y horario especificados
        $query = MonitorNwd::where('monitor_id', $monitorId)
            ->whereDate('start_date', '<=', $date)
            ->whereDate('end_date', '>=', $date);

        if ($startTime && $endTime) {

            // Only consider time constraints if it's not a full day
            $query->where(function ($query) use ($startTime, $endTime) {
                $query->whereTime('start_time', '<', $endTime)
                    ->whereTime('end_time', '>', $startTime);
            });
        }

        // Excluir el MonitorNwd actual si se proporciona su ID
        if ($excludeId !== null) {
            $query->where('id', '!=', $excludeId);
        }

        $isNwd = $query->exists();

        $isCourse = CourseSubgroup::whereHas('courseDate', function ($query) use ($date, $startTime, $endTime) {
            $query->whereDate('date', $date)
                ->where(function ($query) use ($startTime, $endTime) {
                    if ($startTime && $endTime) {
                        $query->whereTime('hour_start', '<', $endTime)
                            ->whereTime('hour_end', '>', $startTime);
                    }

                });
        })
            ->where('monitor_id', $monitorId)
            ->exists();

        // Si el monitor está ocupado en alguno de los casos, devuelve true; de lo contrario, devuelve false.
        return $isBooked || $isNwd || $isCourse || $hasFullDayNwd;
    }

    public function getActivitylogOptions(): LogOptions
    {
         return LogOptions::defaults();
    }
}
