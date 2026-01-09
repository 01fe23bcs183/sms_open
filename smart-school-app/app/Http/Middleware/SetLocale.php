<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

/**
 * Set Locale Middleware
 * 
 * Prompt 305: Implement Locale Switcher and JS Translations
 * 
 * Sets the application locale based on:
 * 1. User's saved preference (if authenticated)
 * 2. Session value
 * 3. Browser's Accept-Language header
 * 4. Default application locale
 */
class SetLocale
{
    /**
     * Supported locales.
     */
    private const SUPPORTED_LOCALES = ['en', 'es', 'fr', 'de', 'ar', 'hi', 'zh', 'ja', 'pt', 'ru'];

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $this->determineLocale($request);
        
        App::setLocale($locale);

        Session::put('locale', $locale);

        return $next($request);
    }

    /**
     * Determine the locale to use.
     *
     * @param Request $request
     * @return string
     */
    private function determineLocale(Request $request): string
    {
        if ($user = $request->user()) {
            $userLocale = $user->locale ?? null;
            if ($userLocale && $this->isSupported($userLocale)) {
                return $userLocale;
            }
        }

        if (Session::has('locale')) {
            $sessionLocale = Session::get('locale');
            if ($this->isSupported($sessionLocale)) {
                return $sessionLocale;
            }
        }

        $browserLocale = $this->getBrowserLocale($request);
        if ($browserLocale && $this->isSupported($browserLocale)) {
            return $browserLocale;
        }

        return config('app.locale', 'en');
    }

    /**
     * Get locale from browser's Accept-Language header.
     *
     * @param Request $request
     * @return string|null
     */
    private function getBrowserLocale(Request $request): ?string
    {
        $acceptLanguage = $request->header('Accept-Language');
        
        if (!$acceptLanguage) {
            return null;
        }

        $locales = [];
        
        foreach (explode(',', $acceptLanguage) as $part) {
            $part = trim($part);
            $quality = 1.0;
            
            if (strpos($part, ';q=') !== false) {
                [$part, $q] = explode(';q=', $part);
                $quality = (float) $q;
            }
            
            $locale = substr($part, 0, 2);
            $locales[$locale] = $quality;
        }

        arsort($locales);

        foreach (array_keys($locales) as $locale) {
            if ($this->isSupported($locale)) {
                return $locale;
            }
        }

        return null;
    }

    /**
     * Check if a locale is supported.
     *
     * @param string $locale
     * @return bool
     */
    private function isSupported(string $locale): bool
    {
        return in_array($locale, self::SUPPORTED_LOCALES);
    }
}
