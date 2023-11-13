<?php

namespace Tests\Repositories;

use App\Models\SchoolSalaryLevel;
use App\Repositories\SchoolSalaryLevelRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\ApiTestTrait;

class SchoolSalaryLevelRepositoryTest extends TestCase
{
    use ApiTestTrait, DatabaseTransactions;

    protected SchoolSalaryLevelRepository $schoolSalaryLevelRepo;

    public function setUp() : void
    {
        parent::setUp();
        $this->schoolSalaryLevelRepo = app(SchoolSalaryLevelRepository::class);
    }

    /**
     * @test create
     */
    public function test_create_school_salary_level()
    {
        $schoolSalaryLevel = SchoolSalaryLevel::factory()->make()->toArray();

        $createdSchoolSalaryLevel = $this->schoolSalaryLevelRepo->create($schoolSalaryLevel);

        $createdSchoolSalaryLevel = $createdSchoolSalaryLevel->toArray();
        $this->assertArrayHasKey('id', $createdSchoolSalaryLevel);
        $this->assertNotNull($createdSchoolSalaryLevel['id'], 'Created SchoolSalaryLevel must have id specified');
        $this->assertNotNull(SchoolSalaryLevel::find($createdSchoolSalaryLevel['id']), 'SchoolSalaryLevel with given id must be in DB');
        $this->assertModelData($schoolSalaryLevel, $createdSchoolSalaryLevel);
    }

    /**
     * @test read
     */
    public function test_read_school_salary_level()
    {
        $schoolSalaryLevel = SchoolSalaryLevel::factory()->create();

        $dbSchoolSalaryLevel = $this->schoolSalaryLevelRepo->find($schoolSalaryLevel->id);

        $dbSchoolSalaryLevel = $dbSchoolSalaryLevel->toArray();
        $this->assertModelData($schoolSalaryLevel->toArray(), $dbSchoolSalaryLevel);
    }

    /**
     * @test update
     */
    public function test_update_school_salary_level()
    {
        $schoolSalaryLevel = SchoolSalaryLevel::factory()->create();
        $fakeSchoolSalaryLevel = SchoolSalaryLevel::factory()->make()->toArray();

        $updatedSchoolSalaryLevel = $this->schoolSalaryLevelRepo->update($fakeSchoolSalaryLevel, $schoolSalaryLevel->id);

        $this->assertModelData($fakeSchoolSalaryLevel, $updatedSchoolSalaryLevel->toArray());
        $dbSchoolSalaryLevel = $this->schoolSalaryLevelRepo->find($schoolSalaryLevel->id);
        $this->assertModelData($fakeSchoolSalaryLevel, $dbSchoolSalaryLevel->toArray());
    }

    /**
     * @test delete
     */
    public function test_delete_school_salary_level()
    {
        $schoolSalaryLevel = SchoolSalaryLevel::factory()->create();

        $resp = $this->schoolSalaryLevelRepo->delete($schoolSalaryLevel->id);

        $this->assertTrue($resp);
        $this->assertNull(SchoolSalaryLevel::find($schoolSalaryLevel->id), 'SchoolSalaryLevel should not exist in DB');
    }
}
