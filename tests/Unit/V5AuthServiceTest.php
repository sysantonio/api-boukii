<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\V5\Modules\Auth\Services\AuthV5Service;
use App\Models\User;
use App\V5\Models\UserSeasonRole;
use App\V5\Models\Season;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class V5AuthServiceTest extends TestCase
{
    use RefreshDatabase;

    protected AuthV5Service $authService;
    protected User $user;
    protected Season $season;
    protected Role $role;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->authService = new AuthV5Service();
        
        // Create test user
        $this->user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'active' => true
        ]);

        // Create test season
        $this->season = Season::factory()->create();

        // Create role and permission
        $this->role = Role::create(['name' => 'manager']);
        $permission = Permission::create(['name' => 'view schools']);
        $this->role->givePermissionTo($permission);

        // Create user season role
        UserSeasonRole::create([
            'user_id' => $this->user->id,
            'season_id' => $this->season->id,
            'role' => 'manager'
        ]);
    }

    public function test_successful_login_with_season_context()
    {
        $credentials = [
            'email' => 'test@example.com',
            'password' => 'password123',
            'season_id' => $this->season->id
        ];

        $result = $this->authService->loginWithSeasonContext($credentials);

        $this->assertArrayHasKey('token', $result);
        $this->assertArrayHasKey('user', $result);
        $this->assertArrayHasKey('role', $result);
        $this->assertArrayHasKey('season_id', $result);
        $this->assertArrayHasKey('permissions', $result);

        $this->assertEquals('manager', $result['role']);
        $this->assertEquals($this->season->id, $result['season_id']);
        $this->assertContains('view schools', $result['permissions']);
    }

    public function test_login_fails_with_missing_credentials()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Email, password, and season_id are required');

        $this->authService->loginWithSeasonContext([
            'email' => 'test@example.com'
            // Missing password and season_id
        ]);
    }

    public function test_login_fails_with_invalid_user()
    {
        $this->expectException(\Illuminate\Auth\AuthenticationException::class);
        $this->expectExceptionMessage('User not found or inactive');

        $credentials = [
            'email' => 'nonexistent@example.com',
            'password' => 'password123',
            'season_id' => $this->season->id
        ];

        $this->authService->loginWithSeasonContext($credentials);
    }

    public function test_login_fails_with_wrong_password()
    {
        $this->expectException(\Illuminate\Auth\AuthenticationException::class);
        $this->expectExceptionMessage('Invalid credentials');

        $credentials = [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
            'season_id' => $this->season->id
        ];

        $this->authService->loginWithSeasonContext($credentials);
    }

    public function test_login_fails_when_user_has_no_season_role()
    {
        $anotherSeason = Season::factory()->create();

        $this->expectException(\Illuminate\Auth\AuthenticationException::class);
        $this->expectExceptionMessage('User has no role for this season');

        $credentials = [
            'email' => 'test@example.com',
            'password' => 'password123',
            'season_id' => $anotherSeason->id
        ];

        $this->authService->loginWithSeasonContext($credentials);
    }

    public function test_check_season_permissions()
    {
        $permissions = $this->authService->checkSeasonPermissions(
            $this->user->id, 
            $this->season->id
        );

        $this->assertIsArray($permissions);
        $this->assertContains('view schools', $permissions);
    }

    public function test_check_permissions_returns_empty_for_invalid_season()
    {
        $permissions = $this->authService->checkSeasonPermissions(
            $this->user->id, 
            999 // Non-existent season
        );

        $this->assertEmpty($permissions);
    }

    public function test_assign_season_role()
    {
        $newSeason = Season::factory()->create();
        $adminRole = Role::create(['name' => 'admin']);

        $this->authService->assignSeasonRole(
            $this->user->id, 
            $newSeason->id, 
            'admin'
        );

        $this->assertDatabaseHas('user_season_roles', [
            'user_id' => $this->user->id,
            'season_id' => $newSeason->id,
            'role' => 'admin'
        ]);
    }

    public function test_assign_invalid_role_throws_exception()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid role: nonexistent');

        $this->authService->assignSeasonRole(
            $this->user->id, 
            $this->season->id, 
            'nonexistent'
        );
    }

    public function test_get_user_seasons()
    {
        $anotherSeason = Season::factory()->create(['name' => 'Another Season']);
        UserSeasonRole::create([
            'user_id' => $this->user->id,
            'season_id' => $anotherSeason->id,
            'role' => 'admin'
        ]);

        $seasons = $this->authService->getUserSeasons($this->user->id);

        $this->assertCount(2, $seasons);
        $this->assertEquals('manager', $seasons[0]['role']);
    }

    public function test_has_season_permission()
    {
        $hasPermission = $this->authService->hasSeasonPermission(
            $this->user->id,
            $this->season->id,
            'view schools'
        );

        $this->assertTrue($hasPermission);

        $hasInvalidPermission = $this->authService->hasSeasonPermission(
            $this->user->id,
            $this->season->id,
            'nonexistent permission'
        );

        $this->assertFalse($hasInvalidPermission);
    }

    public function test_logout_revokes_tokens()
    {
        // Create a token first
        $token = $this->user->createToken('test-token');

        $this->assertEquals(1, $this->user->tokens()->count());

        $this->authService->logout($this->user->id);

        $this->assertEquals(0, $this->user->fresh()->tokens()->count());
    }
}