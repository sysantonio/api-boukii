<?php

namespace Tests\Repositories;

use App\Models\ClientSport;
use App\Repositories\ClientSportRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\ApiTestTrait;

class ClientSportRepositoryTest extends TestCase
{
    use ApiTestTrait, DatabaseTransactions;

    protected ClientSportRepository $clientSportRepo;

    public function setUp() : void
    {
        parent::setUp();
        $this->clientSportRepo = app(ClientSportRepository::class);
    }

    /**
     * @test create
     */
    public function test_create_client_sport()
    {
        $clientSport = ClientSport::factory()->make()->toArray();

        $createdClientSport = $this->clientSportRepo->create($clientSport);

        $createdClientSport = $createdClientSport->toArray();
        $this->assertArrayHasKey('id', $createdClientSport);
        $this->assertNotNull($createdClientSport['id'], 'Created ClientSport must have id specified');
        $this->assertNotNull(ClientSport::find($createdClientSport['id']), 'ClientSport with given id must be in DB');
        $this->assertModelData($clientSport, $createdClientSport);
    }

    /**
     * @test read
     */
    public function test_read_client_sport()
    {
        $clientSport = ClientSport::factory()->create();

        $dbClientSport = $this->clientSportRepo->find($clientSport->id);

        $dbClientSport = $dbClientSport->toArray();
        $this->assertModelData($clientSport->toArray(), $dbClientSport);
    }

    /**
     * @test update
     */
    public function test_update_client_sport()
    {
        $clientSport = ClientSport::factory()->create();
        $fakeClientSport = ClientSport::factory()->make()->toArray();

        $updatedClientSport = $this->clientSportRepo->update($fakeClientSport, $clientSport->id);

        $this->assertModelData($fakeClientSport, $updatedClientSport->toArray());
        $dbClientSport = $this->clientSportRepo->find($clientSport->id);
        $this->assertModelData($fakeClientSport, $dbClientSport->toArray());
    }

    /**
     * @test delete
     */
    public function test_delete_client_sport()
    {
        $clientSport = ClientSport::factory()->create();

        $resp = $this->clientSportRepo->delete($clientSport->id);

        $this->assertTrue($resp);
        $this->assertNull(ClientSport::find($clientSport->id), 'ClientSport should not exist in DB');
    }
}
