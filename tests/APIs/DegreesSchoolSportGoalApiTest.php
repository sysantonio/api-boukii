<?php

namespace Tests\APIs;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\ApiTestTrait;
use App\Models\DegreesSchoolSportGoal;

class DegreesSchoolSportGoalApiTest extends TestCase
{
    use ApiTestTrait, WithoutMiddleware, DatabaseTransactions;

    /**
     * @test
     */
    public function test_create_degrees_school_sport_goal()
    {
        $degreesSchoolSportGoal = DegreesSchoolSportGoal::factory()->make()->toArray();

        $this->response = $this->json(
            'POST',
            '/api/degrees-school-sport-goals', $degreesSchoolSportGoal
        );

        $this->assertApiResponse($degreesSchoolSportGoal);
    }

    /**
     * @test
     */
    public function test_read_degrees_school_sport_goal()
    {
        $degreesSchoolSportGoal = DegreesSchoolSportGoal::factory()->create();

        $this->response = $this->json(
            'GET',
            '/api/degrees-school-sport-goals/'.$degreesSchoolSportGoal->id
        );

        $this->assertApiResponse($degreesSchoolSportGoal->toArray());
    }

    /**
     * @test
     */
    public function test_update_degrees_school_sport_goal()
    {
        $degreesSchoolSportGoal = DegreesSchoolSportGoal::factory()->create();
        $editedDegreesSchoolSportGoal = DegreesSchoolSportGoal::factory()->make()->toArray();

        $this->response = $this->json(
            'PUT',
            '/api/degrees-school-sport-goals/'.$degreesSchoolSportGoal->id,
            $editedDegreesSchoolSportGoal
        );

        $this->assertApiResponse($editedDegreesSchoolSportGoal);
    }

    /**
     * @test
     */
    public function test_delete_degrees_school_sport_goal()
    {
        $degreesSchoolSportGoal = DegreesSchoolSportGoal::factory()->create();

        $this->response = $this->json(
            'DELETE',
             '/api/degrees-school-sport-goals/'.$degreesSchoolSportGoal->id
         );

        $this->assertApiSuccess();
        $this->response = $this->json(
            'GET',
            '/api/degrees-school-sport-goals/'.$degreesSchoolSportGoal->id
        );

        $this->response->assertStatus(404);
    }
}
