<?php

namespace Tests\APIs;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\ApiTestTrait;
use App\Models\SchoolSalaryLevel;

class SchoolSalaryLevelApiTest extends TestCase
{
    use ApiTestTrait, WithoutMiddleware, DatabaseTransactions;

    /**
     * @test
     */
    public function test_create_school_salary_level()
    {
        $schoolSalaryLevel = SchoolSalaryLevel::factory()->make()->toArray();

        $this->response = $this->json(
            'POST',
            '/api/school-salary-levels', $schoolSalaryLevel
        );

        $this->assertApiResponse($schoolSalaryLevel);
    }

    /**
     * @test
     */
    public function test_read_school_salary_level()
    {
        $schoolSalaryLevel = SchoolSalaryLevel::factory()->create();

        $this->response = $this->json(
            'GET',
            '/api/school-salary-levels/'.$schoolSalaryLevel->id
        );

        $this->assertApiResponse($schoolSalaryLevel->toArray());
    }

    /**
     * @test
     */
    public function test_update_school_salary_level()
    {
        $schoolSalaryLevel = SchoolSalaryLevel::factory()->create();
        $editedSchoolSalaryLevel = SchoolSalaryLevel::factory()->make()->toArray();

        $this->response = $this->json(
            'PUT',
            '/api/school-salary-levels/'.$schoolSalaryLevel->id,
            $editedSchoolSalaryLevel
        );

        $this->assertApiResponse($editedSchoolSalaryLevel);
    }

    /**
     * @test
     */
    public function test_delete_school_salary_level()
    {
        $schoolSalaryLevel = SchoolSalaryLevel::factory()->create();

        $this->response = $this->json(
            'DELETE',
             '/api/school-salary-levels/'.$schoolSalaryLevel->id
         );

        $this->assertApiSuccess();
        $this->response = $this->json(
            'GET',
            '/api/school-salary-levels/'.$schoolSalaryLevel->id
        );

        $this->response->assertStatus(404);
    }
}
