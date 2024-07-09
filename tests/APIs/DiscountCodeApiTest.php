<?php

namespace Tests\APIs;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\ApiTestTrait;
use App\Models\DiscountCode;

class DiscountCodeApiTest extends TestCase
{
    use ApiTestTrait, WithoutMiddleware, DatabaseTransactions;

    /**
     * @test
     */
    public function test_create_discount_code()
    {
        $discountCode = DiscountCode::factory()->make()->toArray();

        $this->response = $this->json(
            'POST',
            '/api/discount-codes', $discountCode
        );

        $this->assertApiResponse($discountCode);
    }

    /**
     * @test
     */
    public function test_read_discount_code()
    {
        $discountCode = DiscountCode::factory()->create();

        $this->response = $this->json(
            'GET',
            '/api/discount-codes/'.$discountCode->id
        );

        $this->assertApiResponse($discountCode->toArray());
    }

    /**
     * @test
     */
    public function test_update_discount_code()
    {
        $discountCode = DiscountCode::factory()->create();
        $editedDiscountCode = DiscountCode::factory()->make()->toArray();

        $this->response = $this->json(
            'PUT',
            '/api/discount-codes/'.$discountCode->id,
            $editedDiscountCode
        );

        $this->assertApiResponse($editedDiscountCode);
    }

    /**
     * @test
     */
    public function test_delete_discount_code()
    {
        $discountCode = DiscountCode::factory()->create();

        $this->response = $this->json(
            'DELETE',
             '/api/discount-codes/'.$discountCode->id
         );

        $this->assertApiSuccess();
        $this->response = $this->json(
            'GET',
            '/api/discount-codes/'.$discountCode->id
        );

        $this->response->assertStatus(404);
    }
}
