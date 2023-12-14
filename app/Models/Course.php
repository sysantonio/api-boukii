<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\LogOptions;

/**
 * @OA\Schema(
 *      schema="Course",
 *      required={"course_type","is_flexible","sport_id","school_id","name","short_description","description","price","currency","max_participants","confirm_attendance","active","online"},
 *      @OA\Property(
 *          property="course_type",
 *          description="",
 *          readOnly=false,
 *          nullable=false,
 *          type="boolean",
 *      ),
 *      @OA\Property(
 *          property="is_flexible",
 *          description="",
 *          readOnly=false,
 *          nullable=false,
 *          type="boolean",
 *      ),
 *      @OA\Property(
 *          property="name",
 *          description="",
 *          readOnly=false,
 *          nullable=false,
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="short_description",
 *          description="",
 *          readOnly=false,
 *          nullable=false,
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="description",
 *          description="",
 *          readOnly=false,
 *          nullable=false,
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="price",
 *          description="If duration_flexible, per 15min",
 *          readOnly=false,
 *          nullable=false,
 *          type="number",
 *          format="number"
 *      ),
 *      @OA\Property(
 *          property="currency",
 *          description="",
 *          readOnly=false,
 *          nullable=false,
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="date_start",
 *          description="",
 *          readOnly=false,
 *          nullable=true,
 *          type="string",
 *          format="date"
 *      ),
 *      @OA\Property(
 *          property="date_end",
 *          description="",
 *          readOnly=false,
 *          nullable=true,
 *          type="string",
 *          format="date"
 *      ),
 *      @OA\Property(
 *          property="date_start_res",
 *          description="",
 *          readOnly=false,
 *          nullable=true,
 *          type="string",
 *          format="date"
 *      ),
 *      @OA\Property(
 *          property="date_end_res",
 *          description="",
 *          readOnly=false,
 *          nullable=true,
 *          type="string",
 *          format="date"
 *      ),
 *     @OA\Property(
 *            property="age_min",
 *            description="Minimum age for participants",
 *            type="integer",
 *            nullable=true
 *        ),
 *        @OA\Property(
 *            property="age_max",
 *            description="Maximum age for participants",
 *            type="integer",
 *            nullable=true
 *        ),
 *      @OA\Property(
 *          property="confirm_attendance",
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
 *     @OA\Property(
 *          property="unique",
 *          description="",
 *          readOnly=false,
 *          nullable=false,
 *          type="boolean",
 *      ),
 *      @OA\Property(
 *          property="online",
 *          description="",
 *          readOnly=false,
 *          nullable=false,
 *          type="boolean",
 *      ),
 *      @OA\Property(
 *          property="image",
 *          description="",
 *          readOnly=false,
 *          nullable=true,
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="translations",
 *          description="",
 *          readOnly=false,
 *          nullable=true,
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="price_range",
 *          description="",
 *          readOnly=false,
 *          nullable=true,
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="discounts",
 *          description="",
 *          readOnly=false,
 *          nullable=true,
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="settings",
 *          description="",
 *          readOnly=false,
 *          nullable=true,
 *          type="string",
 *      ),
 *      @OA\Property(
 *           property="sport_id",
 *           description="Sport ID",
 *           type="integer",
 *           nullable=false
 *       ),
 *       @OA\Property(
 *           property="school_id",
 *           description="School ID",
 *           type="integer",
 *           nullable=false
 *       ),
 *       @OA\Property(
 *           property="station_id",
 *           description="Station ID",
 *           type="integer",
 *           nullable=true
 *       ),
 *       @OA\Property(
 *           property="max_participants",
 *           description="Maximum number of participants",
 *           type="integer",
 *           nullable=false
 *       ),
 *       @OA\Property(
 *           property="duration",
 *           description="Duration of the course",
 *           type="string",
 *           nullable=false
 *       ),
 *       @OA\Property(
 *           property="hour_min",
 *           description="Minimum hour for the course",
 *           type="string",
 *           nullable=true
 *       ),
 *       @OA\Property(
 *           property="hour_max",
 *           description="Maximum hour for the course",
 *           type="string",
 *           nullable=true
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
class Course extends Model
{
    use SoftDeletes;
    use HasFactory;

    public $table = 'courses';

    public $fillable = [
        'course_type',
        'is_flexible',
        'sport_id',
        'school_id',
        'station_id',
        'name',
        'short_description',
        'description',
        'old_id',
        'price',
        'currency',
        'max_participants',
        'duration',
        'date_start',
        'date_end',
        'date_start_res',
        'date_end_res',
        'hour_min',
        'hour_max',
        'age_min',
        'age_max',
        'confirm_attendance',
        'active',
        'unique',
        'online',
        'image',
        'translations',
        'price_range',
        'discounts',
        'settings'
    ];

    protected $casts = [
        'is_flexible' => 'boolean',
        'name' => 'string',
        'short_description' => 'string',
        'description' => 'string',
        'price' => 'decimal:2',
        'currency' => 'string',
        'duration' => 'string',
        'date_start' => 'date',
        'date_end' => 'date',
        'date_start_res' => 'date',
        'date_end_res' => 'date',
        'hour_min' => 'string',
        'hour_max' => 'string',
        'confirm_attendance' => 'boolean',
        'active' => 'boolean',
        'unique' => 'boolean',
        'online' => 'boolean',
        'image' => 'string',
        'translations' => 'string',
        'price_range' => 'json',
        'discounts' => 'json',
        'settings' => 'json'
    ];

    public static array $rules = [
        'course_type' => 'required',
        'is_flexible' => 'required|boolean',
        'sport_id' => 'required',
        'school_id' => 'required',
        'station_id' => 'nullable',
        'name' => 'required|string|max:65535',
        'short_description' => 'required|string|max:65535',
        'description' => 'required|string|max:65535',
        'price' => 'required|numeric',
        'currency' => 'required|string|max:3',
        'max_participants' => 'required',
        'duration' => 'nullable',
        'date_start' => 'nullable',
        'date_end' => 'nullable',
        'date_start_res' => 'nullable',
        'date_end_res' => 'nullable',
        'hour_min' => 'nullable|string|max:255',
        'hour_max' => 'nullable|string|max:255',
        'confirm_attendance' => 'required|boolean',
        'active' => 'required|boolean',
        'unique' => 'boolean',
        'online' => 'required|boolean',
        'image' => 'nullable|string',
        'age_min' => 'nullable',
        'age_max' => 'nullable',
        'translations' => 'nullable|string',
        'price_range' => 'nullable',
        'discounts' => 'nullable|string',
        'settings' => 'nullable|string',
        'created_at' => 'nullable',
        'updated_at' => 'nullable',
        'deleted_at' => 'nullable'
    ];

    public function sport(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Sport::class, 'sport_id');
    }

    public function school(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\School::class, 'school_id');
    }

    public function station(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Station::class, 'station_id');
    }

    public function bookingUsers(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\BookingUser::class, 'course_id');
    }

    public function courseDates(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\CourseDate::class, 'course_id');
    }

    public function courseDatesActive(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\CourseDate::class, 'course_id')
            ->where('active', 1);
    }


    public function courseExtras(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\CourseExtra::class, 'course_id');
    }

    public function courseGroups(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\CourseGroup::class, 'course_id');
    }

    public function courseSubgroups(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\CourseSubgroup::class, 'course_id');
    }

    public function scopeWithAvailableDates(Builder $query, $type, $startDate, $endDate, $sportId = 1,
                                                    $clientId = null, $degreeId = null, $getLowerDegrees = false,
                                                    $degreeOrders = null, $min_age = null, $max_age = null)
    {

        $clientAge = null;
        $clientDegreeOrder = null;
        $clientDegree = null;

        $query->where('sport_id', $sportId);

        // Si se proporcionó clientId, obtener los detalles del cliente

        if ($clientId) {
            $client = Client::find($clientId);
            $clientDegreeId = $client->sports()->where('sports.id', $sportId)->first()->pivot->degree_id ?? null;
            if ($clientDegreeId) {
                $clientDegree = Degree::find($clientDegreeId);
            }
            $clientAge = Carbon::parse($client->birth_date)->age;

        }

        if ($degreeId) {
            $clientDegree = Degree::find($degreeId);
        }

        if ($type == 1) {
            // Lógica para cursos de tipo 1
            $query->whereHas('courseDates',
                function (Builder $subQuery) use (
                    $startDate, $endDate, $clientDegree, $clientAge, $getLowerDegrees, $min_age, $max_age
                ) {
                    $subQuery->where('date', '>=', $startDate)
                        ->where('date', '<=', $endDate)
                        ->whereHas('courseSubgroups',
                            function (Builder $subQuery) use (
                                $clientDegree, $clientAge, $getLowerDegrees, $min_age, $max_age
                            ) {
                                $subQuery->whereRaw('max_participants > (SELECT COUNT(*) FROM booking_users
                                WHERE booking_users.course_date_id = course_dates.id)')
                                    ->whereHas('courseGroup',
                                        function (Builder $groupQuery) use (
                                            $clientDegree, $clientAge, $getLowerDegrees, $min_age, $max_age
                                        ) {

                                            // Comprobación de degree_order y rango de edad
                                            if ($clientDegree !== null && $getLowerDegrees) {

                                                $groupQuery->whereHas('degree',
                                                    function (Builder $degreeQuery) use ($clientDegree) {
                                                        $degreeQuery->where('degree_order', '<=',
                                                            $clientDegree->degree_order);
                                                    });
                                            } else if ($clientDegree !== null && !$getLowerDegrees) {
                                                $groupQuery->whereHas('degree',
                                                    function (Builder $degreeQuery) use ($clientDegree) {
                                                        $degreeQuery->where('id', $clientDegree->id);
                                                    });
                                            }
                                            if ($clientAge !== null) {
                                                // Filtrado por la edad del cliente si está disponible
                                                $groupQuery->where('age_min', '<=', $clientAge)
                                                    ->where('age_max', '>=', $clientAge);
                                            } else {
                                                // Filtrado por min_age y max_age si clientId no está disponible
                                                if ($min_age !== null) {
                                                    $groupQuery->where('age_min', '<=', $min_age);
                                                }
                                                if ($max_age !== null) {
                                                    $groupQuery->where('age_max', '>=', $max_age);
                                                }
                                            }
                                            // Comprobación de degree_order y rango de edad
                                            if (!empty($degreeOrders)) {
                                                $groupQuery->whereHas('degree',
                                                    function (Builder $degreeQuery) use ($degreeOrders, $getLowerDegrees
                                                    ) {
                                                        if ($getLowerDegrees) {
                                                            // Si se pide obtener grados inferiores, compara con el menor grado
                                                            $degreeQuery->where('degree_order', '<=',
                                                                min($degreeOrders));
                                                        } else {
                                                            // En caso contrario, filtra por los grados específicos
                                                            $degreeQuery->whereIn('degree_order', $degreeOrders);
                                                        }
                                                    });
                                            }
                                        });
                            });
                });
        } elseif ($type == 2) {
            // Lógica para cursos de tipo 2
            $query->where('course_type', 2)
                ->where('sport_id', $sportId) // Asegúrate de que estás filtrando por el sport_id correcto
                ->whereHas('courseDates', function (Builder $subQuery) use ($startDate, $endDate, $clientAge) {
                    $subQuery->where('date', '>=', $startDate)
                        ->where('date', '<=', $endDate)
                        ->whereRaw('courses.max_participants > (SELECT COUNT(*) FROM booking_users
                        WHERE booking_users.course_date_id = course_dates.id)');

                });

            if ($clientAge) {
                $query->where('age_min', '<=', $clientAge)
                    ->where('age_max', '>=', $clientAge);
            }
        }

        return $query;
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('activity');
    }
}
