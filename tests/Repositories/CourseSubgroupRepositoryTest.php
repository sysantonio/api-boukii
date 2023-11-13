<?php

namespace Tests\Repositories;

use App\Models\CourseSubgroup;
use App\Repositories\CourseSubgroupRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\ApiTestTrait;

class CourseSubgroupRepositoryTest extends TestCase
{
    use ApiTestTrait, DatabaseTransactions;

    protected CourseSubgroupRepository $courseSubgroupRepo;

    public function setUp() : void
    {
        parent::setUp();
        $this->courseSubgroupRepo = app(CourseSubgroupRepository::class);
    }

    /**
     * @test create
     */
    public function test_create_course_subgroup()
    {
        $courseSubgroup = CourseSubgroup::factory()->make()->toArray();

        $createdCourseSubgroup = $this->courseSubgroupRepo->create($courseSubgroup);

        $createdCourseSubgroup = $createdCourseSubgroup->toArray();
        $this->assertArrayHasKey('id', $createdCourseSubgroup);
        $this->assertNotNull($createdCourseSubgroup['id'], 'Created CourseSubgroup must have id specified');
        $this->assertNotNull(CourseSubgroup::find($createdCourseSubgroup['id']), 'CourseSubgroup with given id must be in DB');
        $this->assertModelData($courseSubgroup, $createdCourseSubgroup);
    }

    /**
     * @test read
     */
    public function test_read_course_subgroup()
    {
        $courseSubgroup = CourseSubgroup::factory()->create();

        $dbCourseSubgroup = $this->courseSubgroupRepo->find($courseSubgroup->id);

        $dbCourseSubgroup = $dbCourseSubgroup->toArray();
        $this->assertModelData($courseSubgroup->toArray(), $dbCourseSubgroup);
    }

    /**
     * @test update
     */
    public function test_update_course_subgroup()
    {
        $courseSubgroup = CourseSubgroup::factory()->create();
        $fakeCourseSubgroup = CourseSubgroup::factory()->make()->toArray();

        $updatedCourseSubgroup = $this->courseSubgroupRepo->update($fakeCourseSubgroup, $courseSubgroup->id);

        $this->assertModelData($fakeCourseSubgroup, $updatedCourseSubgroup->toArray());
        $dbCourseSubgroup = $this->courseSubgroupRepo->find($courseSubgroup->id);
        $this->assertModelData($fakeCourseSubgroup, $dbCourseSubgroup->toArray());
    }

    /**
     * @test delete
     */
    public function test_delete_course_subgroup()
    {
        $courseSubgroup = CourseSubgroup::factory()->create();

        $resp = $this->courseSubgroupRepo->delete($courseSubgroup->id);

        $this->assertTrue($resp);
        $this->assertNull(CourseSubgroup::find($courseSubgroup->id), 'CourseSubgroup should not exist in DB');
    }
}
