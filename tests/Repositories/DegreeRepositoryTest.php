<?php

namespace Tests\Repositories;

use App\Models\Degree;
use App\Repositories\DegreeRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\ApiTestTrait;

class DegreeRepositoryTest extends TestCase
{
    use ApiTestTrait, DatabaseTransactions;

    protected DegreeRepository $degreeRepo;

    public function setUp() : void
    {
        parent::setUp();
        $this->degreeRepo = app(DegreeRepository::class);
    }

    /**
     * @test create
     */
    public function test_create_degree()
    {
        $degree = Degree::factory()->make()->toArray();

        $createdDegree = $this->degreeRepo->create($degree);

        $createdDegree = $createdDegree->toArray();
        $this->assertArrayHasKey('id', $createdDegree);
        $this->assertNotNull($createdDegree['id'], 'Created Degree must have id specified');
        $this->assertNotNull(Degree::find($createdDegree['id']), 'Degree with given id must be in DB');
        $this->assertModelData($degree, $createdDegree);
    }

    /**
     * @test read
     */
    public function test_read_degree()
    {
        $degree = Degree::factory()->create();

        $dbDegree = $this->degreeRepo->find($degree->id);

        $dbDegree = $dbDegree->toArray();
        $this->assertModelData($degree->toArray(), $dbDegree);
    }

    /**
     * @test update
     */
    public function test_update_degree()
    {
        $degree = Degree::factory()->create();
        $fakeDegree = Degree::factory()->make()->toArray();

        $updatedDegree = $this->degreeRepo->update($fakeDegree, $degree->id);

        $this->assertModelData($fakeDegree, $updatedDegree->toArray());
        $dbDegree = $this->degreeRepo->find($degree->id);
        $this->assertModelData($fakeDegree, $dbDegree->toArray());
    }

    /**
     * @test delete
     */
    public function test_delete_degree()
    {
        $degree = Degree::factory()->create();

        $resp = $this->degreeRepo->delete($degree->id);

        $this->assertTrue($resp);
        $this->assertNull(Degree::find($degree->id), 'Degree should not exist in DB');
    }
}
