<?php

namespace Tests\Feature;

use App\Models\School;
use App\V5\Models\Season;
use App\V5\Models\SchoolSeasonSettings;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;

class SchoolSeasonTest extends TestCase
{
    use WithoutMiddleware;

    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('school_season_settings');
        Schema::dropIfExists('seasons');
        Schema::dropIfExists('schools');

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
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('school_season_settings');
        Schema::dropIfExists('seasons');
        Schema::dropIfExists('schools');
        parent::tearDown();
    }

    public function test_list_schools_by_season_returns_settings(): void
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

        $this->getJson('/api/v5/schools?season_id=' . $season->id)
            ->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonPath('0.id', $school->id)
            ->assertJsonPath('0.season_settings.0.key', 'currency');
    }
}
