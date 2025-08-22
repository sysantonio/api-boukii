<?php

namespace Tests\Feature\V5\Context;

use App\Models\School;
use App\Models\User;
use App\Support\Pivot;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class RelationsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite']);
        config(['database.connections.sqlite.database' => ':memory:']);
        config(['activitylog.enabled' => false]);
        DB::purge('sqlite');
        DB::reconnect('sqlite');

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('email')->nullable();
            $table->string('password');
            $table->string('type', 100)->nullable();
            $table->boolean('active')->default(1);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('schools', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('slug')->nullable();
            $table->boolean('active')->default(1);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create(Pivot::USER_SCHOOLS, function (Blueprint $table) {
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('school_id');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists(Pivot::USER_SCHOOLS);
        Schema::dropIfExists('schools');
        Schema::dropIfExists('users');

        parent::tearDown();
    }

    public function test_attach_and_detach_schools()
    {
        $user = User::create([
            'email' => 'user@example.com',
            'password' => 'secret',
            'type' => 'admin',
            'active' => true,
        ]);

        $school = School::create([
            'name' => 'Test School',
            'description' => 'desc',
            'slug' => 'test',
            'active' => true,
        ]);

        $user->schools()->attach($school->id);

        $this->assertDatabaseHas(Pivot::USER_SCHOOLS, [
            'user_id' => $user->id,
            'school_id' => $school->id,
            'deleted_at' => null,
        ]);

        $user->schools()->detach($school->id);

        $this->assertDatabaseMissing(Pivot::USER_SCHOOLS, [
            'user_id' => $user->id,
            'school_id' => $school->id,
            'deleted_at' => null,
        ]);
    }

    public function test_sync_schools()
    {
        $user = User::create([
            'email' => 'user@example.com',
            'password' => 'secret',
            'type' => 'admin',
            'active' => true,
        ]);

        $school1 = School::create([
            'name' => 'School 1',
            'description' => 'desc1',
            'slug' => 'school-1',
            'active' => true,
        ]);

        $school2 = School::create([
            'name' => 'School 2',
            'description' => 'desc2',
            'slug' => 'school-2',
            'active' => true,
        ]);

        $user->schools()->sync([$school1->id, $school2->id]);

        $this->assertDatabaseHas(Pivot::USER_SCHOOLS, [
            'user_id' => $user->id,
            'school_id' => $school1->id,
            'deleted_at' => null,
        ]);

        $this->assertDatabaseHas(Pivot::USER_SCHOOLS, [
            'user_id' => $user->id,
            'school_id' => $school2->id,
            'deleted_at' => null,
        ]);

        $this->assertCount(2, $user->schools()->get());
    }
}

