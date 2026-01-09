<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * Module Access Middleware
 * 
 * Prompt 310: Create Module Access Middleware
 * 
 * Guards module access based on system settings.
 * Disables modules that are turned off in settings.
 */
class ModuleAccessMiddleware
{
    /**
     * Cache key prefix for module settings.
     */
    protected const CACHE_PREFIX = 'module_enabled_';

    /**
     * Cache TTL in seconds (5 minutes).
     */
    protected const CACHE_TTL = 300;

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $module  The module name to check
     */
    public function handle(Request $request, Closure $next, string $module): Response
    {
        if (!$this->isModuleEnabled($module)) {
            return $this->moduleDisabled($request, $module);
        }

        return $next($request);
    }

    /**
     * Check if a module is enabled.
     */
    protected function isModuleEnabled(string $module): bool
    {
        return Cache::remember(
            self::CACHE_PREFIX . $module,
            self::CACHE_TTL,
            function () use ($module) {
                $setting = \App\Models\Setting::where('key', "module_{$module}_enabled")->first();
                
                if (!$setting) {
                    return true;
                }

                return filter_var($setting->value, FILTER_VALIDATE_BOOLEAN);
            }
        );
    }

    /**
     * Return module disabled response.
     */
    protected function moduleDisabled(Request $request, string $module): Response
    {
        $message = "The {$module} module is currently disabled.";

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => false,
                'message' => $message,
                'error' => 'module_disabled',
                'module' => $module,
            ], 403);
        }

        return response()->view('errors.module-disabled', [
            'module' => $module,
            'message' => $message,
        ], 403);
    }

    /**
     * Clear module cache.
     */
    public static function clearCache(string $module = null): void
    {
        if ($module) {
            Cache::forget(self::CACHE_PREFIX . $module);
        } else {
            $modules = ['students', 'attendance', 'exams', 'fees', 'library', 'transport', 'hostel', 'communication'];
            foreach ($modules as $mod) {
                Cache::forget(self::CACHE_PREFIX . $mod);
            }
        }
    }
}
