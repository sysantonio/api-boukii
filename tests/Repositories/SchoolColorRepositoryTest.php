<?php

namespace Tests\Repositories;

use App\Models\SchoolColor;
use App\Repositories\SchoolColorRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\ApiTestTrait;

class SchoolColorRepositoryTest extends TestCase
{
    use ApiTestTrait, DatabaseTransactions;

    protected SchoolColorRepository $schoolColorRepo;

    public function setUp() : void
    {
        parent::setUp();
        $this->schoolColorRepo = app(SchoolColorRepository::class);
    }

    /**
     * @test create
     */
    public function test_create_school_color()
    {
        $schoolColor = SchoolColor::factory()->make()->toArray();

        $createdSchoolColor = $this->schoolColorRepo->create($schoolColor);

        $createdSchoolColor = $createdSchoolColor->toArray();
        $this->assertArrayHasKey('id', $createdSchoolColor);
        $this->assertNotNull($createdSchoolColor['id'], 'Created SchoolColor must have id specified');
        $this->assertNotNull(SchoolColor::find($createdSchoolColor['id']), 'SchoolColor with given id must be in DB');
        $this->assertModelData($schoolColor, $createdSchoolColor);
    }

    /**
     * @test read
     */
    public function test_read_school_color()
    {
        $schoolColor = SchoolColor::factory()->create();

        $dbSchoolColor = $this->schoolColorRepo->find($schoolColor->id);

        $dbSchoolColor = $dbSchoolColor->toArray();
        $this->assertModelData($schoolColor->toArray(), $dbSchoolColor);
    }

    /**
     * @test update
     */
    public function test_update_school_color()
    {
        $schoolColor = SchoolColor::factory()->create();
        $fakeSchoolColor = SchoolColor::factory()->make()->toArray();

        $updatedSchoolColor = $this->schoolColorRepo->update($fakeSchoolColor, $schoolColor->id);

        $this->assertModelData($fakeSchoolColor, $updatedSchoolColor->toArray());
        $dbSchoolColor = $this->schoolColorRepo->find($schoolColor->id);
        $this->assertModelData($fakeSchoolColor, $dbSchoolColor->toArray());
    }

    /**
     * @test delete
     */
    public function test_delete_school_color()
    {
        $schoolColor = SchoolColor::factory()->create();

        $resp = $this->schoolColorRepo->delete($schoolColor->id);

        $this->assertTrue($resp);
        $this->assertNull(SchoolColor::find($schoolColor->id), 'SchoolColor should not exist in DB');
    }
}
