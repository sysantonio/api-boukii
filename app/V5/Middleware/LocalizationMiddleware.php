<?php

namespace App\V5\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class LocalizationMiddleware
{
    /**
     * Available locales for V5
     */
    private const AVAILABLE_LOCALES = ['en', 'es', 'fr', 'de', 'it'];

    /**
     * Default locale
     */
    private const DEFAULT_LOCALE = 'en';

    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $locale = $this->determineLocale($request);

        if ($this->isValidLocale($locale)) {
            App::setLocale($locale);
        } else {
            App::setLocale(self::DEFAULT_LOCALE);
        }

        return $next($request);
    }

    /**
     * Determine the locale from various sources
     */
    private function determineLocale(Request $request): string
    {
        // 1. Check query parameter
        if ($request->has('lang')) {
            return $request->get('lang');
        }

        // 2. Check header
        if ($request->hasHeader('Accept-Language')) {
            $acceptLanguage = $request->header('Accept-Language');
            $preferredLanguage = $this->parseAcceptLanguage($acceptLanguage);
            if ($preferredLanguage) {
                return $preferredLanguage;
            }
        }

        // 3. Check user preferences (if authenticated)
        $user = $request->user();
        if ($user && isset($user->preferred_language)) {
            return $user->preferred_language;
        }

        // 4. Check school default language (if season context available)
        $seasonId = $request->get('season_id');
        if ($seasonId) {
            $schoolLanguage = $this->getSchoolDefaultLanguage($seasonId);
            if ($schoolLanguage) {
                return $schoolLanguage;
            }
        }

        // 5. Return default locale
        return self::DEFAULT_LOCALE;
    }

    /**
     * Parse Accept-Language header and return preferred available locale
     */
    private function parseAcceptLanguage(string $acceptLanguage): ?string
    {
        $locales = [];

        // Parse Accept-Language header
        foreach (explode(',', $acceptLanguage) as $lang) {
            $parts = explode(';', trim($lang));
            $locale = trim($parts[0]);
            $quality = 1.0;

            if (isset($parts[1]) && strpos($parts[1], 'q=') === 0) {
                $quality = (float) substr($parts[1], 2);
            }

            // Extract language code (before hyphen if present)
            $languageCode = explode('-', $locale)[0];

            if ($this->isValidLocale($languageCode)) {
                $locales[$languageCode] = $quality;
            }
        }

        // Sort by quality and return highest
        if (! empty($locales)) {
            arsort($locales);

            return array_key_first($locales);
        }

        return null;
    }

    /**
     * Get school default language from season context
     */
    private function getSchoolDefaultLanguage(int $seasonId): ?string
    {
        try {
            // This would need to be implemented based on your school-season settings
            // For now, return null
            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Check if locale is valid
     */
    private function isValidLocale(string $locale): bool
    {
        return in_array($locale, self::AVAILABLE_LOCALES);
    }

    /**
     * Get available locales
     */
    public static function getAvailableLocales(): array
    {
        return self::AVAILABLE_LOCALES;
    }
}
