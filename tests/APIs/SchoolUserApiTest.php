<?php

namespace Tests\APIs;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\ApiTestTrait;
use App\Models\SchoolUser;

class SchoolUserApiTest extends TestCase
{
    use ApiTestTrait, WithoutMiddleware, DatabaseTransactions;

    /**
     * @test
     */
    public function test_create_school_user()
    {
        $schoolUser = SchoolUser::factory()->make()->toArray();

        $this->response = $this->json(
            'POST',
            '/api/school-users', $schoolUser
        );

        $this->assertApiResponse($schoolUser);
    }

    /**
     * @test
     */
    public function test_read_school_user()
    {
        $schoolUser = SchoolUser::factory()->create();

        $this->response = $this->json(
            'GET',
            '/api/school-users/'.$schoolUser->id
        );

        $this->assertApiResponse($schoolUser->toArray());
    }

    /**
     * @test
     */
    public function test_update_school_user()
    {
        $schoolUser = SchoolUser::factory()->create();
        $editedSchoolUser = SchoolUser::factory()->make()->toArray();

        $this->response = $this->json(
            'PUT',
            '/api/school-users/'.$schoolUser->id,
            $editedSchoolUser
        );

        $this->assertApiResponse($editedSchoolUser);
    }

    /**
     * @test
     */
    public function test_delete_school_user()
    {
        $schoolUser = SchoolUser::factory()->create();

        $this->response = $this->json(
            'DELETE',
             '/api/school-users/'.$schoolUser->id
         );

        $this->assertApiSuccess();
        $this->response = $this->json(
            'GET',
            '/api/school-users/'.$schoolUser->id
        );

        $this->response->assertStatus(404);
    }
}
