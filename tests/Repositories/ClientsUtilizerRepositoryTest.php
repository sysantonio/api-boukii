<?php

namespace Tests\Repositories;

use App\Models\ClientsUtilizer;
use App\Repositories\ClientsUtilizerRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\ApiTestTrait;

class ClientsUtilizerRepositoryTest extends TestCase
{
    use ApiTestTrait, DatabaseTransactions;

    protected ClientsUtilizerRepository $clientsUtilizerRepo;

    public function setUp() : void
    {
        parent::setUp();
        $this->clientsUtilizerRepo = app(ClientsUtilizerRepository::class);
    }

    /**
     * @test create
     */
    public function test_create_clients_utilizer()
    {
        $clientsUtilizer = ClientsUtilizer::factory()->make()->toArray();

        $createdClientsUtilizer = $this->clientsUtilizerRepo->create($clientsUtilizer);

        $createdClientsUtilizer = $createdClientsUtilizer->toArray();
        $this->assertArrayHasKey('id', $createdClientsUtilizer);
        $this->assertNotNull($createdClientsUtilizer['id'], 'Created ClientsUtilizer must have id specified');
        $this->assertNotNull(ClientsUtilizer::find($createdClientsUtilizer['id']), 'ClientsUtilizer with given id must be in DB');
        $this->assertModelData($clientsUtilizer, $createdClientsUtilizer);
    }

    /**
     * @test read
     */
    public function test_read_clients_utilizer()
    {
        $clientsUtilizer = ClientsUtilizer::factory()->create();

        $dbClientsUtilizer = $this->clientsUtilizerRepo->find($clientsUtilizer->id);

        $dbClientsUtilizer = $dbClientsUtilizer->toArray();
        $this->assertModelData($clientsUtilizer->toArray(), $dbClientsUtilizer);
    }

    /**
     * @test update
     */
    public function test_update_clients_utilizer()
    {
        $clientsUtilizer = ClientsUtilizer::factory()->create();
        $fakeClientsUtilizer = ClientsUtilizer::factory()->make()->toArray();

        $updatedClientsUtilizer = $this->clientsUtilizerRepo->update($fakeClientsUtilizer, $clientsUtilizer->id);

        $this->assertModelData($fakeClientsUtilizer, $updatedClientsUtilizer->toArray());
        $dbClientsUtilizer = $this->clientsUtilizerRepo->find($clientsUtilizer->id);
        $this->assertModelData($fakeClientsUtilizer, $dbClientsUtilizer->toArray());
    }

    /**
     * @test delete
     */
    public function test_delete_clients_utilizer()
    {
        $clientsUtilizer = ClientsUtilizer::factory()->create();

        $resp = $this->clientsUtilizerRepo->delete($clientsUtilizer->id);

        $this->assertTrue($resp);
        $this->assertNull(ClientsUtilizer::find($clientsUtilizer->id), 'ClientsUtilizer should not exist in DB');
    }
}
