<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes; use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\LogOptions;

/**
 * @OA\Schema(
 *      schema="Client",
 *      required={"first_name","last_name","birth_date"},
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
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="country",
 *          description="",
 *          readOnly=false,
 *          nullable=true,
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="image",
 *          description="",
 *          readOnly=false,
 *          nullable=true,
 *          type="string",
 *      ),
 *      @OA\Property(
 *           property="language1_id",
 *           description="First language ID",
 *           type="integer",
 *           nullable=true
 *       ),
 *       @OA\Property(
 *           property="language2_id",
 *           description="Second language ID",
 *           type="integer",
 *           nullable=true
 *       ),
 *       @OA\Property(
 *           property="language3_id",
 *           description="Third language ID",
 *           type="integer",
 *           nullable=true
 *       ),
 *       @OA\Property(
 *           property="language4_id",
 *           description="Fourth language ID",
 *           type="integer",
 *           nullable=true
 *       ),
 *       @OA\Property(
 *           property="language5_id",
 *           description="Fifth language ID",
 *           type="integer",
 *           nullable=true
 *       ),
 *       @OA\Property(
 *           property="language6_id",
 *           description="Sixth language ID",
 *           type="integer",
 *           nullable=true
 *       ),
 *       @OA\Property(
 *           property="user_id",
 *           description="User ID",
 *           type="integer",
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
 *      )
 * )
 */class Client extends Model
{
    use SoftDeletes;    use HasFactory;    public $table = 'clients';

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
        'language1_id',
        'language2_id',
        'language3_id',
        'language4_id',
        'language5_id',
        'language6_id',
        'image',
        'user_id'
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
        'image' => 'string'
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
        'language1_id' => 'nullable',
        'language2_id' => 'nullable',
        'language3_id' => 'nullable',
        'language4_id' => 'nullable',
        'language5_id' => 'nullable',
        'language6_id' => 'nullable',
        'image' => 'nullable|string',
        'user_id' => 'nullable',
        'created_at' => 'nullable',
        'updated_at' => 'nullable',
        'deleted_at' => 'nullable'
    ];

    public function language3(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Language::class, 'language3_id');
    }

    public function language1(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Language::class, 'language1_id');
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

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    public function language2(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Language::class, 'language2_id');
    }

    public function bookingUsers(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\BookingUser::class, 'client_id');
    }

    public function bookings(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\Booking::class, 'client_main_id');
    }

    public function observations(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\ClientObservation::class, 'client_id');
    }

    public function clientsSchools(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\ClientsSchool::class, 'client_id');
    }

    public function clientsUtilizers(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\ClientsUtilizer::class, 'main_id');
    }

    public function utilizers(): \Illuminate\Database\Eloquent\Relations\HasManyThrough
    {
        return $this->hasManyThrough(
            Client::class,  // Modelo de destino (Utilizer)
            ClientsUtilizer::class,  // Modelo intermedio (ClientsUtilizer)
            'main_id',  // Clave extranjera en ClientsUtilizer que relaciona con Client
            'id',  // Clave primaria en Utilizer
            'id',  // Clave primaria en Client
            'client_id'  // Clave extranjera en ClientsUtilizer que relaciona con Utilizer
        );
    }

    public function sports()
    {
        return $this->belongsToMany(Sport::class, 'clients_sports',
            'client_id', 'sport_id')
            ->using(ClientSport::class)
            ->withPivot('degree_id')
            ->withTimestamps();
    }
    public function main()
    {
        return $this->hasOneThrough(Client::class,
            ClientsUtilizer::class, 'main_id',
            'id', 'id',
            'main_id')
            ->withDefault($this);
    }

    public function clientsUtilizer3s(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\ClientsUtilizer::class, foreignKey: 'client_id');
    }

    public function evaluations(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\Evaluation::class, 'client_id');
    }

    public function vouchers(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\Voucher::class, 'client_id');
    }

    public static function checkBookingOverlap() {

    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('activity');
    }
}
