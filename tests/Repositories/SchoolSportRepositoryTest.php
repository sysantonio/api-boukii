<?php

namespace Tests\Repositories;

use App\Models\SchoolSport;
use App\Repositories\SchoolSportRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\ApiTestTrait;

class SchoolSportRepositoryTest extends TestCase
{
    use ApiTestTrait, DatabaseTransactions;

    protected SchoolSportRepository $schoolSportRepo;

    public function setUp() : void
    {
        parent::setUp();
        $this->schoolSportRepo = app(SchoolSportRepository::class);
    }

    /**
     * @test create
     */
    public function test_create_school_sport()
    {
        $schoolSport = SchoolSport::factory()->make()->toArray();

        $createdSchoolSport = $this->schoolSportRepo->create($schoolSport);

        $createdSchoolSport = $createdSchoolSport->toArray();
        $this->assertArrayHasKey('id', $createdSchoolSport);
        $this->assertNotNull($createdSchoolSport['id'], 'Created SchoolSport must have id specified');
        $this->assertNotNull(SchoolSport::find($createdSchoolSport['id']), 'SchoolSport with given id must be in DB');
        $this->assertModelData($schoolSport, $createdSchoolSport);
    }

    /**
     * @test read
     */
    public function test_read_school_sport()
    {
        $schoolSport = SchoolSport::factory()->create();

        $dbSchoolSport = $this->schoolSportRepo->find($schoolSport->id);

        $dbSchoolSport = $dbSchoolSport->toArray();
        $this->assertModelData($schoolSport->toArray(), $dbSchoolSport);
    }

    /**
     * @test update
     */
    public function test_update_school_sport()
    {
        $schoolSport = SchoolSport::factory()->create();
        $fakeSchoolSport = SchoolSport::factory()->make()->toArray();

        $updatedSchoolSport = $this->schoolSportRepo->update($fakeSchoolSport, $schoolSport->id);

        $this->assertModelData($fakeSchoolSport, $updatedSchoolSport->toArray());
        $dbSchoolSport = $this->schoolSportRepo->find($schoolSport->id);
        $this->assertModelData($fakeSchoolSport, $dbSchoolSport->toArray());
    }

    /**
     * @test delete
     */
    public function test_delete_school_sport()
    {
        $schoolSport = SchoolSport::factory()->create();

        $resp = $this->schoolSportRepo->delete($schoolSport->id);

        $this->assertTrue($resp);
        $this->assertNull(SchoolSport::find($schoolSport->id), 'SchoolSport should not exist in DB');
    }
}
