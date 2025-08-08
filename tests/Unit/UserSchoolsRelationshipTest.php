<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\School;
use App\Models\SchoolUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserSchoolsRelationshipTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_schools_relationship_returns_data_without_sql_ambiguity()
    {
        // Create a test user
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'active' => true
        ]);

        // Create an active test school
        $school = School::factory()->create([
            'name' => 'Active Test School',
            'slug' => 'active-test-school',
            'logo' => 'https://example.com/logo.png',
            'active' => 1,
            'deleted_at' => null
        ]);

        // Create school_users relationship
        SchoolUser::create([
            'user_id' => $user->id,
            'school_id' => $school->id
        ]);

        // Test the relationship - this should not throw SQL ambiguity error
        $schools = $user->schools()->get();

        // Assertions
        $this->assertCount(1, $schools);
        
        $retrievedSchool = $schools->first();
        $this->assertNotNull($retrievedSchool);
        $this->assertEquals($school->id, $retrievedSchool->id);
        $this->assertEquals('Active Test School', $retrievedSchool->name);
        $this->assertEquals('active-test-school', $retrievedSchool->slug);
    }

    public function test_user_schools_relationship_with_select_works_correctly()
    {
        $user = User::factory()->create(['active' => true]);
        $school = School::factory()->create([
            'name' => 'Test School',
            'active' => 1
        ]);

        SchoolUser::create([
            'user_id' => $user->id,
            'school_id' => $school->id
        ]);

        // Test with explicit select - this should not cause SQL ambiguity
        $schools = $user->schools()
            ->select(['schools.id', 'schools.name', 'schools.slug', 'schools.logo'])
            ->get();

        $this->assertCount(1, $schools);
        $retrievedSchool = $schools->first();
        $this->assertEquals($school->id, $retrievedSchool->id);
        $this->assertEquals('Test School', $retrievedSchool->name);
    }

    public function test_get_current_school_id_method_works()
    {
        $user = User::factory()->create(['active' => true]);
        $school = School::factory()->create(['active' => 1]);

        SchoolUser::create([
            'user_id' => $user->id,
            'school_id' => $school->id
        ]);

        $currentSchoolId = $user->getCurrentSchoolId();
        $this->assertEquals($school->id, $currentSchoolId);
    }

    public function test_with_safe_schools_scope_works()
    {
        $user = User::factory()->create(['active' => true]);
        $school = School::factory()->create([
            'name' => 'Safe Test School',
            'active' => 1
        ]);

        SchoolUser::create([
            'user_id' => $user->id,
            'school_id' => $school->id
        ]);

        // Test the scope method
        $userWithSchools = User::withSafeSchools()
            ->where('id', $user->id)
            ->first();

        $this->assertNotNull($userWithSchools);
        $this->assertTrue($userWithSchools->relationLoaded('schools'));
        
        $loadedSchools = $userWithSchools->schools;
        $this->assertCount(1, $loadedSchools);
        $this->assertEquals('Safe Test School', $loadedSchools->first()->name);
    }
}