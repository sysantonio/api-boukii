<?php

namespace Tests\V5\Feature;

use App\Models\School;
use App\Models\User;
use App\V5\Models\Season;
use App\V5\Models\SchoolSeasonSettings;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SchoolSeasonTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('role_has_permissions');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('user_season_roles');
        Schema::dropIfExists('school_season_settings');
        Schema::dropIfExists('seasons');
        Schema::dropIfExists('schools');
        Schema::dropIfExists('users');
        Schema::dropIfExists('personal_access_tokens');

        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->morphs('tokenable');
            $table->string('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->bigInteger('id')->primary();
            $table->string('email')->nullable();
            $table->string('password');
            $table->string('type', 100);
            $table->boolean('active')->default(1);
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();
        });

        Schema::create('schools', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description');
            $table->string('slug');
            $table->boolean('active')->default(1);
            $table->json('settings')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('seasons', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_active')->default(false);
            $table->unsignedBigInteger('school_id');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('school_season_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('season_id');
            $table->string('key');
            $table->json('value')->nullable();
            $table->timestamps();
        });

        Schema::create('user_season_roles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('season_id');
            $table->string('role');
            $table->timestamps();
            $table->unique(['user_id', 'season_id']);
        });

        Schema::create('roles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
        });

        Schema::create('permissions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
        });

        Schema::create('role_has_permissions', function (Blueprint $table) {
            $table->unsignedBigInteger('permission_id');
            $table->unsignedBigInteger('role_id');
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('role_has_permissions');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('user_season_roles');
        Schema::dropIfExists('school_season_settings');
        Schema::dropIfExists('seasons');
        Schema::dropIfExists('schools');
        Schema::dropIfExists('users');
        Schema::dropIfExists('personal_access_tokens');
        parent::tearDown();
    }

    private function setupUser(int $seasonId, bool $withPermission = true): string
    {
        $user = User::create([
            'id' => 1,
            'email' => 'user@test.com',
            'password' => Hash::make('pass'),
            'type' => 'admin',
            'active' => 1,
        ]);
        $this->actingAs($user);

        DB::table('user_season_roles')->insert([
            'user_id' => $user->id,
            'season_id' => $seasonId,
            'role' => 'viewer',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if ($withPermission) {
            $roleId = DB::table('roles')->insertGetId([
                'name' => 'viewer',
                'guard_name' => 'web',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $permissionId = DB::table('permissions')->insertGetId([
                'name' => 'view schools',
                'guard_name' => 'web',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('role_has_permissions')->insert([
                'permission_id' => $permissionId,
                'role_id' => $roleId,
            ]);
        }

        $login = $this->postJson('/api/v5/auth/login', [
            'email' => 'user@test.com',
            'password' => 'pass',
            'season_id' => $seasonId,
        ]);

        $login->assertStatus(200);

        return $login->json('token');
    }

    public function test_access_denied_without_permission(): void
    {
        $schoolId = DB::table('schools')->insertGetId([
            'name' => 'Test School',
            'description' => 'desc',
            'slug' => 'test',
            'active' => true,
            'settings' => json_encode([]),
        ]);

        $seasonId = DB::table('seasons')->insertGetId([
            'name' => 'S1',
            'start_date' => '2024-01-01',
            'end_date' => '2024-12-31',
            'is_active' => true,
            'school_id' => $schoolId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $token = $this->setupUser($seasonId, false);

        $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->getJson('/api/v5/schools?season_id=' . $seasonId)
            ->assertStatus(403);
    }

    public function test_can_list_schools_when_permission_granted(): void
    {
        $school = School::create([
            'name' => 'Test School',
            'description' => 'desc',
            'slug' => 'test',
            'active' => true,
            'settings' => json_encode([]),
        ]);

        $season = Season::create([
            'name' => 'Winter',
            'start_date' => '2024-01-01',
            'end_date' => '2024-02-01',
            'is_active' => true,
            'school_id' => $school->id,
        ]);

        SchoolSeasonSettings::create([
            'school_id' => $school->id,
            'season_id' => $season->id,
            'key' => 'currency',
            'value' => json_encode('CHF'),
        ]);

        $token = $this->setupUser($season->id, true);

        $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->getJson('/api/v5/schools?season_id=' . $season->id)
            ->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonPath('0.id', $school->id)
            ->assertJsonPath('0.season_settings.0.key', 'currency');
    }
}
