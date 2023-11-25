<?php

namespace Tests\APIs;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\ApiTestTrait;
use App\Models\ClientSport;

class ClientSportApiTest extends TestCase
{
    use ApiTestTrait, WithoutMiddleware, DatabaseTransactions;

    /**
     * @test
     */
    public function test_create_client_sport()
    {
        $clientSport = ClientSport::factory()->make()->toArray();

        $this->response = $this->json(
            'POST',
            '/api/client-sports', $clientSport
        );

        $this->assertApiResponse($clientSport);
    }

    /**
     * @test
     */
    public function test_read_client_sport()
    {
        $clientSport = ClientSport::factory()->create();

        $this->response = $this->json(
            'GET',
            '/api/client-sports/'.$clientSport->id
        );

        $this->assertApiResponse($clientSport->toArray());
    }

    /**
     * @test
     */
    public function test_update_client_sport()
    {
        $clientSport = ClientSport::factory()->create();
        $editedClientSport = ClientSport::factory()->make()->toArray();

        $this->response = $this->json(
            'PUT',
            '/api/client-sports/'.$clientSport->id,
            $editedClientSport
        );

        $this->assertApiResponse($editedClientSport);
    }

    /**
     * @test
     */
    public function test_delete_client_sport()
    {
        $clientSport = ClientSport::factory()->create();

        $this->response = $this->json(
            'DELETE',
             '/api/client-sports/'.$clientSport->id
         );

        $this->assertApiSuccess();
        $this->response = $this->json(
            'GET',
            '/api/client-sports/'.$clientSport->id
        );

        $this->response->assertStatus(404);
    }
}
