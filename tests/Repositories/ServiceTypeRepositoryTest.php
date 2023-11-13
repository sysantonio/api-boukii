<?php

namespace Tests\Repositories;

use App\Models\ServiceType;
use App\Repositories\ServiceTypeRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\ApiTestTrait;

class ServiceTypeRepositoryTest extends TestCase
{
    use ApiTestTrait, DatabaseTransactions;

    protected ServiceTypeRepository $serviceTypeRepo;

    public function setUp() : void
    {
        parent::setUp();
        $this->serviceTypeRepo = app(ServiceTypeRepository::class);
    }

    /**
     * @test create
     */
    public function test_create_service_type()
    {
        $serviceType = ServiceType::factory()->make()->toArray();

        $createdServiceType = $this->serviceTypeRepo->create($serviceType);

        $createdServiceType = $createdServiceType->toArray();
        $this->assertArrayHasKey('id', $createdServiceType);
        $this->assertNotNull($createdServiceType['id'], 'Created ServiceType must have id specified');
        $this->assertNotNull(ServiceType::find($createdServiceType['id']), 'ServiceType with given id must be in DB');
        $this->assertModelData($serviceType, $createdServiceType);
    }

    /**
     * @test read
     */
    public function test_read_service_type()
    {
        $serviceType = ServiceType::factory()->create();

        $dbServiceType = $this->serviceTypeRepo->find($serviceType->id);

        $dbServiceType = $dbServiceType->toArray();
        $this->assertModelData($serviceType->toArray(), $dbServiceType);
    }

    /**
     * @test update
     */
    public function test_update_service_type()
    {
        $serviceType = ServiceType::factory()->create();
        $fakeServiceType = ServiceType::factory()->make()->toArray();

        $updatedServiceType = $this->serviceTypeRepo->update($fakeServiceType, $serviceType->id);

        $this->assertModelData($fakeServiceType, $updatedServiceType->toArray());
        $dbServiceType = $this->serviceTypeRepo->find($serviceType->id);
        $this->assertModelData($fakeServiceType, $dbServiceType->toArray());
    }

    /**
     * @test delete
     */
    public function test_delete_service_type()
    {
        $serviceType = ServiceType::factory()->create();

        $resp = $this->serviceTypeRepo->delete($serviceType->id);

        $this->assertTrue($resp);
        $this->assertNull(ServiceType::find($serviceType->id), 'ServiceType should not exist in DB');
    }
}
