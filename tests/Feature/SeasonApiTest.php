<?php

namespace Tests\Feature;

use App\V5\Models\Season;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Tests\TestCase;

class SeasonApiTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('seasons', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_active')->default(false);
            $table->unsignedBigInteger('school_id');
            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('seasons');
        parent::tearDown();
    }

    public function test_can_create_and_show_season(): void
    {
        $payload = [
            'name' => 'Season A',
            'start_date' => '2024-01-01',
            'end_date' => '2024-02-01',
            'is_active' => false,
            'school_id' => 1,
        ];

        $create = $this->postJson('/api/v5/seasons', $payload);
        $create->assertStatus(201)
            ->assertJsonPath('name', 'Season A');

        $id = $create->json('id');

        $this->getJson('/api/v5/seasons/'.$id)
            ->assertStatus(200)
            ->assertJsonPath('id', $id);
    }

    public function test_can_list_seasons(): void
    {
        Season::create([
            'name' => 'S1',
            'start_date' => '2024-03-01',
            'end_date' => '2024-04-01',
            'is_active' => false,
            'school_id' => 1,
        ]);
        Season::create([
            'name' => 'S2',
            'start_date' => '2024-05-01',
            'end_date' => '2024-06-01',
            'is_active' => false,
            'school_id' => 1,
        ]);

        $this->getJson('/api/v5/seasons')
            ->assertStatus(200)
            ->assertJsonCount(2);
    }

    public function test_can_update_and_delete_season(): void
    {
        $season = Season::create([
            'name' => 'ToUpdate',
            'start_date' => '2024-07-01',
            'end_date' => '2024-08-01',
            'is_active' => false,
            'school_id' => 1,
        ]);

        $this->putJson('/api/v5/seasons/'.$season->id, ['name' => 'Updated'])
            ->assertStatus(200)
            ->assertJsonPath('name', 'Updated');

        $this->deleteJson('/api/v5/seasons/'.$season->id)
            ->assertStatus(200)
            ->assertJson(['deleted' => true]);

        $this->getJson('/api/v5/seasons/'.$season->id)->assertStatus(404);
    }
}
