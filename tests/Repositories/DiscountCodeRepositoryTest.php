<?php

namespace Tests\Repositories;

use App\Models\DiscountCode;
use App\Repositories\DiscountCodeRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\ApiTestTrait;

class DiscountCodeRepositoryTest extends TestCase
{
    use ApiTestTrait, DatabaseTransactions;

    protected DiscountCodeRepository $discountCodeRepo;

    public function setUp() : void
    {
        parent::setUp();
        $this->discountCodeRepo = app(DiscountCodeRepository::class);
    }

    /**
     * @test create
     */
    public function test_create_discount_code()
    {
        $discountCode = DiscountCode::factory()->make()->toArray();

        $createdDiscountCode = $this->discountCodeRepo->create($discountCode);

        $createdDiscountCode = $createdDiscountCode->toArray();
        $this->assertArrayHasKey('id', $createdDiscountCode);
        $this->assertNotNull($createdDiscountCode['id'], 'Created DiscountCode must have id specified');
        $this->assertNotNull(DiscountCode::find($createdDiscountCode['id']), 'DiscountCode with given id must be in DB');
        $this->assertModelData($discountCode, $createdDiscountCode);
    }

    /**
     * @test read
     */
    public function test_read_discount_code()
    {
        $discountCode = DiscountCode::factory()->create();

        $dbDiscountCode = $this->discountCodeRepo->find($discountCode->id);

        $dbDiscountCode = $dbDiscountCode->toArray();
        $this->assertModelData($discountCode->toArray(), $dbDiscountCode);
    }

    /**
     * @test update
     */
    public function test_update_discount_code()
    {
        $discountCode = DiscountCode::factory()->create();
        $fakeDiscountCode = DiscountCode::factory()->make()->toArray();

        $updatedDiscountCode = $this->discountCodeRepo->update($fakeDiscountCode, $discountCode->id);

        $this->assertModelData($fakeDiscountCode, $updatedDiscountCode->toArray());
        $dbDiscountCode = $this->discountCodeRepo->find($discountCode->id);
        $this->assertModelData($fakeDiscountCode, $dbDiscountCode->toArray());
    }

    /**
     * @test delete
     */
    public function test_delete_discount_code()
    {
        $discountCode = DiscountCode::factory()->create();

        $resp = $this->discountCodeRepo->delete($discountCode->id);

        $this->assertTrue($resp);
        $this->assertNull(DiscountCode::find($discountCode->id), 'DiscountCode should not exist in DB');
    }
}
