<?php

namespace Tests\Unit\V5;

use App\Http\Requests\API\V5\ClientLogRequest;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class ClientLogRequestTest extends TestCase
{
    public function test_valid_data_passes_validation(): void
    {
        $request = new ClientLogRequest();
        $validator = Validator::make([
            'level' => 'info',
            'message' => 'test',
            'context' => ['foo' => 'bar'],
            'clientTime' => now()->toISOString(),
        ], $request->rules());

        $this->assertTrue($validator->passes());
    }

    public function test_invalid_level_fails_validation(): void
    {
        $request = new ClientLogRequest();
        $validator = Validator::make([
            'level' => 'invalid',
            'message' => 'test',
            'context' => [],
            'clientTime' => now()->toISOString(),
        ], $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('level', $validator->errors()->toArray());
    }
}
