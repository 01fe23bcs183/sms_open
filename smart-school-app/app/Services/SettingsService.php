<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Settings Service
 * 
 * Prompt 336: Create Settings Service
 * 
 * Manages system settings and cache. Reads and writes settings with
 * caching layer. Supports per-school or global settings.
 */
class SettingsService
{
    /**
     * Cache key prefix for settings.
     */
    private const CACHE_PREFIX = 'settings_';

    /**
     * Cache TTL in seconds (1 hour).
     */
    private const CACHE_TTL = 3600;

    /**
     * Get a setting value.
     * 
     * @param string $key
     * @param mixed $default
     * @param int|null $schoolId
     * @return mixed
     */
    public function get(string $key, mixed $default = null, ?int $schoolId = null): mixed
    {
        $cacheKey = $this->getCacheKey($key, $schoolId);
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($key, $default, $schoolId) {
            $query = Setting::where('key', $key);
            
            if ($schoolId) {
                $query->where('school_id', $schoolId);
            } else {
                $query->whereNull('school_id');
            }
            
            $setting = $query->first();
            
            if (!$setting) {
                return $default;
            }
            
            return $this->castValue($setting->value, $setting->type);
        });
    }

    /**
     * Set a setting value.
     * 
     * @param string $key
     * @param mixed $value
     * @param string $type
     * @param int|null $schoolId
     * @param string|null $group
     * @return Setting
     */
    public function set(
        string $key,
        mixed $value,
        string $type = 'string',
        ?int $schoolId = null,
        ?string $group = null
    ): Setting {
        $setting = Setting::updateOrCreate(
            [
                'key' => $key,
                'school_id' => $schoolId,
            ],
            [
                'value' => $this->serializeValue($value, $type),
                'type' => $type,
                'group' => $group,
            ]
        );
        
        // Clear cache
        $this->clearCache($key, $schoolId);
        
        return $setting;
    }

    /**
     * Set multiple settings at once.
     * 
     * @param array $settings Array of ['key' => value] or ['key' => ['value' => x, 'type' => y]]
     * @param int|null $schoolId
     * @param string|null $group
     * @return int Number of settings updated
     */
    public function setMany(array $settings, ?int $schoolId = null, ?string $group = null): int
    {
        $count = 0;
        
        DB::transaction(function () use ($settings, $schoolId, $group, &$count) {
            foreach ($settings as $key => $data) {
                if (is_array($data)) {
                    $value = $data['value'] ?? null;
                    $type = $data['type'] ?? 'string';
                } else {
                    $value = $data;
                    $type = 'string';
                }
                
                $this->set($key, $value, $type, $schoolId, $group);
                $count++;
            }
        });
        
        return $count;
    }

    /**
     * Delete a setting.
     * 
     * @param string $key
     * @param int|null $schoolId
     * @return bool
     */
    public function delete(string $key, ?int $schoolId = null): bool
    {
        $query = Setting::where('key', $key);
        
        if ($schoolId) {
            $query->where('school_id', $schoolId);
        } else {
            $query->whereNull('school_id');
        }
        
        $result = $query->delete() > 0;
        
        if ($result) {
            $this->clearCache($key, $schoolId);
        }
        
        return $result;
    }

    /**
     * Get all settings.
     * 
     * @param int|null $schoolId
     * @param string|null $group
     * @return array
     */
    public function getAll(?int $schoolId = null, ?string $group = null): array
    {
        $query = Setting::query();
        
        if ($schoolId) {
            $query->where('school_id', $schoolId);
        } else {
            $query->whereNull('school_id');
        }
        
        if ($group) {
            $query->where('group', $group);
        }
        
        $settings = $query->get();
        
        $result = [];
        foreach ($settings as $setting) {
            $result[$setting->key] = $this->castValue($setting->value, $setting->type);
        }
        
        return $result;
    }

    /**
     * Get settings by group.
     * 
     * @param string $group
     * @param int|null $schoolId
     * @return array
     */
    public function getByGroup(string $group, ?int $schoolId = null): array
    {
        return $this->getAll($schoolId, $group);
    }

    /**
     * Get available setting groups.
     * 
     * @param int|null $schoolId
     * @return array
     */
    public function getGroups(?int $schoolId = null): array
    {
        $query = Setting::select('group')->distinct();
        
        if ($schoolId) {
            $query->where('school_id', $schoolId);
        } else {
            $query->whereNull('school_id');
        }
        
        return $query->whereNotNull('group')->pluck('group')->toArray();
    }

    /**
     * Check if a setting exists.
     * 
     * @param string $key
     * @param int|null $schoolId
     * @return bool
     */
    public function has(string $key, ?int $schoolId = null): bool
    {
        $query = Setting::where('key', $key);
        
        if ($schoolId) {
            $query->where('school_id', $schoolId);
        } else {
            $query->whereNull('school_id');
        }
        
        return $query->exists();
    }

    /**
     * Clear cache for a setting.
     * 
     * @param string $key
     * @param int|null $schoolId
     * @return void
     */
    public function clearCache(string $key, ?int $schoolId = null): void
    {
        $cacheKey = $this->getCacheKey($key, $schoolId);
        Cache::forget($cacheKey);
    }

    /**
     * Clear all settings cache.
     * 
     * @return void
     */
    public function clearAllCache(): void
    {
        // Get all settings and clear their cache
        $settings = Setting::all();
        
        foreach ($settings as $setting) {
            $this->clearCache($setting->key, $setting->school_id);
        }
    }

    /**
     * Get school settings with defaults.
     * 
     * @param int $schoolId
     * @return array
     */
    public function getSchoolSettings(int $schoolId): array
    {
        $defaults = $this->getAll(null); // Global defaults
        $schoolSettings = $this->getAll($schoolId);
        
        return array_merge($defaults, $schoolSettings);
    }

    /**
     * Initialize default settings.
     * 
     * @return void
     */
    public function initializeDefaults(): void
    {
        $defaults = [
            // General Settings
            ['key' => 'school_name', 'value' => 'Smart School', 'type' => 'string', 'group' => 'general'],
            ['key' => 'school_email', 'value' => 'info@smartschool.com', 'type' => 'string', 'group' => 'general'],
            ['key' => 'school_phone', 'value' => '', 'type' => 'string', 'group' => 'general'],
            ['key' => 'school_address', 'value' => '', 'type' => 'string', 'group' => 'general'],
            ['key' => 'school_logo', 'value' => '', 'type' => 'string', 'group' => 'general'],
            ['key' => 'timezone', 'value' => 'Asia/Kolkata', 'type' => 'string', 'group' => 'general'],
            ['key' => 'date_format', 'value' => 'Y-m-d', 'type' => 'string', 'group' => 'general'],
            ['key' => 'currency', 'value' => 'INR', 'type' => 'string', 'group' => 'general'],
            ['key' => 'currency_symbol', 'value' => '₹', 'type' => 'string', 'group' => 'general'],
            
            // Academic Settings
            ['key' => 'academic_year_start_month', 'value' => '4', 'type' => 'integer', 'group' => 'academic'],
            ['key' => 'grading_system', 'value' => 'percentage', 'type' => 'string', 'group' => 'academic'],
            ['key' => 'attendance_type', 'value' => 'daily', 'type' => 'string', 'group' => 'academic'],
            
            // Fee Settings
            ['key' => 'late_fee_enabled', 'value' => '1', 'type' => 'boolean', 'group' => 'fees'],
            ['key' => 'late_fee_per_day', 'value' => '10', 'type' => 'float', 'group' => 'fees'],
            ['key' => 'online_payment_enabled', 'value' => '0', 'type' => 'boolean', 'group' => 'fees'],
            
            // Library Settings
            ['key' => 'max_books_per_student', 'value' => '3', 'type' => 'integer', 'group' => 'library'],
            ['key' => 'max_books_per_teacher', 'value' => '5', 'type' => 'integer', 'group' => 'library'],
            ['key' => 'book_return_days', 'value' => '14', 'type' => 'integer', 'group' => 'library'],
            ['key' => 'library_fine_per_day', 'value' => '1', 'type' => 'float', 'group' => 'library'],
            
            // Notification Settings
            ['key' => 'email_notifications_enabled', 'value' => '1', 'type' => 'boolean', 'group' => 'notifications'],
            ['key' => 'sms_notifications_enabled', 'value' => '0', 'type' => 'boolean', 'group' => 'notifications'],
        ];
        
        foreach ($defaults as $setting) {
            if (!$this->has($setting['key'])) {
                $this->set(
                    $setting['key'],
                    $setting['value'],
                    $setting['type'],
                    null,
                    $setting['group']
                );
            }
        }
    }

    /**
     * Get cache key for a setting.
     * 
     * @param string $key
     * @param int|null $schoolId
     * @return string
     */
    private function getCacheKey(string $key, ?int $schoolId = null): string
    {
        $suffix = $schoolId ? "school_{$schoolId}" : 'global';
        return self::CACHE_PREFIX . $key . '_' . $suffix;
    }

    /**
     * Cast value to appropriate type.
     * 
     * @param string $value
     * @param string $type
     * @return mixed
     */
    private function castValue(string $value, string $type): mixed
    {
        return match ($type) {
            'integer', 'int' => (int) $value,
            'float', 'double' => (float) $value,
            'boolean', 'bool' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'array', 'json' => json_decode($value, true) ?? [],
            default => $value,
        };
    }

    /**
     * Serialize value for storage.
     * 
     * @param mixed $value
     * @param string $type
     * @return string
     */
    private function serializeValue(mixed $value, string $type): string
    {
        if ($type === 'array' || $type === 'json') {
            return json_encode($value);
        }
        
        if ($type === 'boolean' || $type === 'bool') {
            return $value ? '1' : '0';
        }
        
        return (string) $value;
    }
}
