<?php

namespace Tests\Unit;

use App\V5\Modules\Season\Services\SeasonSnapshotService;
use App\V5\Modules\Season\Repositories\SeasonSnapshotRepository;
use App\V5\Modules\Season\Repositories\SeasonRepository;
use App\V5\Models\Season;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Tests\TestCase;

class SeasonSnapshotServiceTest extends TestCase
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
            $table->timestamp('deleted_at')->nullable();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('season_snapshots');
        Schema::dropIfExists('seasons');
        parent::tearDown();
    }

    private function getService(): SeasonSnapshotService
    {
        return new SeasonSnapshotService(new SeasonSnapshotRepository());
    }

    public function test_create_immutable_snapshot_and_validate(): void
    {
        $season = Season::create([
            'name' => 'Test',
            'start_date' => '2024-01-01',
            'end_date' => '2024-02-01',
            'is_active' => true,
            'school_id' => 1,
        ]);

        $service = $this->getService();
        $snapshot = $service->createImmutableSnapshot($season, 'manual', $season->toArray());

        $this->assertTrue($snapshot->is_immutable);
        $this->assertTrue($service->validateSnapshot($snapshot));
    }
}
