<?php

namespace Tests\APIs;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\ApiTestTrait;
use App\Models\ClientObservation;

class ClientObservationApiTest extends TestCase
{
    use ApiTestTrait, WithoutMiddleware, DatabaseTransactions;

    /**
     * @test
     */
    public function test_create_client_observation()
    {
        $clientObservation = ClientObservation::factory()->make()->toArray();

        $this->response = $this->json(
            'POST',
            '/api/client-observations', $clientObservation
        );

        $this->assertApiResponse($clientObservation);
    }

    /**
     * @test
     */
    public function test_read_client_observation()
    {
        $clientObservation = ClientObservation::factory()->create();

        $this->response = $this->json(
            'GET',
            '/api/client-observations/'.$clientObservation->id
        );

        $this->assertApiResponse($clientObservation->toArray());
    }

    /**
     * @test
     */
    public function test_update_client_observation()
    {
        $clientObservation = ClientObservation::factory()->create();
        $editedClientObservation = ClientObservation::factory()->make()->toArray();

        $this->response = $this->json(
            'PUT',
            '/api/client-observations/'.$clientObservation->id,
            $editedClientObservation
        );

        $this->assertApiResponse($editedClientObservation);
    }

    /**
     * @test
     */
    public function test_delete_client_observation()
    {
        $clientObservation = ClientObservation::factory()->create();

        $this->response = $this->json(
            'DELETE',
             '/api/client-observations/'.$clientObservation->id
         );

        $this->assertApiSuccess();
        $this->response = $this->json(
            'GET',
            '/api/client-observations/'.$clientObservation->id
        );

        $this->response->assertStatus(404);
    }
}
