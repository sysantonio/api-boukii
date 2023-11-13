<?php

namespace Tests\Repositories;

use App\Models\ClientObservation;
use App\Repositories\ClientObservationRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\ApiTestTrait;

class ClientObservationRepositoryTest extends TestCase
{
    use ApiTestTrait, DatabaseTransactions;

    protected ClientObservationRepository $clientObservationRepo;

    public function setUp() : void
    {
        parent::setUp();
        $this->clientObservationRepo = app(ClientObservationRepository::class);
    }

    /**
     * @test create
     */
    public function test_create_client_observation()
    {
        $clientObservation = ClientObservation::factory()->make()->toArray();

        $createdClientObservation = $this->clientObservationRepo->create($clientObservation);

        $createdClientObservation = $createdClientObservation->toArray();
        $this->assertArrayHasKey('id', $createdClientObservation);
        $this->assertNotNull($createdClientObservation['id'], 'Created ClientObservation must have id specified');
        $this->assertNotNull(ClientObservation::find($createdClientObservation['id']), 'ClientObservation with given id must be in DB');
        $this->assertModelData($clientObservation, $createdClientObservation);
    }

    /**
     * @test read
     */
    public function test_read_client_observation()
    {
        $clientObservation = ClientObservation::factory()->create();

        $dbClientObservation = $this->clientObservationRepo->find($clientObservation->id);

        $dbClientObservation = $dbClientObservation->toArray();
        $this->assertModelData($clientObservation->toArray(), $dbClientObservation);
    }

    /**
     * @test update
     */
    public function test_update_client_observation()
    {
        $clientObservation = ClientObservation::factory()->create();
        $fakeClientObservation = ClientObservation::factory()->make()->toArray();

        $updatedClientObservation = $this->clientObservationRepo->update($fakeClientObservation, $clientObservation->id);

        $this->assertModelData($fakeClientObservation, $updatedClientObservation->toArray());
        $dbClientObservation = $this->clientObservationRepo->find($clientObservation->id);
        $this->assertModelData($fakeClientObservation, $dbClientObservation->toArray());
    }

    /**
     * @test delete
     */
    public function test_delete_client_observation()
    {
        $clientObservation = ClientObservation::factory()->create();

        $resp = $this->clientObservationRepo->delete($clientObservation->id);

        $this->assertTrue($resp);
        $this->assertNull(ClientObservation::find($clientObservation->id), 'ClientObservation should not exist in DB');
    }
}
