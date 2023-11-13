<?php

namespace Tests\Repositories;

use App\Models\DegreesSchoolSportGoal;
use App\Repositories\DegreesSchoolSportGoalRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\ApiTestTrait;

class DegreesSchoolSportGoalRepositoryTest extends TestCase
{
    use ApiTestTrait, DatabaseTransactions;

    protected DegreesSchoolSportGoalRepository $degreesSchoolSportGoalRepo;

    public function setUp() : void
    {
        parent::setUp();
        $this->degreesSchoolSportGoalRepo = app(DegreesSchoolSportGoalRepository::class);
    }

    /**
     * @test create
     */
    public function test_create_degrees_school_sport_goal()
    {
        $degreesSchoolSportGoal = DegreesSchoolSportGoal::factory()->make()->toArray();

        $createdDegreesSchoolSportGoal = $this->degreesSchoolSportGoalRepo->create($degreesSchoolSportGoal);

        $createdDegreesSchoolSportGoal = $createdDegreesSchoolSportGoal->toArray();
        $this->assertArrayHasKey('id', $createdDegreesSchoolSportGoal);
        $this->assertNotNull($createdDegreesSchoolSportGoal['id'], 'Created DegreesSchoolSportGoal must have id specified');
        $this->assertNotNull(DegreesSchoolSportGoal::find($createdDegreesSchoolSportGoal['id']), 'DegreesSchoolSportGoal with given id must be in DB');
        $this->assertModelData($degreesSchoolSportGoal, $createdDegreesSchoolSportGoal);
    }

    /**
     * @test read
     */
    public function test_read_degrees_school_sport_goal()
    {
        $degreesSchoolSportGoal = DegreesSchoolSportGoal::factory()->create();

        $dbDegreesSchoolSportGoal = $this->degreesSchoolSportGoalRepo->find($degreesSchoolSportGoal->id);

        $dbDegreesSchoolSportGoal = $dbDegreesSchoolSportGoal->toArray();
        $this->assertModelData($degreesSchoolSportGoal->toArray(), $dbDegreesSchoolSportGoal);
    }

    /**
     * @test update
     */
    public function test_update_degrees_school_sport_goal()
    {
        $degreesSchoolSportGoal = DegreesSchoolSportGoal::factory()->create();
        $fakeDegreesSchoolSportGoal = DegreesSchoolSportGoal::factory()->make()->toArray();

        $updatedDegreesSchoolSportGoal = $this->degreesSchoolSportGoalRepo->update($fakeDegreesSchoolSportGoal, $degreesSchoolSportGoal->id);

        $this->assertModelData($fakeDegreesSchoolSportGoal, $updatedDegreesSchoolSportGoal->toArray());
        $dbDegreesSchoolSportGoal = $this->degreesSchoolSportGoalRepo->find($degreesSchoolSportGoal->id);
        $this->assertModelData($fakeDegreesSchoolSportGoal, $dbDegreesSchoolSportGoal->toArray());
    }

    /**
     * @test delete
     */
    public function test_delete_degrees_school_sport_goal()
    {
        $degreesSchoolSportGoal = DegreesSchoolSportGoal::factory()->create();

        $resp = $this->degreesSchoolSportGoalRepo->delete($degreesSchoolSportGoal->id);

        $this->assertTrue($resp);
        $this->assertNull(DegreesSchoolSportGoal::find($degreesSchoolSportGoal->id), 'DegreesSchoolSportGoal should not exist in DB');
    }
}
