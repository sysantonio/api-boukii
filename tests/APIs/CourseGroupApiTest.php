<?php

namespace Tests\APIs;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\ApiTestTrait;
use App\Models\CourseGroup;

class CourseGroupApiTest extends TestCase
{
    use ApiTestTrait, WithoutMiddleware, DatabaseTransactions;

    /**
     * @test
     */
    public function test_create_course_group()
    {
        $courseGroup = CourseGroup::factory()->make()->toArray();

        $this->response = $this->json(
            'POST',
            '/api/course-groups', $courseGroup
        );

        $this->assertApiResponse($courseGroup);
    }

    /**
     * @test
     */
    public function test_read_course_group()
    {
        $courseGroup = CourseGroup::factory()->create();

        $this->response = $this->json(
            'GET',
            '/api/course-groups/'.$courseGroup->id
        );

        $this->assertApiResponse($courseGroup->toArray());
    }

    /**
     * @test
     */
    public function test_update_course_group()
    {
        $courseGroup = CourseGroup::factory()->create();
        $editedCourseGroup = CourseGroup::factory()->make()->toArray();

        $this->response = $this->json(
            'PUT',
            '/api/course-groups/'.$courseGroup->id,
            $editedCourseGroup
        );

        $this->assertApiResponse($editedCourseGroup);
    }

    /**
     * @test
     */
    public function test_delete_course_group()
    {
        $courseGroup = CourseGroup::factory()->create();

        $this->response = $this->json(
            'DELETE',
             '/api/course-groups/'.$courseGroup->id
         );

        $this->assertApiSuccess();
        $this->response = $this->json(
            'GET',
            '/api/course-groups/'.$courseGroup->id
        );

        $this->response->assertStatus(404);
    }
}
