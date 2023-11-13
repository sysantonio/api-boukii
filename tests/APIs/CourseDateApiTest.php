<?php

namespace Tests\APIs;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\ApiTestTrait;
use App\Models\CourseDate;

class CourseDateApiTest extends TestCase
{
    use ApiTestTrait, WithoutMiddleware, DatabaseTransactions;

    /**
     * @test
     */
    public function test_create_course_date()
    {
        $courseDate = CourseDate::factory()->make()->toArray();

        $this->response = $this->json(
            'POST',
            '/api/course-dates', $courseDate
        );

        $this->assertApiResponse($courseDate);
    }

    /**
     * @test
     */
    public function test_read_course_date()
    {
        $courseDate = CourseDate::factory()->create();

        $this->response = $this->json(
            'GET',
            '/api/course-dates/'.$courseDate->id
        );

        $this->assertApiResponse($courseDate->toArray());
    }

    /**
     * @test
     */
    public function test_update_course_date()
    {
        $courseDate = CourseDate::factory()->create();
        $editedCourseDate = CourseDate::factory()->make()->toArray();

        $this->response = $this->json(
            'PUT',
            '/api/course-dates/'.$courseDate->id,
            $editedCourseDate
        );

        $this->assertApiResponse($editedCourseDate);
    }

    /**
     * @test
     */
    public function test_delete_course_date()
    {
        $courseDate = CourseDate::factory()->create();

        $this->response = $this->json(
            'DELETE',
             '/api/course-dates/'.$courseDate->id
         );

        $this->assertApiSuccess();
        $this->response = $this->json(
            'GET',
            '/api/course-dates/'.$courseDate->id
        );

        $this->response->assertStatus(404);
    }
}
