<?php

namespace Tests\Repositories;

use App\Models\CourseExtra;
use App\Repositories\CourseExtraRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\ApiTestTrait;

class CourseExtraRepositoryTest extends TestCase
{
    use ApiTestTrait, DatabaseTransactions;

    protected CourseExtraRepository $courseExtraRepo;

    public function setUp() : void
    {
        parent::setUp();
        $this->courseExtraRepo = app(CourseExtraRepository::class);
    }

    /**
     * @test create
     */
    public function test_create_course_extra()
    {
        $courseExtra = CourseExtra::factory()->make()->toArray();

        $createdCourseExtra = $this->courseExtraRepo->create($courseExtra);

        $createdCourseExtra = $createdCourseExtra->toArray();
        $this->assertArrayHasKey('id', $createdCourseExtra);
        $this->assertNotNull($createdCourseExtra['id'], 'Created CourseExtra must have id specified');
        $this->assertNotNull(CourseExtra::find($createdCourseExtra['id']), 'CourseExtra with given id must be in DB');
        $this->assertModelData($courseExtra, $createdCourseExtra);
    }

    /**
     * @test read
     */
    public function test_read_course_extra()
    {
        $courseExtra = CourseExtra::factory()->create();

        $dbCourseExtra = $this->courseExtraRepo->find($courseExtra->id);

        $dbCourseExtra = $dbCourseExtra->toArray();
        $this->assertModelData($courseExtra->toArray(), $dbCourseExtra);
    }

    /**
     * @test update
     */
    public function test_update_course_extra()
    {
        $courseExtra = CourseExtra::factory()->create();
        $fakeCourseExtra = CourseExtra::factory()->make()->toArray();

        $updatedCourseExtra = $this->courseExtraRepo->update($fakeCourseExtra, $courseExtra->id);

        $this->assertModelData($fakeCourseExtra, $updatedCourseExtra->toArray());
        $dbCourseExtra = $this->courseExtraRepo->find($courseExtra->id);
        $this->assertModelData($fakeCourseExtra, $dbCourseExtra->toArray());
    }

    /**
     * @test delete
     */
    public function test_delete_course_extra()
    {
        $courseExtra = CourseExtra::factory()->create();

        $resp = $this->courseExtraRepo->delete($courseExtra->id);

        $this->assertTrue($resp);
        $this->assertNull(CourseExtra::find($courseExtra->id), 'CourseExtra should not exist in DB');
    }
}
