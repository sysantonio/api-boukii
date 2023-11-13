<?php

namespace Tests\Repositories;

use App\Models\SchoolUser;
use App\Repositories\SchoolUserRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\ApiTestTrait;

class SchoolUserRepositoryTest extends TestCase
{
    use ApiTestTrait, DatabaseTransactions;

    protected SchoolUserRepository $schoolUserRepo;

    public function setUp() : void
    {
        parent::setUp();
        $this->schoolUserRepo = app(SchoolUserRepository::class);
    }

    /**
     * @test create
     */
    public function test_create_school_user()
    {
        $schoolUser = SchoolUser::factory()->make()->toArray();

        $createdSchoolUser = $this->schoolUserRepo->create($schoolUser);

        $createdSchoolUser = $createdSchoolUser->toArray();
        $this->assertArrayHasKey('id', $createdSchoolUser);
        $this->assertNotNull($createdSchoolUser['id'], 'Created SchoolUser must have id specified');
        $this->assertNotNull(SchoolUser::find($createdSchoolUser['id']), 'SchoolUser with given id must be in DB');
        $this->assertModelData($schoolUser, $createdSchoolUser);
    }

    /**
     * @test read
     */
    public function test_read_school_user()
    {
        $schoolUser = SchoolUser::factory()->create();

        $dbSchoolUser = $this->schoolUserRepo->find($schoolUser->id);

        $dbSchoolUser = $dbSchoolUser->toArray();
        $this->assertModelData($schoolUser->toArray(), $dbSchoolUser);
    }

    /**
     * @test update
     */
    public function test_update_school_user()
    {
        $schoolUser = SchoolUser::factory()->create();
        $fakeSchoolUser = SchoolUser::factory()->make()->toArray();

        $updatedSchoolUser = $this->schoolUserRepo->update($fakeSchoolUser, $schoolUser->id);

        $this->assertModelData($fakeSchoolUser, $updatedSchoolUser->toArray());
        $dbSchoolUser = $this->schoolUserRepo->find($schoolUser->id);
        $this->assertModelData($fakeSchoolUser, $dbSchoolUser->toArray());
    }

    /**
     * @test delete
     */
    public function test_delete_school_user()
    {
        $schoolUser = SchoolUser::factory()->create();

        $resp = $this->schoolUserRepo->delete($schoolUser->id);

        $this->assertTrue($resp);
        $this->assertNull(SchoolUser::find($schoolUser->id), 'SchoolUser should not exist in DB');
    }
}
