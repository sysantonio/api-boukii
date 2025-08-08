<?php

namespace Tests\Unit\V5\Exceptions;

use Tests\TestCase;
use App\V5\Exceptions\Season\SeasonNotFoundException;
use App\V5\Exceptions\Season\SeasonValidationException;
use App\V5\Exceptions\Auth\AuthenticationException;
use App\V5\Exceptions\Auth\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;

class V5ExceptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_season_not_found_exception_structure()
    {
        $exception = SeasonNotFoundException::withId(123);
        
        $this->assertEquals('SEASON_NOT_FOUND', $exception->getErrorCode());
        $this->assertEquals(404, $exception->getHttpStatusCode());
        $this->assertEquals(['season_id' => 123], $exception->getContext());
        
        $response = $exception->toResponse();
        $data = $response->getData(true);
        
        $this->assertTrue($data['error']);
        $this->assertEquals('SEASON_NOT_FOUND', $data['code']);
        $this->assertArrayHasKey('message', $data);
        $this->assertArrayHasKey('timestamp', $data);
        $this->assertEquals(['season_id' => 123], $data['context']);
    }

    public function test_season_validation_exception_overlapping_seasons()
    {
        $conflictingSeasons = [1, 2, 3];
        $exception = SeasonValidationException::overlappingSeasons($conflictingSeasons);
        
        $this->assertEquals('SEASON_VALIDATION_ERROR', $exception->getErrorCode());
        $this->assertEquals(422, $exception->getHttpStatusCode());
        $this->assertEquals(['conflicting_seasons' => $conflictingSeasons], $exception->getContext());
    }

    public function test_authentication_exception_invalid_credentials()
    {
        $exception = AuthenticationException::invalidCredentials();
        
        $this->assertEquals('AUTHENTICATION_FAILED', $exception->getErrorCode());
        $this->assertEquals(401, $exception->getHttpStatusCode());
        
        $response = $exception->toResponse();
        $this->assertEquals(401, $response->getStatusCode());
    }

    public function test_authorization_exception_missing_permission()
    {
        $permission = 'view_schools';
        $exception = AuthorizationException::missingPermission($permission);
        
        $this->assertEquals('AUTHORIZATION_FAILED', $exception->getErrorCode());
        $this->assertEquals(403, $exception->getHttpStatusCode());
        $this->assertEquals(['required_permission' => $permission], $exception->getContext());
    }

    public function test_exception_response_includes_debug_info_when_debug_enabled()
    {
        config(['app.debug' => true]);
        
        $exception = SeasonNotFoundException::withId(123);
        $response = $exception->toResponse();
        $data = $response->getData(true);
        
        $this->assertArrayHasKey('debug', $data);
        $this->assertArrayHasKey('file', $data['debug']);
        $this->assertArrayHasKey('line', $data['debug']);
        $this->assertArrayHasKey('trace', $data['debug']);
    }

    public function test_exception_response_excludes_debug_info_when_debug_disabled()
    {
        config(['app.debug' => false]);
        
        $exception = SeasonNotFoundException::withId(123);
        $response = $exception->toResponse();
        $data = $response->getData(true);
        
        $this->assertArrayNotHasKey('debug', $data);
    }

    /**
     * @dataProvider exceptionLanguageProvider
     */
    public function test_exception_messages_are_localized($locale, $expectedMessageKey)
    {
        app()->setLocale($locale);
        
        $exception = SeasonNotFoundException::withId(123);
        $response = $exception->toResponse();
        $data = $response->getData(true);
        
        // Message should be translated
        $this->assertNotEquals($expectedMessageKey, $data['message']);
        $this->assertIsString($data['message']);
    }

    public function exceptionLanguageProvider(): array
    {
        return [
            ['en', 'exceptions.season.not_found'],
            ['es', 'exceptions.season.not_found'],
            ['fr', 'exceptions.season.not_found'],
            ['de', 'exceptions.season.not_found'],
            ['it', 'exceptions.season.not_found'],
        ];
    }
}