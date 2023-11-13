<?php

namespace Tests\APIs;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\ApiTestTrait;
use App\Models\CourseExtra;

class CourseExtraApiTest extends TestCase
{
    use ApiTestTrait, WithoutMiddleware, DatabaseTransactions;

    /**
     * @test
     */
    public function test_create_course_extra()
    {
        $courseExtra = CourseExtra::factory()->make()->toArray();

        $this->response = $this->json(
            'POST',
            '/api/course-extras', $courseExtra
        );

        $this->assertApiResponse($courseExtra);
    }

    /**
     * @test
     */
    public function test_read_course_extra()
    {
        $courseExtra = CourseExtra::factory()->create();

        $this->response = $this->json(
            'GET',
            '/api/course-extras/'.$courseExtra->id
        );

        $this->assertApiResponse($courseExtra->toArray());
    }

    /**
     * @test
     */
    public function test_update_course_extra()
    {
        $courseExtra = CourseExtra::factory()->create();
        $editedCourseExtra = CourseExtra::factory()->make()->toArray();

        $this->response = $this->json(
            'PUT',
            '/api/course-extras/'.$courseExtra->id,
            $editedCourseExtra
        );

        $this->assertApiResponse($editedCourseExtra);
    }

    /**
     * @test
     */
    public function test_delete_course_extra()
    {
        $courseExtra = CourseExtra::factory()->create();

        $this->response = $this->json(
            'DELETE',
             '/api/course-extras/'.$courseExtra->id
         );

        $this->assertApiSuccess();
        $this->response = $this->json(
            'GET',
            '/api/course-extras/'.$courseExtra->id
        );

        $this->response->assertStatus(404);
    }
}
