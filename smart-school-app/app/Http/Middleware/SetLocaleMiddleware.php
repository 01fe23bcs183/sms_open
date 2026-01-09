<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\LanguageService;

/**
 * Set Locale Middleware
 * 
 * Prompt 447: Create Language Middleware
 * 
 * Middleware to detect and set the application locale based on
 * user preferences, session, or browser settings.
 * 
 * Features:
 * - Automatic locale detection
 * - Session persistence
 * - User preference support
 * - Query parameter override
 * - Browser language detection
 */
class SetLocaleMiddleware
{
    protected LanguageService $languageService;

    public function __construct(LanguageService $languageService)
    {
        $this->languageService = $languageService;
    }

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Detect the appropriate locale
        $locale = $this->languageService->detectLocale($request);

        // Set the application locale
        $this->languageService->setLocale($locale);

        // Add locale info to the request for use in views
        $request->attributes->set('locale', $locale);
        $request->attributes->set('direction', $this->languageService->getDirection());
        $request->attributes->set('is_rtl', $this->languageService->isRtl());

        // Share locale data with views
        view()->share('currentLocale', $locale);
        view()->share('textDirection', $this->languageService->getDirection());
        view()->share('isRtl', $this->languageService->isRtl());
        view()->share('availableLanguages', $this->languageService->getAvailableLanguages());

        return $next($request);
    }
}
