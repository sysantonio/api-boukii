<?php

namespace Tests\Repositories;

use App\Models\CourseDate;
use App\Repositories\CourseDateRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\ApiTestTrait;

class CourseDateRepositoryTest extends TestCase
{
    use ApiTestTrait, DatabaseTransactions;

    protected CourseDateRepository $courseDateRepo;

    public function setUp() : void
    {
        parent::setUp();
        $this->courseDateRepo = app(CourseDateRepository::class);
    }

    /**
     * @test create
     */
    public function test_create_course_date()
    {
        $courseDate = CourseDate::factory()->make()->toArray();

        $createdCourseDate = $this->courseDateRepo->create($courseDate);

        $createdCourseDate = $createdCourseDate->toArray();
        $this->assertArrayHasKey('id', $createdCourseDate);
        $this->assertNotNull($createdCourseDate['id'], 'Created CourseDate must have id specified');
        $this->assertNotNull(CourseDate::find($createdCourseDate['id']), 'CourseDate with given id must be in DB');
        $this->assertModelData($courseDate, $createdCourseDate);
    }

    /**
     * @test read
     */
    public function test_read_course_date()
    {
        $courseDate = CourseDate::factory()->create();

        $dbCourseDate = $this->courseDateRepo->find($courseDate->id);

        $dbCourseDate = $dbCourseDate->toArray();
        $this->assertModelData($courseDate->toArray(), $dbCourseDate);
    }

    /**
     * @test update
     */
    public function test_update_course_date()
    {
        $courseDate = CourseDate::factory()->create();
        $fakeCourseDate = CourseDate::factory()->make()->toArray();

        $updatedCourseDate = $this->courseDateRepo->update($fakeCourseDate, $courseDate->id);

        $this->assertModelData($fakeCourseDate, $updatedCourseDate->toArray());
        $dbCourseDate = $this->courseDateRepo->find($courseDate->id);
        $this->assertModelData($fakeCourseDate, $dbCourseDate->toArray());
    }

    /**
     * @test delete
     */
    public function test_delete_course_date()
    {
        $courseDate = CourseDate::factory()->create();

        $resp = $this->courseDateRepo->delete($courseDate->id);

        $this->assertTrue($resp);
        $this->assertNull(CourseDate::find($courseDate->id), 'CourseDate should not exist in DB');
    }
}
