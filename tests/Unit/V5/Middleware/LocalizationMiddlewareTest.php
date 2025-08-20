<?php

namespace Tests\Unit\V5\Middleware;

use Tests\TestCase;
use App\V5\Middleware\LocalizationMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class LocalizationMiddlewareTest extends TestCase
{
    protected LocalizationMiddleware $middleware;

    protected function setUp(): void
    {
        parent::setUp();
        $this->middleware = new LocalizationMiddleware();
    }

    public function test_sets_locale_from_query_parameter(): void
    {
        $request = Request::create('/test', 'GET', ['lang' => 'es']);
        
        $this->middleware->handle($request, function ($req) {
            $this->assertEquals('es', App::getLocale());
            return response('ok');
        });
    }

    public function test_sets_locale_from_accept_language_header(): void
    {
        $request = Request::create('/test', 'GET');
        $request->headers->set('Accept-Language', 'fr-FR,fr;q=0.9,en;q=0.8');
        
        $this->middleware->handle($request, function ($req) {
            $this->assertEquals('fr', App::getLocale());
            return response('ok');
        });
    }

    public function test_parses_complex_accept_language_header(): void
    {
        $request = Request::create('/test', 'GET');
        $request->headers->set('Accept-Language', 'de-DE,de;q=0.9,en-US;q=0.8,en;q=0.7,es;q=0.6');
        
        $this->middleware->handle($request, function ($req) {
            $this->assertEquals('de', App::getLocale());
            return response('ok');
        });
    }

    public function test_falls_back_to_default_locale_for_unsupported_language(): void
    {
        $request = Request::create('/test', 'GET', ['lang' => 'zh']);
        
        $this->middleware->handle($request, function ($req) {
            $this->assertEquals('en', App::getLocale());
            return response('ok');
        });
    }

    public function test_falls_back_to_default_locale_when_no_language_specified(): void
    {
        $request = Request::create('/test', 'GET');
        
        $this->middleware->handle($request, function ($req) {
            $this->assertEquals('en', App::getLocale());
            return response('ok');
        });
    }

    /**
     * @dataProvider validLocaleProvider
     */
    public function test_accepts_all_valid_locales($locale): void
    {
        $request = Request::create('/test', 'GET', ['lang' => $locale]);
        
        $this->middleware->handle($request, function ($req) use ($locale) {
            $this->assertEquals($locale, App::getLocale());
            return response('ok');
        });
    }

    public static function validLocaleProvider(): array
    {
        return [
            ['en'],
            ['es'],
            ['fr'],
            ['de'],
            ['it'],
        ];
    }

    public function test_query_parameter_takes_precedence_over_header(): void
    {
        $request = Request::create('/test', 'GET', ['lang' => 'it']);
        $request->headers->set('Accept-Language', 'fr-FR,fr;q=0.9');
        
        $this->middleware->handle($request, function ($req) {
            $this->assertEquals('it', App::getLocale());
            return response('ok');
        });
    }

    public function test_handles_malformed_accept_language_header_gracefully(): void
    {
        $request = Request::create('/test', 'GET');
        $request->headers->set('Accept-Language', 'invalid-header-format');
        
        $this->middleware->handle($request, function ($req) {
            $this->assertEquals('en', App::getLocale());
            return response('ok');
        });
    }

    public function test_available_locales_method(): void
    {
        $locales = LocalizationMiddleware::getAvailableLocales();
        
        $this->assertIsArray($locales);
        $this->assertContains('en', $locales);
        $this->assertContains('es', $locales);
        $this->assertContains('fr', $locales);
        $this->assertContains('de', $locales);
        $this->assertContains('it', $locales);
        $this->assertCount(5, $locales);
    }
}
