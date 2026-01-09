<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

/**
 * Locale Controller
 * 
 * Prompt 305: Implement Locale Switcher and JS Translations
 * 
 * Handles locale switching for the application.
 * Stores locale preference in session and user profile.
 */
class LocaleController extends Controller
{
    /**
     * Supported locales.
     */
    private const SUPPORTED_LOCALES = [
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
     * Switch the application locale.
     * 
     * POST /locale
     * 
     * @param Request $request
     * @return JsonResponse|RedirectResponse
     */
    public function switch(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'locale' => 'required|string|in:' . implode(',', array_keys(self::SUPPORTED_LOCALES)),
        ]);

        $locale = $request->input('locale');

        Session::put('locale', $locale);

        App::setLocale($locale);

        if ($user = $request->user()) {
            $user->update(['locale' => $locale]);
        }

        if ($request->expectsJson()) {
            return $this->successResponse(
                [
                    'locale' => $locale,
                    'name' => self::SUPPORTED_LOCALES[$locale],
                    'direction' => $this->getDirection($locale),
                ],
                'Locale switched successfully'
            );
        }

        return back()->with('success', 'Language changed successfully');
    }

    /**
     * Get the current locale.
     * 
     * GET /locale
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function current(Request $request): JsonResponse
    {
        $locale = App::getLocale();

        return $this->successResponse([
            'locale' => $locale,
            'name' => self::SUPPORTED_LOCALES[$locale] ?? $locale,
            'direction' => $this->getDirection($locale),
            'supported_locales' => $this->getSupportedLocales(),
        ], 'Current locale retrieved successfully');
    }

    /**
     * Get all supported locales.
     * 
     * GET /locale/supported
     * 
     * @return JsonResponse
     */
    public function supported(): JsonResponse
    {
        return $this->successResponse(
            $this->getSupportedLocales(),
            'Supported locales retrieved successfully'
        );
    }

    /**
     * Get text direction for a locale.
     * 
     * @param string $locale
     * @return string
     */
    private function getDirection(string $locale): string
    {
        $rtlLocales = ['ar', 'he', 'fa', 'ur'];
        return in_array($locale, $rtlLocales) ? 'rtl' : 'ltr';
    }

    /**
     * Get formatted list of supported locales.
     * 
     * @return array
     */
    private function getSupportedLocales(): array
    {
        $locales = [];

        foreach (self::SUPPORTED_LOCALES as $code => $name) {
            $locales[] = [
                'code' => $code,
                'name' => $name,
                'direction' => $this->getDirection($code),
            ];
        }

        return $locales;
    }
}
