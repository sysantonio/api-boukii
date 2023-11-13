<?php

namespace Tests\APIs;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\ApiTestTrait;
use App\Models\ClientsUtilizer;

class ClientsUtilizerApiTest extends TestCase
{
    use ApiTestTrait, WithoutMiddleware, DatabaseTransactions;

    /**
     * @test
     */
    public function test_create_clients_utilizer()
    {
        $clientsUtilizer = ClientsUtilizer::factory()->make()->toArray();

        $this->response = $this->json(
            'POST',
            '/api/clients-utilizers', $clientsUtilizer
        );

        $this->assertApiResponse($clientsUtilizer);
    }

    /**
     * @test
     */
    public function test_read_clients_utilizer()
    {
        $clientsUtilizer = ClientsUtilizer::factory()->create();

        $this->response = $this->json(
            'GET',
            '/api/clients-utilizers/'.$clientsUtilizer->id
        );

        $this->assertApiResponse($clientsUtilizer->toArray());
    }

    /**
     * @test
     */
    public function test_update_clients_utilizer()
    {
        $clientsUtilizer = ClientsUtilizer::factory()->create();
        $editedClientsUtilizer = ClientsUtilizer::factory()->make()->toArray();

        $this->response = $this->json(
            'PUT',
            '/api/clients-utilizers/'.$clientsUtilizer->id,
            $editedClientsUtilizer
        );

        $this->assertApiResponse($editedClientsUtilizer);
    }

    /**
     * @test
     */
    public function test_delete_clients_utilizer()
    {
        $clientsUtilizer = ClientsUtilizer::factory()->create();

        $this->response = $this->json(
            'DELETE',
             '/api/clients-utilizers/'.$clientsUtilizer->id
         );

        $this->assertApiSuccess();
        $this->response = $this->json(
            'GET',
            '/api/clients-utilizers/'.$clientsUtilizer->id
        );

        $this->response->assertStatus(404);
    }
}
