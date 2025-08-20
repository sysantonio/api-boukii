<?php
namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\DB;

class CiSmokeTest extends TestCase
{
    public function test_db_has_core_tables(): void
    {
        $this->assertTrue(DB::getSchemaBuilder()->hasTable('users'));
        $this->assertTrue(DB::getSchemaBuilder()->hasTable('schools'));
        $this->assertTrue(DB::getSchemaBuilder()->hasTable('seasons'));
    }
}
