<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Locale Middleware
 * 
 * Prompt 313: Create Locale Middleware
 * 
 * Applies user-selected language across requests.
 * Sets locale based on session/user preference.
 */
class LocaleMiddleware
{
    /**
     * Supported locales.
     */
    protected array $supportedLocales = [
        'en' => 'English',
        'es' => 'Spanish',
        'fr' => 'French',
        'de' => 'German',
        'ar' => 'Arabic',
        'hi' => 'Hindi',
        'zh' => 'Chinese',
        'ja' => 'Japanese',
        'pt' => 'Portuguese',
        'ru' => 'Russian',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $this->determineLocale($request);

        app()->setLocale($locale);

        if (!session()->has('locale')) {
            session()->put('locale', $locale);
        }

        view()->share('currentLocale', $locale);
        view()->share('supportedLocales', $this->supportedLocales);
        view()->share('isRtl', $this->isRtlLocale($locale));

        return $next($request);
    }

    /**
     * Determine the locale for the request.
     */
    protected function determineLocale(Request $request): string
    {
        $user = $request->user();
        if ($user && !empty($user->locale)) {
            return $this->validateLocale($user->locale);
        }

        if (session()->has('locale')) {
            return $this->validateLocale(session('locale'));
        }

        if ($request->hasHeader('Accept-Language')) {
            $browserLocale = $this->parseAcceptLanguage($request->header('Accept-Language'));
            if ($browserLocale) {
                return $browserLocale;
            }
        }

        return config('app.locale', 'en');
    }

    /**
     * Validate and return a supported locale.
     */
    protected function validateLocale(string $locale): string
    {
        $locale = strtolower(substr($locale, 0, 2));

        if (array_key_exists($locale, $this->supportedLocales)) {
            return $locale;
        }

        return config('app.locale', 'en');
    }

    /**
     * Parse Accept-Language header.
     */
    protected function parseAcceptLanguage(string $header): ?string
    {
        $languages = [];

        foreach (explode(',', $header) as $part) {
            $part = trim($part);
            $quality = 1.0;

            if (strpos($part, ';q=') !== false) {
                [$part, $q] = explode(';q=', $part);
                $quality = (float) $q;
            }

            $locale = strtolower(substr(trim($part), 0, 2));
            $languages[$locale] = $quality;
        }

        arsort($languages);

        foreach (array_keys($languages) as $locale) {
            if (array_key_exists($locale, $this->supportedLocales)) {
                return $locale;
            }
        }

        return null;
    }

    /**
     * Check if locale is RTL.
     */
    protected function isRtlLocale(string $locale): bool
    {
        return in_array($locale, ['ar', 'he', 'fa', 'ur']);
    }

    /**
     * Get supported locales.
     */
    public function getSupportedLocales(): array
    {
        return $this->supportedLocales;
    }
}
