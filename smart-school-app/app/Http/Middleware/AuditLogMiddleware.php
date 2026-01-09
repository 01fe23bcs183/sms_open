<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Audit Log Middleware
 * 
 * Prompt 318: Create Audit Log Middleware
 * 
 * Captures user actions for compliance.
 * Logs request metadata for key actions.
 */
class AuditLogMiddleware
{
    /**
     * Sensitive fields to exclude from logging.
     */
    protected array $sensitiveFields = [
        'password',
        'password_confirmation',
        'current_password',
        'new_password',
        'token',
        'api_token',
        'secret',
        'credit_card',
        'card_number',
        'cvv',
        'pin',
    ];

    /**
     * HTTP methods to log.
     */
    protected array $logMethods = [
        'POST',
        'PUT',
        'PATCH',
        'DELETE',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($this->shouldLog($request)) {
            $this->logRequest($request, $response);
        }

        return $response;
    }

    /**
     * Determine if request should be logged.
     */
    protected function shouldLog(Request $request): bool
    {
        if (!in_array($request->method(), $this->logMethods)) {
            return false;
        }

        if ($request->is('api/*/health', 'api/*/ping', 'sanctum/*')) {
            return false;
        }

        return true;
    }

    /**
     * Log the request.
     */
    protected function logRequest(Request $request, Response $response): void
    {
        $user = $request->user();

        $logData = [
            'timestamp' => now()->toIso8601String(),
            'user_id' => $user?->id,
            'user_email' => $user?->email,
            'user_role' => $user?->roles->pluck('name')->first(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'route' => $request->route()?->getName(),
            'route_action' => $request->route()?->getActionName(),
            'payload' => $this->sanitizePayload($request->all()),
            'response_status' => $response->getStatusCode(),
            'session_id' => session()->getId(),
        ];

        if ($this->shouldStoreInDatabase()) {
            $this->storeInDatabase($logData);
        }

        Log::channel($this->getLogChannel())->info('Audit Log', $logData);
    }

    /**
     * Sanitize payload by removing sensitive fields.
     */
    protected function sanitizePayload(array $payload): array
    {
        foreach ($this->sensitiveFields as $field) {
            if (isset($payload[$field])) {
                $payload[$field] = '[REDACTED]';
            }
        }

        array_walk_recursive($payload, function (&$value, $key) {
            if (in_array(strtolower($key), $this->sensitiveFields)) {
                $value = '[REDACTED]';
            }
        });

        return $payload;
    }

    /**
     * Check if should store in database.
     */
    protected function shouldStoreInDatabase(): bool
    {
        return config('audit.store_in_database', false);
    }

    /**
     * Store audit log in database.
     */
    protected function storeInDatabase(array $logData): void
    {
        try {
            \DB::table('audit_logs')->insert([
                'user_id' => $logData['user_id'],
                'action' => $logData['method'] . ' ' . $logData['route'],
                'ip_address' => $logData['ip_address'],
                'user_agent' => $logData['user_agent'],
                'url' => $logData['url'],
                'payload' => json_encode($logData['payload']),
                'response_status' => $logData['response_status'],
                'created_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to store audit log in database', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get the log channel.
     */
    protected function getLogChannel(): string
    {
        return config('audit.log_channel', 'daily');
    }
}
