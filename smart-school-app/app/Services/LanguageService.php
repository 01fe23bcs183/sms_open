<?php

namespace App\Services;

use App\Models\Language;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

/**
 * Language Service
 * 
 * Prompt 445: Create Language Service
 * 
 * Manages language settings and switching for multi-language support.
 * Handles language detection, switching, and persistence.
 * 
 * Features:
 * - Language switching
 * - User preference persistence
 * - Available languages management
 * - RTL language support
 * - Default language configuration
 */
class LanguageService
{
    protected string $defaultLocale;
    protected array $supportedLocales;

    public function __construct()
    {
        $this->defaultLocale = config('app.locale', 'en');
        $this->supportedLocales = config('app.supported_locales', ['en']);
    }

    /**
     * Get all available languages.
     *
     * @param bool $activeOnly
     * @return \Illuminate\Support\Collection
     */
    public function getAvailableLanguages(bool $activeOnly = true)
    {
        $cacheKey = $activeOnly ? 'available_languages_active' : 'available_languages_all';

        return Cache::remember($cacheKey, 3600, function () use ($activeOnly) {
            $query = Language::query();
            
            if ($activeOnly) {
                $query->where('is_active', true);
            }

            return $query->orderBy('name')->get();
        });
    }

    /**
     * Get a language by code.
     *
     * @param string $code
     * @return Language|null
     */
    public function getLanguage(string $code): ?Language
    {
        return Cache::remember("language_{$code}", 3600, function () use ($code) {
            return Language::where('code', $code)->first();
        });
    }

    /**
     * Get the current locale.
     *
     * @return string
     */
    public function getCurrentLocale(): string
    {
        return App::getLocale();
    }

    /**
     * Set the application locale.
     *
     * @param string $locale
     * @return bool
     */
    public function setLocale(string $locale): bool
    {
        if (!$this->isSupported($locale)) {
            Log::warning('Attempted to set unsupported locale', ['locale' => $locale]);
            return false;
        }

        App::setLocale($locale);
        Session::put('locale', $locale);

        Log::info('Locale changed', ['locale' => $locale]);

        return true;
    }

    /**
     * Check if a locale is supported.
     *
     * @param string $locale
     * @return bool
     */
    public function isSupported(string $locale): bool
    {
        $language = $this->getLanguage($locale);
        return $language !== null && $language->is_active;
    }

    /**
     * Get the default locale.
     *
     * @return string
     */
    public function getDefaultLocale(): string
    {
        return $this->defaultLocale;
    }

    /**
     * Get the fallback locale.
     *
     * @return string
     */
    public function getFallbackLocale(): string
    {
        return config('app.fallback_locale', 'en');
    }

    /**
     * Check if current locale is RTL.
     *
     * @return bool
     */
    public function isRtl(): bool
    {
        $language = $this->getLanguage($this->getCurrentLocale());
        return $language ? $language->is_rtl : false;
    }

    /**
     * Get text direction for current locale.
     *
     * @return string
     */
    public function getDirection(): string
    {
        return $this->isRtl() ? 'rtl' : 'ltr';
    }

    /**
     * Get locale from user preference.
     *
     * @param \App\Models\User|null $user
     * @return string
     */
    public function getUserLocale($user = null): string
    {
        if ($user && isset($user->locale)) {
            return $user->locale;
        }

        return Session::get('locale', $this->defaultLocale);
    }

    /**
     * Set user's preferred locale.
     *
     * @param \App\Models\User $user
     * @param string $locale
     * @return bool
     */
    public function setUserLocale($user, string $locale): bool
    {
        if (!$this->isSupported($locale)) {
            return false;
        }

        $user->locale = $locale;
        $user->save();

        $this->setLocale($locale);

        return true;
    }

    /**
     * Detect locale from request.
     *
     * @param \Illuminate\Http\Request $request
     * @return string
     */
    public function detectLocale($request): string
    {
        // Check query parameter
        if ($request->has('lang')) {
            $locale = $request->get('lang');
            if ($this->isSupported($locale)) {
                return $locale;
            }
        }

        // Check session
        if (Session::has('locale')) {
            $locale = Session::get('locale');
            if ($this->isSupported($locale)) {
                return $locale;
            }
        }

        // Check authenticated user preference
        if ($request->user() && isset($request->user()->locale)) {
            $locale = $request->user()->locale;
            if ($this->isSupported($locale)) {
                return $locale;
            }
        }

        // Check browser Accept-Language header
        $browserLocale = $this->detectBrowserLocale($request);
        if ($browserLocale && $this->isSupported($browserLocale)) {
            return $browserLocale;
        }

        return $this->defaultLocale;
    }

    /**
     * Detect locale from browser Accept-Language header.
     *
     * @param \Illuminate\Http\Request $request
     * @return string|null
     */
    protected function detectBrowserLocale($request): ?string
    {
        $acceptLanguage = $request->header('Accept-Language');
        
        if (!$acceptLanguage) {
            return null;
        }

        // Parse Accept-Language header
        $languages = [];
        foreach (explode(',', $acceptLanguage) as $lang) {
            $parts = explode(';', $lang);
            $locale = trim($parts[0]);
            $quality = isset($parts[1]) ? (float) str_replace('q=', '', $parts[1]) : 1.0;
            $languages[$locale] = $quality;
        }

        arsort($languages);

        foreach (array_keys($languages) as $locale) {
            // Try exact match
            if ($this->isSupported($locale)) {
                return $locale;
            }

            // Try language code only (e.g., 'en' from 'en-US')
            $langCode = substr($locale, 0, 2);
            if ($this->isSupported($langCode)) {
                return $langCode;
            }
        }

        return null;
    }

    /**
     * Create a new language.
     *
     * @param array $data
     * @return Language
     */
    public function createLanguage(array $data): Language
    {
        $language = Language::create([
            'code' => $data['code'],
            'name' => $data['name'],
            'native_name' => $data['native_name'] ?? $data['name'],
            'is_rtl' => $data['is_rtl'] ?? false,
            'is_active' => $data['is_active'] ?? true,
        ]);

        $this->clearCache();

        Log::info('Language created', ['code' => $language->code]);

        return $language;
    }

    /**
     * Update a language.
     *
     * @param Language $language
     * @param array $data
     * @return Language
     */
    public function updateLanguage(Language $language, array $data): Language
    {
        $language->update($data);
        $this->clearCache();

        Log::info('Language updated', ['code' => $language->code]);

        return $language->fresh();
    }

    /**
     * Delete a language.
     *
     * @param Language $language
     * @return bool
     */
    public function deleteLanguage(Language $language): bool
    {
        if ($language->code === $this->defaultLocale) {
            throw new \Exception('Cannot delete the default language');
        }

        $result = $language->delete();
        $this->clearCache();

        Log::info('Language deleted', ['code' => $language->code]);

        return $result;
    }

    /**
     * Activate a language.
     *
     * @param Language $language
     * @return Language
     */
    public function activateLanguage(Language $language): Language
    {
        $language->update(['is_active' => true]);
        $this->clearCache();

        return $language;
    }

    /**
     * Deactivate a language.
     *
     * @param Language $language
     * @return Language
     */
    public function deactivateLanguage(Language $language): Language
    {
        if ($language->code === $this->defaultLocale) {
            throw new \Exception('Cannot deactivate the default language');
        }

        $language->update(['is_active' => false]);
        $this->clearCache();

        return $language;
    }

    /**
     * Get language statistics.
     *
     * @return array
     */
    public function getStatistics(): array
    {
        return [
            'total_languages' => Language::count(),
            'active_languages' => Language::where('is_active', true)->count(),
            'rtl_languages' => Language::where('is_rtl', true)->count(),
            'current_locale' => $this->getCurrentLocale(),
            'default_locale' => $this->defaultLocale,
        ];
    }

    /**
     * Clear language cache.
     *
     * @return void
     */
    public function clearCache(): void
    {
        Cache::forget('available_languages_active');
        Cache::forget('available_languages_all');

        // Clear individual language caches
        $languages = Language::all();
        foreach ($languages as $language) {
            Cache::forget("language_{$language->code}");
        }
    }
}
