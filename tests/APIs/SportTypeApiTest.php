<?php

namespace Tests\APIs;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\ApiTestTrait;
use App\Models\SportType;

class SportTypeApiTest extends TestCase
{
    use ApiTestTrait, WithoutMiddleware, DatabaseTransactions;

    /**
     * @test
     */
    public function test_create_sport_type()
    {
        $sportType = SportType::factory()->make()->toArray();

        $this->response = $this->json(
            'POST',
            '/api/sport-types', $sportType
        );

        $this->assertApiResponse($sportType);
    }

    /**
     * @test
     */
    public function test_read_sport_type()
    {
        $sportType = SportType::factory()->create();

        $this->response = $this->json(
            'GET',
            '/api/sport-types/'.$sportType->id
        );

        $this->assertApiResponse($sportType->toArray());
    }

    /**
     * @test
     */
    public function test_update_sport_type()
    {
        $sportType = SportType::factory()->create();
        $editedSportType = SportType::factory()->make()->toArray();

        $this->response = $this->json(
            'PUT',
            '/api/sport-types/'.$sportType->id,
            $editedSportType
        );

        $this->assertApiResponse($editedSportType);
    }

    /**
     * @test
     */
    public function test_delete_sport_type()
    {
        $sportType = SportType::factory()->create();

        $this->response = $this->json(
            'DELETE',
             '/api/sport-types/'.$sportType->id
         );

        $this->assertApiSuccess();
        $this->response = $this->json(
            'GET',
            '/api/sport-types/'.$sportType->id
        );

        $this->response->assertStatus(404);
    }
}
