<?php

namespace App\Services;

use App\Models\Translation;
use App\Models\Language;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;

/**
 * Translation Service
 * 
 * Prompt 446: Create Translation Service
 * 
 * Manages translations for multi-language support.
 * Handles translation CRUD, caching, and import/export.
 * 
 * Features:
 * - Translation management
 * - Group-based organization
 * - Cache management
 * - Import/export functionality
 * - Missing translation tracking
 */
class TranslationService
{
    protected string $defaultLocale;

    public function __construct()
    {
        $this->defaultLocale = config('app.locale', 'en');
    }

    /**
     * Get a translation.
     *
     * @param string $key
     * @param string|null $locale
     * @param string $group
     * @return string|null
     */
    public function get(string $key, ?string $locale = null, string $group = 'general'): ?string
    {
        $locale = $locale ?? app()->getLocale();
        $cacheKey = "translation_{$locale}_{$group}_{$key}";

        return Cache::remember($cacheKey, 3600, function () use ($key, $locale, $group) {
            $translation = Translation::where('key', $key)
                ->where('locale', $locale)
                ->where('group', $group)
                ->first();

            if ($translation) {
                return $translation->value;
            }

            // Try fallback locale
            if ($locale !== $this->defaultLocale) {
                $fallback = Translation::where('key', $key)
                    ->where('locale', $this->defaultLocale)
                    ->where('group', $group)
                    ->first();

                if ($fallback) {
                    return $fallback->value;
                }
            }

            return null;
        });
    }

    /**
     * Set a translation.
     *
     * @param string $key
     * @param string $value
     * @param string $locale
     * @param string $group
     * @return Translation
     */
    public function set(string $key, string $value, string $locale, string $group = 'general'): Translation
    {
        $translation = Translation::updateOrCreate(
            [
                'key' => $key,
                'locale' => $locale,
                'group' => $group,
            ],
            [
                'value' => $value,
            ]
        );

        $this->clearCache($locale, $group, $key);

        Log::info('Translation set', [
            'key' => $key,
            'locale' => $locale,
            'group' => $group,
        ]);

        return $translation;
    }

    /**
     * Delete a translation.
     *
     * @param string $key
     * @param string $locale
     * @param string $group
     * @return bool
     */
    public function delete(string $key, string $locale, string $group = 'general'): bool
    {
        $result = Translation::where('key', $key)
            ->where('locale', $locale)
            ->where('group', $group)
            ->delete();

        $this->clearCache($locale, $group, $key);

        return $result > 0;
    }

    /**
     * Get all translations for a locale.
     *
     * @param string $locale
     * @param string|null $group
     * @return \Illuminate\Support\Collection
     */
    public function getAll(string $locale, ?string $group = null)
    {
        $query = Translation::where('locale', $locale);

        if ($group) {
            $query->where('group', $group);
        }

        return $query->get();
    }

    /**
     * Get translations grouped by group.
     *
     * @param string $locale
     * @return array
     */
    public function getGrouped(string $locale): array
    {
        $translations = Translation::where('locale', $locale)->get();

        return $translations->groupBy('group')->map(function ($items) {
            return $items->pluck('value', 'key')->toArray();
        })->toArray();
    }

    /**
     * Get all translation groups.
     *
     * @return array
     */
    public function getGroups(): array
    {
        return Translation::distinct()->pluck('group')->toArray();
    }

    /**
     * Import translations from array.
     *
     * @param array $translations
     * @param string $locale
     * @param string $group
     * @param bool $overwrite
     * @return array
     */
    public function import(array $translations, string $locale, string $group = 'general', bool $overwrite = false): array
    {
        $imported = 0;
        $skipped = 0;

        foreach ($translations as $key => $value) {
            if (!is_string($value)) {
                continue;
            }

            $existing = Translation::where('key', $key)
                ->where('locale', $locale)
                ->where('group', $group)
                ->first();

            if ($existing && !$overwrite) {
                $skipped++;
                continue;
            }

            $this->set($key, $value, $locale, $group);
            $imported++;
        }

        Log::info('Translations imported', [
            'locale' => $locale,
            'group' => $group,
            'imported' => $imported,
            'skipped' => $skipped,
        ]);

        return [
            'imported' => $imported,
            'skipped' => $skipped,
        ];
    }

    /**
     * Export translations to array.
     *
     * @param string $locale
     * @param string|null $group
     * @return array
     */
    public function export(string $locale, ?string $group = null): array
    {
        $query = Translation::where('locale', $locale);

        if ($group) {
            $query->where('group', $group);
        }

        return $query->pluck('value', 'key')->toArray();
    }

    /**
     * Import translations from JSON file.
     *
     * @param string $filePath
     * @param string $locale
     * @param string $group
     * @param bool $overwrite
     * @return array
     */
    public function importFromJson(string $filePath, string $locale, string $group = 'general', bool $overwrite = false): array
    {
        if (!File::exists($filePath)) {
            throw new \Exception("File not found: {$filePath}");
        }

        $content = File::get($filePath);
        $translations = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Invalid JSON file');
        }

        return $this->import($translations, $locale, $group, $overwrite);
    }

    /**
     * Export translations to JSON file.
     *
     * @param string $filePath
     * @param string $locale
     * @param string|null $group
     * @return bool
     */
    public function exportToJson(string $filePath, string $locale, ?string $group = null): bool
    {
        $translations = $this->export($locale, $group);
        $content = json_encode($translations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        return File::put($filePath, $content) !== false;
    }

    /**
     * Import translations from Laravel language file.
     *
     * @param string $locale
     * @param string $group
     * @param bool $overwrite
     * @return array
     */
    public function importFromLangFile(string $locale, string $group, bool $overwrite = false): array
    {
        $filePath = lang_path("{$locale}/{$group}.php");

        if (!File::exists($filePath)) {
            throw new \Exception("Language file not found: {$filePath}");
        }

        $translations = include $filePath;

        if (!is_array($translations)) {
            throw new \Exception('Invalid language file format');
        }

        // Flatten nested arrays
        $flattened = $this->flattenArray($translations);

        return $this->import($flattened, $locale, $group, $overwrite);
    }

    /**
     * Export translations to Laravel language file.
     *
     * @param string $locale
     * @param string $group
     * @return bool
     */
    public function exportToLangFile(string $locale, string $group): bool
    {
        $translations = $this->export($locale, $group);
        
        // Unflatten to nested array
        $nested = $this->unflattenArray($translations);

        $content = "<?php\n\nreturn " . var_export($nested, true) . ";\n";
        $filePath = lang_path("{$locale}/{$group}.php");

        // Ensure directory exists
        $directory = dirname($filePath);
        if (!File::isDirectory($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        return File::put($filePath, $content) !== false;
    }

    /**
     * Flatten nested array with dot notation.
     *
     * @param array $array
     * @param string $prefix
     * @return array
     */
    protected function flattenArray(array $array, string $prefix = ''): array
    {
        $result = [];

        foreach ($array as $key => $value) {
            $newKey = $prefix ? "{$prefix}.{$key}" : $key;

            if (is_array($value)) {
                $result = array_merge($result, $this->flattenArray($value, $newKey));
            } else {
                $result[$newKey] = $value;
            }
        }

        return $result;
    }

    /**
     * Unflatten dot notation array to nested array.
     *
     * @param array $array
     * @return array
     */
    protected function unflattenArray(array $array): array
    {
        $result = [];

        foreach ($array as $key => $value) {
            $keys = explode('.', $key);
            $current = &$result;

            foreach ($keys as $i => $k) {
                if ($i === count($keys) - 1) {
                    $current[$k] = $value;
                } else {
                    if (!isset($current[$k]) || !is_array($current[$k])) {
                        $current[$k] = [];
                    }
                    $current = &$current[$k];
                }
            }
        }

        return $result;
    }

    /**
     * Find missing translations.
     *
     * @param string $sourceLocale
     * @param string $targetLocale
     * @param string|null $group
     * @return array
     */
    public function findMissing(string $sourceLocale, string $targetLocale, ?string $group = null): array
    {
        $sourceQuery = Translation::where('locale', $sourceLocale);
        $targetQuery = Translation::where('locale', $targetLocale);

        if ($group) {
            $sourceQuery->where('group', $group);
            $targetQuery->where('group', $group);
        }

        $sourceKeys = $sourceQuery->pluck('key', 'group')->toArray();
        $targetKeys = $targetQuery->pluck('key', 'group')->toArray();

        $missing = [];

        foreach ($sourceKeys as $g => $key) {
            if (!isset($targetKeys[$g]) || $targetKeys[$g] !== $key) {
                $missing[] = [
                    'group' => $g,
                    'key' => $key,
                ];
            }
        }

        return $missing;
    }

    /**
     * Copy translations from one locale to another.
     *
     * @param string $sourceLocale
     * @param string $targetLocale
     * @param string|null $group
     * @param bool $overwrite
     * @return array
     */
    public function copyTranslations(
        string $sourceLocale,
        string $targetLocale,
        ?string $group = null,
        bool $overwrite = false
    ): array {
        $query = Translation::where('locale', $sourceLocale);

        if ($group) {
            $query->where('group', $group);
        }

        $translations = $query->get();
        $copied = 0;
        $skipped = 0;

        foreach ($translations as $translation) {
            $existing = Translation::where('key', $translation->key)
                ->where('locale', $targetLocale)
                ->where('group', $translation->group)
                ->first();

            if ($existing && !$overwrite) {
                $skipped++;
                continue;
            }

            $this->set($translation->key, $translation->value, $targetLocale, $translation->group);
            $copied++;
        }

        return [
            'copied' => $copied,
            'skipped' => $skipped,
        ];
    }

    /**
     * Search translations.
     *
     * @param string $query
     * @param string|null $locale
     * @param string|null $group
     * @return \Illuminate\Support\Collection
     */
    public function search(string $query, ?string $locale = null, ?string $group = null)
    {
        $builder = Translation::where(function ($q) use ($query) {
            $q->where('key', 'like', "%{$query}%")
              ->orWhere('value', 'like', "%{$query}%");
        });

        if ($locale) {
            $builder->where('locale', $locale);
        }

        if ($group) {
            $builder->where('group', $group);
        }

        return $builder->get();
    }

    /**
     * Get translation statistics.
     *
     * @return array
     */
    public function getStatistics(): array
    {
        $languages = Language::where('is_active', true)->get();
        $stats = [];

        foreach ($languages as $language) {
            $stats[$language->code] = [
                'name' => $language->name,
                'total' => Translation::where('locale', $language->code)->count(),
                'groups' => Translation::where('locale', $language->code)
                    ->distinct()
                    ->pluck('group')
                    ->count(),
            ];
        }

        return [
            'languages' => $stats,
            'total_translations' => Translation::count(),
            'total_groups' => count($this->getGroups()),
        ];
    }

    /**
     * Clear translation cache.
     *
     * @param string|null $locale
     * @param string|null $group
     * @param string|null $key
     * @return void
     */
    public function clearCache(?string $locale = null, ?string $group = null, ?string $key = null): void
    {
        if ($locale && $group && $key) {
            Cache::forget("translation_{$locale}_{$group}_{$key}");
        } else {
            // Clear all translation caches (this is a simplified approach)
            // In production, you might want to use cache tags
            Cache::flush();
        }
    }
}
