<?php

namespace Tests\APIs;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\ApiTestTrait;
use App\Models\CourseSubgroup;

class CourseSubgroupApiTest extends TestCase
{
    use ApiTestTrait, WithoutMiddleware, DatabaseTransactions;

    /**
     * @test
     */
    public function test_create_course_subgroup()
    {
        $courseSubgroup = CourseSubgroup::factory()->make()->toArray();

        $this->response = $this->json(
            'POST',
            '/api/course-subgroups', $courseSubgroup
        );

        $this->assertApiResponse($courseSubgroup);
    }

    /**
     * @test
     */
    public function test_read_course_subgroup()
    {
        $courseSubgroup = CourseSubgroup::factory()->create();

        $this->response = $this->json(
            'GET',
            '/api/course-subgroups/'.$courseSubgroup->id
        );

        $this->assertApiResponse($courseSubgroup->toArray());
    }

    /**
     * @test
     */
    public function test_update_course_subgroup()
    {
        $courseSubgroup = CourseSubgroup::factory()->create();
        $editedCourseSubgroup = CourseSubgroup::factory()->make()->toArray();

        $this->response = $this->json(
            'PUT',
            '/api/course-subgroups/'.$courseSubgroup->id,
            $editedCourseSubgroup
        );

        $this->assertApiResponse($editedCourseSubgroup);
    }

    /**
     * @test
     */
    public function test_delete_course_subgroup()
    {
        $courseSubgroup = CourseSubgroup::factory()->create();

        $this->response = $this->json(
            'DELETE',
             '/api/course-subgroups/'.$courseSubgroup->id
         );

        $this->assertApiSuccess();
        $this->response = $this->json(
            'GET',
            '/api/course-subgroups/'.$courseSubgroup->id
        );

        $this->response->assertStatus(404);
    }
}
