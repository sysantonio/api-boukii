<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\LogOptions;

/**
 * @OA\Schema(
 *      schema="School",
 *      required={"name","description","fiscal_name","fiscal_id","fiscal_address","fiscal_cp","fiscal_city","iban","logo","slug","conditions_url","bookings_comission_cash","bookings_comission_boukii_pay","bookings_comission_other","school_rate","has_ski","has_snowboard","has_telemark","has_rando","inscription","type","active","settings"},
 *      @OA\Property(
 *          property="name",
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
 *          property="contact_email",
 *          description="",
 *          readOnly=false,
 *          nullable=true,
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="contact_phone",
 *          description="",
 *          readOnly=false,
 *          nullable=true,
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="contact_telephone",
 *          description="",
 *          readOnly=false,
 *          nullable=true,
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="contact_address",
 *          description="",
 *          readOnly=false,
 *          nullable=true,
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="contact_cp",
 *          description="",
 *          readOnly=false,
 *          nullable=true,
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="contact_city",
 *          description="",
 *          readOnly=false,
 *          nullable=true,
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="contact_province",
 *          description="",
 *          readOnly=false,
 *          nullable=true,
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="contact_country",
 *          description="",
 *          readOnly=false,
 *          nullable=true,
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="fiscal_name",
 *          description="",
 *          readOnly=false,
 *          nullable=false,
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="fiscal_id",
 *          description="",
 *          readOnly=false,
 *          nullable=false,
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="fiscal_address",
 *          description="",
 *          readOnly=false,
 *          nullable=false,
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="fiscal_cp",
 *          description="",
 *          readOnly=false,
 *          nullable=false,
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="fiscal_city",
 *          description="",
 *          readOnly=false,
 *          nullable=false,
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="fiscal_province",
 *          description="",
 *          readOnly=false,
 *          nullable=true,
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="fiscal_country",
 *          description="",
 *          readOnly=false,
 *          nullable=true,
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="iban",
 *          description="",
 *          readOnly=false,
 *          nullable=false,
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="logo",
 *          description="",
 *          readOnly=false,
 *          nullable=false,
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="slug",
 *          description="",
 *          readOnly=false,
 *          nullable=false,
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="cancellation_insurance_percent",
 *          description="",
 *          readOnly=false,
 *          nullable=true,
 *          type="number",
 *          format="number"
 *      ),
 *      @OA\Property(
 *          property="payrexx_instance",
 *          description="",
 *          readOnly=false,
 *          nullable=true,
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="payrexx_key",
 *          description="",
 *          readOnly=false,
 *          nullable=true,
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="conditions_url",
 *          description="",
 *          readOnly=false,
 *          nullable=false,
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="bookings_comission_cash",
 *          description="",
 *          readOnly=false,
 *          nullable=false,
 *          type="number",
 *          format="number"
 *      ),
 *      @OA\Property(
 *          property="bookings_comission_boukii_pay",
 *          description="",
 *          readOnly=false,
 *          nullable=false,
 *          type="number",
 *          format="number"
 *      ),
 *      @OA\Property(
 *          property="bookings_comission_other",
 *          description="",
 *          readOnly=false,
 *          nullable=false,
 *          type="number",
 *          format="number"
 *      ),
 *      @OA\Property(
 *          property="school_rate",
 *          description="",
 *          readOnly=false,
 *          nullable=false,
 *          type="number",
 *          format="number"
 *      ),
 *      @OA\Property(
 *          property="has_ski",
 *          description="",
 *          readOnly=false,
 *          nullable=false,
 *          type="boolean",
 *      ),
 *      @OA\Property(
 *          property="has_snowboard",
 *          description="",
 *          readOnly=false,
 *          nullable=false,
 *          type="boolean",
 *      ),
 *      @OA\Property(
 *          property="has_telemark",
 *          description="",
 *          readOnly=false,
 *          nullable=false,
 *          type="boolean",
 *      ),
 *      @OA\Property(
 *          property="has_rando",
 *          description="",
 *          readOnly=false,
 *          nullable=false,
 *          type="boolean",
 *      ),
 *      @OA\Property(
 *          property="inscription",
 *          description="",
 *          readOnly=false,
 *          nullable=false,
 *          type="boolean",
 *      ),
 *      @OA\Property(
 *          property="type",
 *          description="",
 *          readOnly=false,
 *          nullable=false,
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="active",
 *          description="",
 *          readOnly=false,
 *          nullable=false,
 *          type="boolean",
 *      ),
 *      @OA\Property(
 *          property="settings",
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
class School extends Model
{
    use SoftDeletes;    use HasFactory;    public $table = 'schools';

    public $fillable = [
        'name',
        'description',
        'contact_email',
        'contact_phone',
        'contact_telephone',
        'contact_address',
        'contact_cp',
        'contact_city',
        'contact_province',
        'contact_country',
        'fiscal_name',
        'fiscal_id',
        'fiscal_address',
        'fiscal_cp',
        'fiscal_city',
        'fiscal_province',
        'fiscal_country',
        'iban',
        'logo',
        'slug',
        'cancellation_insurance_percent',
        'payrexx_instance',
        'payrexx_key',
        'conditions_url',
        'bookings_comission_cash',
        'bookings_comission_boukii_pay',
        'bookings_comission_other',
        'school_rate',
        'has_ski',
        'has_snowboard',
        'has_telemark',
        'has_rando',
        'inscription',
        'type',
        'active',
        'settings'
    ];

    protected $casts = [
        'name' => 'string',
        'description' => 'string',
        'contact_email' => 'string',
        'contact_phone' => 'string',
        'contact_telephone' => 'string',
        'contact_address' => 'string',
        'contact_cp' => 'string',
        'contact_city' => 'string',
        'contact_province' => 'string',
        'contact_country' => 'string',
        'fiscal_name' => 'string',
        'fiscal_id' => 'string',
        'fiscal_address' => 'string',
        'fiscal_cp' => 'string',
        'fiscal_city' => 'string',
        'fiscal_province' => 'string',
        'fiscal_country' => 'string',
        'iban' => 'string',
        'logo' => 'string',
        'slug' => 'string',
        'cancellation_insurance_percent' => 'decimal:2',
        'payrexx_instance' => 'string',
        'payrexx_key' => 'string',
        'conditions_url' => 'string',
        'bookings_comission_cash' => 'decimal:2',
        'bookings_comission_boukii_pay' => 'decimal:2',
        'bookings_comission_other' => 'decimal:2',
        'school_rate' => 'float',
        'has_ski' => 'boolean',
        'has_snowboard' => 'boolean',
        'has_telemark' => 'boolean',
        'has_rando' => 'boolean',
        'inscription' => 'boolean',
        'type' => 'string',
        'active' => 'boolean',
        'settings' => 'string'
    ];

    public static array $rules = [
        'name' => 'required|string|max:255',
        'description' => 'required|string|max:100',
        'contact_email' => 'nullable|string|max:65535',
        'contact_phone' => 'nullable|string|max:65535',
        'contact_telephone' => 'nullable|string|max:65535',
        'contact_address' => 'nullable|string|max:65535',
        'contact_cp' => 'nullable|string|max:65535',
        'contact_city' => 'nullable|string|max:65535',
        'contact_province' => 'nullable|string|max:100',
        'contact_country' => 'nullable|string|max:100',
        'fiscal_name' => 'required|string|max:100',
        'fiscal_id' => 'required|string|max:100',
        'fiscal_address' => 'required|string|max:100',
        'fiscal_cp' => 'required|string|max:100',
        'fiscal_city' => 'required|string|max:100',
        'fiscal_province' => 'nullable|string|max:100',
        'fiscal_country' => 'nullable|string|max:100',
        'iban' => 'required|string|max:100',
        'logo' => 'required|string|max:500',
        'slug' => 'required|string|max:100',
        'cancellation_insurance_percent' => 'nullable|numeric',
        'payrexx_instance' => 'nullable|string|max:65535',
        'payrexx_key' => 'nullable|string|max:65535',
        'conditions_url' => 'required|string|max:100',
        'bookings_comission_cash' => 'required|numeric',
        'bookings_comission_boukii_pay' => 'required|numeric',
        'bookings_comission_other' => 'required|numeric',
        'school_rate' => 'required|numeric',
        'has_ski' => 'required|boolean',
        'has_snowboard' => 'required|boolean',
        'has_telemark' => 'required|boolean',
        'has_rando' => 'required|boolean',
        'inscription' => 'required|boolean',
        'type' => 'required|string|max:100',
        'active' => 'required|boolean',
        'settings' => 'required|string',
        'created_at' => 'nullable',
        'updated_at' => 'nullable',
        'deleted_at' => 'nullable'
    ];

    public function bookings(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\Booking::class, 'school_id');
    }

    public function clientObservations(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\ClientObservation::class, 'school_id');
    }

    public function clientsSchools(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\ClientsSchool::class, 'school_id');
    }

    public function courses(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\Course::class, 'school_id');
    }

    public function degrees(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\Degree::class, 'school_id');
    }

    public function monitorNwds(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\MonitorNwd::class, 'school_id');
    }

    public function monitorObservations(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\MonitorObservation::class, 'school_id');
    }

    public function monitorSportsDegrees(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\MonitorSportsDegree::class, 'school_id');
    }

    public function monitors(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\Monitor::class, 'active_school');
    }

    public function monitorsSchools(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\MonitorsSchool::class, 'school_id');
    }

    public function schoolColors(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\SchoolColor::class, 'school_id');
    }

    public function schoolSalaryLevels(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\SchoolSalaryLevel::class, 'school_id');
    }

    public function schoolSports(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\SchoolSport::class, 'school_id');
    }

    public function schoolUsers(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\SchoolUser::class, 'school_id');
    }

    public function seasons(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\Season::class, 'school_id');
    }

    public function stationsSchools(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\StationsSchool::class, 'school_id');
    }

    public function tasks(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\Task::class, 'school_id');
    }

    public function vouchers(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\Voucher::class, 'school_id');
    }

    public function getActivitylogOptions(): LogOptions
    {

    }
}
