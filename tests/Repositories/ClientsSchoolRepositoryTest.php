<?php

namespace Tests\Repositories;

use App\Models\ClientsSchool;
use App\Repositories\ClientsSchoolRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\ApiTestTrait;

class ClientsSchoolRepositoryTest extends TestCase
{
    use ApiTestTrait, DatabaseTransactions;

    protected ClientsSchoolRepository $clientsSchoolRepo;

    public function setUp() : void
    {
        parent::setUp();
        $this->clientsSchoolRepo = app(ClientsSchoolRepository::class);
    }

    /**
     * @test create
     */
    public function test_create_clients_school()
    {
        $clientsSchool = ClientsSchool::factory()->make()->toArray();

        $createdClientsSchool = $this->clientsSchoolRepo->create($clientsSchool);

        $createdClientsSchool = $createdClientsSchool->toArray();
        $this->assertArrayHasKey('id', $createdClientsSchool);
        $this->assertNotNull($createdClientsSchool['id'], 'Created ClientsSchool must have id specified');
        $this->assertNotNull(ClientsSchool::find($createdClientsSchool['id']), 'ClientsSchool with given id must be in DB');
        $this->assertModelData($clientsSchool, $createdClientsSchool);
    }

    /**
     * @test read
     */
    public function test_read_clients_school()
    {
        $clientsSchool = ClientsSchool::factory()->create();

        $dbClientsSchool = $this->clientsSchoolRepo->find($clientsSchool->id);

        $dbClientsSchool = $dbClientsSchool->toArray();
        $this->assertModelData($clientsSchool->toArray(), $dbClientsSchool);
    }

    /**
     * @test update
     */
    public function test_update_clients_school()
    {
        $clientsSchool = ClientsSchool::factory()->create();
        $fakeClientsSchool = ClientsSchool::factory()->make()->toArray();

        $updatedClientsSchool = $this->clientsSchoolRepo->update($fakeClientsSchool, $clientsSchool->id);

        $this->assertModelData($fakeClientsSchool, $updatedClientsSchool->toArray());
        $dbClientsSchool = $this->clientsSchoolRepo->find($clientsSchool->id);
        $this->assertModelData($fakeClientsSchool, $dbClientsSchool->toArray());
    }

    /**
     * @test delete
     */
    public function test_delete_clients_school()
    {
        $clientsSchool = ClientsSchool::factory()->create();

        $resp = $this->clientsSchoolRepo->delete($clientsSchool->id);

        $this->assertTrue($resp);
        $this->assertNull(ClientsSchool::find($clientsSchool->id), 'ClientsSchool should not exist in DB');
    }
}
