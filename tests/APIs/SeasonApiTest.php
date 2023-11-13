<?php

namespace Tests\APIs;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\ApiTestTrait;
use App\Models\Season;

class SeasonApiTest extends TestCase
{
    use ApiTestTrait, WithoutMiddleware, DatabaseTransactions;

    /**
     * @test
     */
    public function test_create_season()
    {
        $season = Season::factory()->make()->toArray();

        $this->response = $this->json(
            'POST',
            '/api/seasons', $season
        );

        $this->assertApiResponse($season);
    }

    /**
     * @test
     */
    public function test_read_season()
    {
        $season = Season::factory()->create();

        $this->response = $this->json(
            'GET',
            '/api/seasons/'.$season->id
        );

        $this->assertApiResponse($season->toArray());
    }

    /**
     * @test
     */
    public function test_update_season()
    {
        $season = Season::factory()->create();
        $editedSeason = Season::factory()->make()->toArray();

        $this->response = $this->json(
            'PUT',
            '/api/seasons/'.$season->id,
            $editedSeason
        );

        $this->assertApiResponse($editedSeason);
    }

    /**
     * @test
     */
    public function test_delete_season()
    {
        $season = Season::factory()->create();

        $this->response = $this->json(
            'DELETE',
             '/api/seasons/'.$season->id
         );

        $this->assertApiSuccess();
        $this->response = $this->json(
            'GET',
            '/api/seasons/'.$season->id
        );

        $this->response->assertStatus(404);
    }
}
