<?php

namespace Tests\Repositories;

use App\Models\Season;
use App\Repositories\SeasonRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\ApiTestTrait;

class SeasonRepositoryTest extends TestCase
{
    use ApiTestTrait, DatabaseTransactions;

    protected SeasonRepository $seasonRepo;

    public function setUp() : void
    {
        parent::setUp();
        $this->seasonRepo = app(SeasonRepository::class);
    }

    /**
     * @test create
     */
    public function test_create_season()
    {
        $season = Season::factory()->make()->toArray();

        $createdSeason = $this->seasonRepo->create($season);

        $createdSeason = $createdSeason->toArray();
        $this->assertArrayHasKey('id', $createdSeason);
        $this->assertNotNull($createdSeason['id'], 'Created Season must have id specified');
        $this->assertNotNull(Season::find($createdSeason['id']), 'Season with given id must be in DB');
        $this->assertModelData($season, $createdSeason);
    }

    /**
     * @test read
     */
    public function test_read_season()
    {
        $season = Season::factory()->create();

        $dbSeason = $this->seasonRepo->find($season->id);

        $dbSeason = $dbSeason->toArray();
        $this->assertModelData($season->toArray(), $dbSeason);
    }

    /**
     * @test update
     */
    public function test_update_season()
    {
        $season = Season::factory()->create();
        $fakeSeason = Season::factory()->make()->toArray();

        $updatedSeason = $this->seasonRepo->update($fakeSeason, $season->id);

        $this->assertModelData($fakeSeason, $updatedSeason->toArray());
        $dbSeason = $this->seasonRepo->find($season->id);
        $this->assertModelData($fakeSeason, $dbSeason->toArray());
    }

    /**
     * @test delete
     */
    public function test_delete_season()
    {
        $season = Season::factory()->create();

        $resp = $this->seasonRepo->delete($season->id);

        $this->assertTrue($resp);
        $this->assertNull(Season::find($season->id), 'Season should not exist in DB');
    }
}
