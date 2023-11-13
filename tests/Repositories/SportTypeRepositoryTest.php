<?php

namespace Tests\Repositories;

use App\Models\SportType;
use App\Repositories\SportTypeRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\ApiTestTrait;

class SportTypeRepositoryTest extends TestCase
{
    use ApiTestTrait, DatabaseTransactions;

    protected SportTypeRepository $sportTypeRepo;

    public function setUp() : void
    {
        parent::setUp();
        $this->sportTypeRepo = app(SportTypeRepository::class);
    }

    /**
     * @test create
     */
    public function test_create_sport_type()
    {
        $sportType = SportType::factory()->make()->toArray();

        $createdSportType = $this->sportTypeRepo->create($sportType);

        $createdSportType = $createdSportType->toArray();
        $this->assertArrayHasKey('id', $createdSportType);
        $this->assertNotNull($createdSportType['id'], 'Created SportType must have id specified');
        $this->assertNotNull(SportType::find($createdSportType['id']), 'SportType with given id must be in DB');
        $this->assertModelData($sportType, $createdSportType);
    }

    /**
     * @test read
     */
    public function test_read_sport_type()
    {
        $sportType = SportType::factory()->create();

        $dbSportType = $this->sportTypeRepo->find($sportType->id);

        $dbSportType = $dbSportType->toArray();
        $this->assertModelData($sportType->toArray(), $dbSportType);
    }

    /**
     * @test update
     */
    public function test_update_sport_type()
    {
        $sportType = SportType::factory()->create();
        $fakeSportType = SportType::factory()->make()->toArray();

        $updatedSportType = $this->sportTypeRepo->update($fakeSportType, $sportType->id);

        $this->assertModelData($fakeSportType, $updatedSportType->toArray());
        $dbSportType = $this->sportTypeRepo->find($sportType->id);
        $this->assertModelData($fakeSportType, $dbSportType->toArray());
    }

    /**
     * @test delete
     */
    public function test_delete_sport_type()
    {
        $sportType = SportType::factory()->create();

        $resp = $this->sportTypeRepo->delete($sportType->id);

        $this->assertTrue($resp);
        $this->assertNull(SportType::find($sportType->id), 'SportType should not exist in DB');
    }
}
