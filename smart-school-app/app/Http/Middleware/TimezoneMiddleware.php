<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * Timezone Middleware
 * 
 * Prompt 314: Create Timezone Middleware
 * 
 * Sets timezone for date/time operations.
 * Applies school or user timezone settings.
 */
class TimezoneMiddleware
{
    /**
     * Cache key for school timezone.
     */
    protected const CACHE_KEY = 'school_timezone';

    /**
     * Cache TTL in seconds (1 hour).
     */
    protected const CACHE_TTL = 3600;

    /**
     * Default timezone.
     */
    protected const DEFAULT_TIMEZONE = 'UTC';

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $timezone = $this->determineTimezone($request);

        if ($this->isValidTimezone($timezone)) {
            date_default_timezone_set($timezone);
            config(['app.timezone' => $timezone]);
        }

        view()->share('currentTimezone', $timezone);
        view()->share('timezoneOffset', $this->getTimezoneOffset($timezone));

        return $next($request);
    }

    /**
     * Determine the timezone for the request.
     */
    protected function determineTimezone(Request $request): string
    {
        $user = $request->user();
        if ($user && !empty($user->timezone)) {
            return $user->timezone;
        }

        return $this->getSchoolTimezone();
    }

    /**
     * Get school timezone from settings.
     */
    protected function getSchoolTimezone(): string
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            $setting = \App\Models\Setting::where('key', 'school_timezone')->first();

            if ($setting && $this->isValidTimezone($setting->value)) {
                return $setting->value;
            }

            return config('app.timezone', self::DEFAULT_TIMEZONE);
        });
    }

    /**
     * Check if timezone is valid.
     */
    protected function isValidTimezone(string $timezone): bool
    {
        return in_array($timezone, timezone_identifiers_list());
    }

    /**
     * Get timezone offset in hours.
     */
    protected function getTimezoneOffset(string $timezone): string
    {
        try {
            $tz = new \DateTimeZone($timezone);
            $offset = $tz->getOffset(new \DateTime('now', $tz));
            $hours = $offset / 3600;
            $sign = $hours >= 0 ? '+' : '-';
            return sprintf('UTC%s%02d:00', $sign, abs($hours));
        } catch (\Exception $e) {
            return 'UTC+00:00';
        }
    }

    /**
     * Clear timezone cache.
     */
    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Get list of common timezones.
     */
    public static function getCommonTimezones(): array
    {
        return [
            'UTC' => 'UTC (Coordinated Universal Time)',
            'America/New_York' => 'Eastern Time (US & Canada)',
            'America/Chicago' => 'Central Time (US & Canada)',
            'America/Denver' => 'Mountain Time (US & Canada)',
            'America/Los_Angeles' => 'Pacific Time (US & Canada)',
            'Europe/London' => 'London (GMT)',
            'Europe/Paris' => 'Paris (CET)',
            'Europe/Berlin' => 'Berlin (CET)',
            'Asia/Dubai' => 'Dubai (GST)',
            'Asia/Kolkata' => 'India (IST)',
            'Asia/Singapore' => 'Singapore (SGT)',
            'Asia/Tokyo' => 'Tokyo (JST)',
            'Asia/Shanghai' => 'China (CST)',
            'Australia/Sydney' => 'Sydney (AEST)',
        ];
    }
}
