<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * School Context Middleware
 * 
 * Prompt 312: Create School Context Middleware
 * 
 * Supports multi-school context in a single deployment.
 * Sets the active school based on domain or header.
 */
class SchoolContextMiddleware
{
    /**
     * Cache key prefix for school settings.
     */
    protected const CACHE_PREFIX = 'school_context_';

    /**
     * Cache TTL in seconds (30 minutes).
     */
    protected const CACHE_TTL = 1800;

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $school = $this->resolveSchool($request);

        if (!$school) {
            return $this->schoolNotFound($request);
        }

        if (!$school['is_active']) {
            return $this->schoolInactive($request, $school);
        }

        $request->attributes->set('school', $school);
        $request->attributes->set('school_id', $school['id']);

        app()->instance('school', (object) $school);

        config([
            'app.name' => $school['name'] ?? config('app.name'),
            'mail.from.name' => $school['name'] ?? config('mail.from.name'),
        ]);

        return $next($request);
    }

    /**
     * Resolve the school from request.
     */
    protected function resolveSchool(Request $request): ?array
    {
        if ($request->hasHeader('X-School-ID')) {
            return $this->getSchoolById($request->header('X-School-ID'));
        }

        $subdomain = $this->getSubdomain($request);
        if ($subdomain) {
            return $this->getSchoolBySubdomain($subdomain);
        }

        return $this->getDefaultSchool();
    }

    /**
     * Get subdomain from request host.
     */
    protected function getSubdomain(Request $request): ?string
    {
        $host = $request->getHost();
        $parts = explode('.', $host);

        if (count($parts) > 2) {
            $subdomain = $parts[0];
            if (!in_array($subdomain, ['www', 'api', 'admin'])) {
                return $subdomain;
            }
        }

        return null;
    }

    /**
     * Get school by ID.
     */
    protected function getSchoolById(string $id): ?array
    {
        return Cache::remember(
            self::CACHE_PREFIX . 'id_' . $id,
            self::CACHE_TTL,
            function () use ($id) {
                $setting = \App\Models\Setting::where('key', 'school_info')->first();
                if ($setting) {
                    $school = json_decode($setting->value, true);
                    if ($school && ($school['id'] ?? null) == $id) {
                        return $school;
                    }
                }
                return $this->getDefaultSchool();
            }
        );
    }

    /**
     * Get school by subdomain.
     */
    protected function getSchoolBySubdomain(string $subdomain): ?array
    {
        return Cache::remember(
            self::CACHE_PREFIX . 'subdomain_' . $subdomain,
            self::CACHE_TTL,
            function () use ($subdomain) {
                $setting = \App\Models\Setting::where('key', 'school_subdomain')
                    ->where('value', $subdomain)
                    ->first();

                if ($setting) {
                    $schoolSetting = \App\Models\Setting::where('key', 'school_info')->first();
                    if ($schoolSetting) {
                        return json_decode($schoolSetting->value, true);
                    }
                }

                return $this->getDefaultSchool();
            }
        );
    }

    /**
     * Get default school settings.
     */
    protected function getDefaultSchool(): ?array
    {
        return Cache::remember(
            self::CACHE_PREFIX . 'default',
            self::CACHE_TTL,
            function () {
                $setting = \App\Models\Setting::where('key', 'school_info')->first();
                if ($setting) {
                    $school = json_decode($setting->value, true);
                    $school['is_active'] = $school['is_active'] ?? true;
                    return $school;
                }

                return [
                    'id' => 1,
                    'name' => config('app.name', 'Smart School'),
                    'code' => 'DEFAULT',
                    'is_active' => true,
                ];
            }
        );
    }

    /**
     * Return school not found response.
     */
    protected function schoolNotFound(Request $request): Response
    {
        $message = 'School not found.';

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => false,
                'message' => $message,
                'error' => 'school_not_found',
            ], 404);
        }

        abort(404, $message);
    }

    /**
     * Return school inactive response.
     */
    protected function schoolInactive(Request $request, array $school): Response
    {
        $message = 'This school is currently inactive.';

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => false,
                'message' => $message,
                'error' => 'school_inactive',
            ], 503);
        }

        return response()->view('errors.school-inactive', [
            'school' => $school,
            'message' => $message,
        ], 503);
    }

    /**
     * Clear school context cache.
     */
    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_PREFIX . 'default');
    }
}
