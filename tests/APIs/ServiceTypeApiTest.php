<?php

namespace Tests\APIs;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\ApiTestTrait;
use App\Models\ServiceType;

class ServiceTypeApiTest extends TestCase
{
    use ApiTestTrait, WithoutMiddleware, DatabaseTransactions;

    /**
     * @test
     */
    public function test_create_service_type()
    {
        $serviceType = ServiceType::factory()->make()->toArray();

        $this->response = $this->json(
            'POST',
            '/api/service-types', $serviceType
        );

        $this->assertApiResponse($serviceType);
    }

    /**
     * @test
     */
    public function test_read_service_type()
    {
        $serviceType = ServiceType::factory()->create();

        $this->response = $this->json(
            'GET',
            '/api/service-types/'.$serviceType->id
        );

        $this->assertApiResponse($serviceType->toArray());
    }

    /**
     * @test
     */
    public function test_update_service_type()
    {
        $serviceType = ServiceType::factory()->create();
        $editedServiceType = ServiceType::factory()->make()->toArray();

        $this->response = $this->json(
            'PUT',
            '/api/service-types/'.$serviceType->id,
            $editedServiceType
        );

        $this->assertApiResponse($editedServiceType);
    }

    /**
     * @test
     */
    public function test_delete_service_type()
    {
        $serviceType = ServiceType::factory()->create();

        $this->response = $this->json(
            'DELETE',
             '/api/service-types/'.$serviceType->id
         );

        $this->assertApiSuccess();
        $this->response = $this->json(
            'GET',
            '/api/service-types/'.$serviceType->id
        );

        $this->response->assertStatus(404);
    }
}
