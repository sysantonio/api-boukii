<?php

namespace Tests\Repositories;

use App\Models\VouchersLog;
use App\Repositories\VouchersLogRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\ApiTestTrait;

class VouchersLogRepositoryTest extends TestCase
{
    use ApiTestTrait, DatabaseTransactions;

    protected VouchersLogRepository $vouchersLogRepo;

    public function setUp() : void
    {
        parent::setUp();
        $this->vouchersLogRepo = app(VouchersLogRepository::class);
    }

    /**
     * @test create
     */
    public function test_create_vouchers_log()
    {
        $vouchersLog = VouchersLog::factory()->make()->toArray();

        $createdVouchersLog = $this->vouchersLogRepo->create($vouchersLog);

        $createdVouchersLog = $createdVouchersLog->toArray();
        $this->assertArrayHasKey('id', $createdVouchersLog);
        $this->assertNotNull($createdVouchersLog['id'], 'Created VouchersLog must have id specified');
        $this->assertNotNull(VouchersLog::find($createdVouchersLog['id']), 'VouchersLog with given id must be in DB');
        $this->assertModelData($vouchersLog, $createdVouchersLog);
    }

    /**
     * @test read
     */
    public function test_read_vouchers_log()
    {
        $vouchersLog = VouchersLog::factory()->create();

        $dbVouchersLog = $this->vouchersLogRepo->find($vouchersLog->id);

        $dbVouchersLog = $dbVouchersLog->toArray();
        $this->assertModelData($vouchersLog->toArray(), $dbVouchersLog);
    }

    /**
     * @test update
     */
    public function test_update_vouchers_log()
    {
        $vouchersLog = VouchersLog::factory()->create();
        $fakeVouchersLog = VouchersLog::factory()->make()->toArray();

        $updatedVouchersLog = $this->vouchersLogRepo->update($fakeVouchersLog, $vouchersLog->id);

        $this->assertModelData($fakeVouchersLog, $updatedVouchersLog->toArray());
        $dbVouchersLog = $this->vouchersLogRepo->find($vouchersLog->id);
        $this->assertModelData($fakeVouchersLog, $dbVouchersLog->toArray());
    }

    /**
     * @test delete
     */
    public function test_delete_vouchers_log()
    {
        $vouchersLog = VouchersLog::factory()->create();

        $resp = $this->vouchersLogRepo->delete($vouchersLog->id);

        $this->assertTrue($resp);
        $this->assertNull(VouchersLog::find($vouchersLog->id), 'VouchersLog should not exist in DB');
    }
}
