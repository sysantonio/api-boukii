<?php

namespace Tests\Feature;

use App\V5\Models\Season;
use App\V5\Modules\Season\Services\SeasonSnapshotService;
use App\V5\Modules\Season\Repositories\SeasonSnapshotRepository;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Tests\TestCase;

class SeasonWorkflowTest extends TestCase
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
            $table->boolean('is_closed')->default(false);
            $table->timestamp('closed_at')->nullable();
            $table->unsignedBigInteger('school_id');
            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();
        });

        Schema::create('season_snapshots', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('season_id');
            $table->string('snapshot_type');
            $table->text('snapshot_data')->nullable();
            $table->timestamp('snapshot_date')->nullable();
            $table->boolean('is_immutable')->default(false);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->text('description')->nullable();
            $table->string('checksum', 64);
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('season_snapshots');
        Schema::dropIfExists('seasons');
        parent::tearDown();
    }

    public function test_season_lifecycle_operations(): void
    {
        $create = $this->postJson('/api/v5/seasons', [
            'name' => 'Workflow',
            'start_date' => '2024-01-01',
            'end_date' => '2024-12-31',
            'is_active' => false,
            'school_id' => 1,
        ]);
        $create->assertStatus(201);
        $id = $create->json('id');

        $this->putJson('/api/v5/seasons/'.$id, ['is_active' => true])
            ->assertStatus(200)
            ->assertJsonPath('is_active', true);

        $season = Season::find($id);
        $snapshotService = new SeasonSnapshotService(new SeasonSnapshotRepository());
        $snapshot = $snapshotService->createImmutableSnapshot($season, 'manual', $season->toArray());
        $this->assertTrue($snapshot->is_immutable);
        $this->assertEquals($id, $snapshot->season_id);

        $this->postJson('/api/v5/seasons/'.$id.'/close')
            ->assertStatus(200)
            ->assertJsonPath('is_closed', true);

        $season->refresh();
        $this->assertTrue($season->is_closed);
        $this->assertFalse($season->is_active);
    }
}
