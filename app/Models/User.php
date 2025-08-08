<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
 use Illuminate\Database\Eloquent\SoftDeletes;
 use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Traits\HasRoles;
use App\V5\Models\UserSeasonRole;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @OA\Schema(
 *      schema="User",
 *      required={"password","type","active"},
 *      @OA\Property(
 *          property="username",
 *          description="",
 *          readOnly=false,
 *          nullable=true,
 *          type="string",
 *      ),
 *      @OA\Property(
 *           property="first_name",
 *           description="",
 *           readOnly=false,
 *           nullable=false,
 *           type="string",
 *       ),
 *       @OA\Property(
 *           property="last_name",
 *           description="",
 *           readOnly=false,
 *           nullable=false,
 *           type="string",
 *       ),
 *      @OA\Property(
 *          property="email",
 *          description="",
 *          readOnly=false,
 *          nullable=true,
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="password",
 *          description="",
 *          readOnly=false,
 *          nullable=false,
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
 *          property="type",
 *          description="",
 *          readOnly=false,
 *          nullable=false,
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="active",
 *          description="avoids login",
 *          readOnly=false,
 *          nullable=false,
 *          type="boolean",
 *      ),
 *      @OA\Property(
 *          property="recover_token",
 *          description="",
 *          readOnly=false,
 *          nullable=true,
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
 *          property="logout",
 *          description="",
 *          readOnly=false,
 *          nullable=true,
 *          type="boolean",
 *      )
 * )
 */class User extends Authenticatable
{
     use LogsActivity, SoftDeletes, HasFactory, Notifiable, HasApiTokens, HasRoles;    public $table = 'users';

    public $fillable = [
        'username',
        'email',
        'first_name',
        'last_name',
        'password',
        'image',
        'type',
        'active',
        'recover_token',
        'logout'
    ];

    protected $casts = [
        'username' => 'string',
        'first_name' => 'string',
        'last_name' => 'string',
        'email' => 'string',
        'password' => 'string',
        'image' => 'string',
        'type' => 'string',
        'active' => 'boolean',
        'recover_token' => 'string',
        'logout' => 'boolean'
    ];

    public static array $rules = [
        'username' => 'nullable|string|max:255',
        'first_name' => 'nullable|string|max:255',
        'last_name' => 'nullable|string|max:255',
        'email' => 'nullable|string|max:100',
        'password' => 'nullable|string|max:255',
        'image' => 'nullable|string',
        'type' => 'nullable|string|max:100',
        'active' => 'nullable|boolean',
        'recover_token' => 'nullable|string|max:65535',
        'created_at' => 'nullable',
        'updated_at' => 'nullable',
        'deleted_at' => 'nullable',
        'logout' => 'nullable|boolean'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function bookingLogs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\BookingLog::class, 'user_id');
    }

    public function clients(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\Client::class, 'user_id');
    }

    public function monitors(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\Monitor::class, 'user_id');
    }

    public function schoolUsers(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\SchoolUser::class, 'user_id');
    }

    public function schools(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(
            \App\Models\School::class,
            'school_users', // Tabla pivote
            'user_id', // Clave foránea del modelo actual
            'school_id' // Clave foránea del modelo relacionado
        )->where('schools.active', 1)
         ->whereNull('schools.deleted_at');
    }

    /**
     * Get the primary school for this user
     */
    public function getCurrentSchool()
    {
        return $this->schools()->first();
    }

    /**
     * Get the primary school ID for this user
     */
    public function getCurrentSchoolId(): ?int
    {
        $school = $this->getCurrentSchool();
        return $school ? $school->id : null;
    }

    public function userSeasonRoles(): HasMany
    {
        return $this->hasMany(UserSeasonRole::class);
    }

    public function getSeasonRole(int $seasonId): ?string
    {
        return $this->userSeasonRoles()
            ->where('season_id', $seasonId)
            ->value('role');
    }

    public function hasSeasonRole(int $seasonId, string $role): bool
    {
        return $this->userSeasonRoles()
            ->where('season_id', $seasonId)
            ->where('role', $role)
            ->exists();
    }

    /**
     * Scope to safely load schools without column ambiguity
     */
    public function scopeWithSafeSchools($query)
    {
        return $query->with(['schools' => function ($schoolQuery) {
            $schoolQuery->select([
                'schools.id',
                'schools.name',
                'schools.slug', 
                'schools.logo'
            ])->where('schools.active', 1)
              ->whereNull('schools.deleted_at');
        }]);
    }

    public function getActivitylogOptions(): LogOptions
    {
         return LogOptions::defaults();
    }

    public function setInitialPermissionsByRole(): void
    {
        // Crear permisos para cada tabla
        $adminTables = ['clients', 'bookings', 'monitors', 'courses', 'degrees', 'evaluations', 'schools', 'stations',
            'services', 'tasks', 'seasons', 'vouchers'];
        $clientTables = ['clients', 'bookings'];
        $monitorTables = ['clients', 'bookings', 'monitors', 'courses', 'degrees', 'evaluations', 'tasks', 'vouchers'];

        // Selecciona el conjunto de tablas basado en el rol
        switch ($this->type) {
            case 1:
            case 'admin':
                $tables = $adminTables;
                break;
            case 2:
            case 'client':
                $tables = $clientTables;
                break;
            case 3:
            case 'monitor':
                $tables = $monitorTables;
                break;
            default:
                $tables = []; // o manejar como un error
        }

        foreach ($tables as $table) {
            $permissions = ["view $table", "create $table", "update $table", "delete $table"];
            $this->givePermissionTo($permissions);
        }
    }
}
