<?php

namespace Tests\Repositories;

use App\Models\CourseGroup;
use App\Repositories\CourseGroupRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\ApiTestTrait;

class CourseGroupRepositoryTest extends TestCase
{
    use ApiTestTrait, DatabaseTransactions;

    protected CourseGroupRepository $courseGroupRepo;

    public function setUp() : void
    {
        parent::setUp();
        $this->courseGroupRepo = app(CourseGroupRepository::class);
    }

    /**
     * @test create
     */
    public function test_create_course_group()
    {
        $courseGroup = CourseGroup::factory()->make()->toArray();

        $createdCourseGroup = $this->courseGroupRepo->create($courseGroup);

        $createdCourseGroup = $createdCourseGroup->toArray();
        $this->assertArrayHasKey('id', $createdCourseGroup);
        $this->assertNotNull($createdCourseGroup['id'], 'Created CourseGroup must have id specified');
        $this->assertNotNull(CourseGroup::find($createdCourseGroup['id']), 'CourseGroup with given id must be in DB');
        $this->assertModelData($courseGroup, $createdCourseGroup);
    }

    /**
     * @test read
     */
    public function test_read_course_group()
    {
        $courseGroup = CourseGroup::factory()->create();

        $dbCourseGroup = $this->courseGroupRepo->find($courseGroup->id);

        $dbCourseGroup = $dbCourseGroup->toArray();
        $this->assertModelData($courseGroup->toArray(), $dbCourseGroup);
    }

    /**
     * @test update
     */
    public function test_update_course_group()
    {
        $courseGroup = CourseGroup::factory()->create();
        $fakeCourseGroup = CourseGroup::factory()->make()->toArray();

        $updatedCourseGroup = $this->courseGroupRepo->update($fakeCourseGroup, $courseGroup->id);

        $this->assertModelData($fakeCourseGroup, $updatedCourseGroup->toArray());
        $dbCourseGroup = $this->courseGroupRepo->find($courseGroup->id);
        $this->assertModelData($fakeCourseGroup, $dbCourseGroup->toArray());
    }

    /**
     * @test delete
     */
    public function test_delete_course_group()
    {
        $courseGroup = CourseGroup::factory()->create();

        $resp = $this->courseGroupRepo->delete($courseGroup->id);

        $this->assertTrue($resp);
        $this->assertNull(CourseGroup::find($courseGroup->id), 'CourseGroup should not exist in DB');
    }
}
