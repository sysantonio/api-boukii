<?php

namespace Tests\APIs;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\ApiTestTrait;
use App\Models\ClientsSchool;

class ClientsSchoolApiTest extends TestCase
{
    use ApiTestTrait, WithoutMiddleware, DatabaseTransactions;

    /**
     * @test
     */
    public function test_create_clients_school()
    {
        $clientsSchool = ClientsSchool::factory()->make()->toArray();

        $this->response = $this->json(
            'POST',
            '/api/clients-schools', $clientsSchool
        );

        $this->assertApiResponse($clientsSchool);
    }

    /**
     * @test
     */
    public function test_read_clients_school()
    {
        $clientsSchool = ClientsSchool::factory()->create();

        $this->response = $this->json(
            'GET',
            '/api/clients-schools/'.$clientsSchool->id
        );

        $this->assertApiResponse($clientsSchool->toArray());
    }

    /**
     * @test
     */
    public function test_update_clients_school()
    {
        $clientsSchool = ClientsSchool::factory()->create();
        $editedClientsSchool = ClientsSchool::factory()->make()->toArray();

        $this->response = $this->json(
            'PUT',
            '/api/clients-schools/'.$clientsSchool->id,
            $editedClientsSchool
        );

        $this->assertApiResponse($editedClientsSchool);
    }

    /**
     * @test
     */
    public function test_delete_clients_school()
    {
        $clientsSchool = ClientsSchool::factory()->create();

        $this->response = $this->json(
            'DELETE',
             '/api/clients-schools/'.$clientsSchool->id
         );

        $this->assertApiSuccess();
        $this->response = $this->json(
            'GET',
            '/api/clients-schools/'.$clientsSchool->id
        );

        $this->response->assertStatus(404);
    }
}
