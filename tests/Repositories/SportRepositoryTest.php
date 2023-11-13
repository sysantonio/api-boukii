<?php

namespace Tests\Repositories;

use App\Models\Sport;
use App\Repositories\SportRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\ApiTestTrait;

class SportRepositoryTest extends TestCase
{
    use ApiTestTrait, DatabaseTransactions;

    protected SportRepository $sportRepo;

    public function setUp() : void
    {
        parent::setUp();
        $this->sportRepo = app(SportRepository::class);
    }

    /**
     * @test create
     */
    public function test_create_sport()
    {
        $sport = Sport::factory()->make()->toArray();

        $createdSport = $this->sportRepo->create($sport);

        $createdSport = $createdSport->toArray();
        $this->assertArrayHasKey('id', $createdSport);
        $this->assertNotNull($createdSport['id'], 'Created Sport must have id specified');
        $this->assertNotNull(Sport::find($createdSport['id']), 'Sport with given id must be in DB');
        $this->assertModelData($sport, $createdSport);
    }

    /**
     * @test read
     */
    public function test_read_sport()
    {
        $sport = Sport::factory()->create();

        $dbSport = $this->sportRepo->find($sport->id);

        $dbSport = $dbSport->toArray();
        $this->assertModelData($sport->toArray(), $dbSport);
    }

    /**
     * @test update
     */
    public function test_update_sport()
    {
        $sport = Sport::factory()->create();
        $fakeSport = Sport::factory()->make()->toArray();

        $updatedSport = $this->sportRepo->update($fakeSport, $sport->id);

        $this->assertModelData($fakeSport, $updatedSport->toArray());
        $dbSport = $this->sportRepo->find($sport->id);
        $this->assertModelData($fakeSport, $dbSport->toArray());
    }

    /**
     * @test delete
     */
    public function test_delete_sport()
    {
        $sport = Sport::factory()->create();

        $resp = $this->sportRepo->delete($sport->id);

        $this->assertTrue($resp);
        $this->assertNull(Sport::find($sport->id), 'Sport should not exist in DB');
    }
}
