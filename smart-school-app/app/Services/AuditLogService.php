<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use Illuminate\Database\Eloquent\Model;

/**
 * Audit Log Service
 * 
 * Prompt 451: Create Audit Log Service
 * 
 * Provides comprehensive audit logging for tracking user actions,
 * data changes, and system events for compliance and debugging.
 * 
 * Features:
 * - User action logging
 * - Model change tracking
 * - Login/logout tracking
 * - Data export logging
 * - Search and filter capabilities
 */
class AuditLogService
{
    /**
     * Log a user action.
     *
     * @param string $action
     * @param string $description
     * @param array $data
     * @param User|null $user
     * @return int
     */
    public function log(string $action, string $description, array $data = [], ?User $user = null): int
    {
        $user = $user ?? auth()->user();

        return DB::table('audit_logs')->insertGetId([
            'user_id' => $user?->id,
            'action' => $action,
            'description' => $description,
            'data' => json_encode($data),
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'url' => Request::fullUrl(),
            'method' => Request::method(),
            'created_at' => now(),
        ]);
    }

    /**
     * Log a model creation.
     *
     * @param Model $model
     * @param User|null $user
     * @return int
     */
    public function logCreated(Model $model, ?User $user = null): int
    {
        return $this->log(
            'created',
            "Created " . class_basename($model) . " #{$model->getKey()}",
            [
                'model' => get_class($model),
                'model_id' => $model->getKey(),
                'attributes' => $this->filterSensitiveData($model->getAttributes()),
            ],
            $user
        );
    }

    /**
     * Log a model update.
     *
     * @param Model $model
     * @param array $original
     * @param User|null $user
     * @return int
     */
    public function logUpdated(Model $model, array $original, ?User $user = null): int
    {
        $changes = $model->getChanges();
        
        return $this->log(
            'updated',
            "Updated " . class_basename($model) . " #{$model->getKey()}",
            [
                'model' => get_class($model),
                'model_id' => $model->getKey(),
                'original' => $this->filterSensitiveData($original),
                'changes' => $this->filterSensitiveData($changes),
            ],
            $user
        );
    }

    /**
     * Log a model deletion.
     *
     * @param Model $model
     * @param User|null $user
     * @return int
     */
    public function logDeleted(Model $model, ?User $user = null): int
    {
        return $this->log(
            'deleted',
            "Deleted " . class_basename($model) . " #{$model->getKey()}",
            [
                'model' => get_class($model),
                'model_id' => $model->getKey(),
                'attributes' => $this->filterSensitiveData($model->getAttributes()),
            ],
            $user
        );
    }

    /**
     * Log a user login.
     *
     * @param User $user
     * @return int
     */
    public function logLogin(User $user): int
    {
        return $this->log(
            'login',
            "User logged in: {$user->email}",
            [
                'user_id' => $user->id,
                'email' => $user->email,
            ],
            $user
        );
    }

    /**
     * Log a user logout.
     *
     * @param User $user
     * @return int
     */
    public function logLogout(User $user): int
    {
        return $this->log(
            'logout',
            "User logged out: {$user->email}",
            [
                'user_id' => $user->id,
                'email' => $user->email,
            ],
            $user
        );
    }

    /**
     * Log a failed login attempt.
     *
     * @param string $email
     * @return int
     */
    public function logFailedLogin(string $email): int
    {
        return $this->log(
            'failed_login',
            "Failed login attempt for: {$email}",
            [
                'email' => $email,
            ],
            null
        );
    }

    /**
     * Log a data export.
     *
     * @param string $exportType
     * @param array $filters
     * @param int $recordCount
     * @param User|null $user
     * @return int
     */
    public function logExport(string $exportType, array $filters, int $recordCount, ?User $user = null): int
    {
        return $this->log(
            'export',
            "Exported {$recordCount} {$exportType} records",
            [
                'export_type' => $exportType,
                'filters' => $filters,
                'record_count' => $recordCount,
            ],
            $user
        );
    }

    /**
     * Log a data import.
     *
     * @param string $importType
     * @param int $recordCount
     * @param array $results
     * @param User|null $user
     * @return int
     */
    public function logImport(string $importType, int $recordCount, array $results, ?User $user = null): int
    {
        return $this->log(
            'import',
            "Imported {$recordCount} {$importType} records",
            [
                'import_type' => $importType,
                'record_count' => $recordCount,
                'results' => $results,
            ],
            $user
        );
    }

    /**
     * Log a permission change.
     *
     * @param User $targetUser
     * @param string $action
     * @param array $permissions
     * @param User|null $user
     * @return int
     */
    public function logPermissionChange(User $targetUser, string $action, array $permissions, ?User $user = null): int
    {
        return $this->log(
            'permission_change',
            "{$action} permissions for user: {$targetUser->email}",
            [
                'target_user_id' => $targetUser->id,
                'target_user_email' => $targetUser->email,
                'action' => $action,
                'permissions' => $permissions,
            ],
            $user
        );
    }

    /**
     * Log a settings change.
     *
     * @param string $settingKey
     * @param mixed $oldValue
     * @param mixed $newValue
     * @param User|null $user
     * @return int
     */
    public function logSettingChange(string $settingKey, $oldValue, $newValue, ?User $user = null): int
    {
        return $this->log(
            'setting_change',
            "Changed setting: {$settingKey}",
            [
                'setting_key' => $settingKey,
                'old_value' => $oldValue,
                'new_value' => $newValue,
            ],
            $user
        );
    }

    /**
     * Get audit logs with filters.
     *
     * @param array $filters
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getLogs(array $filters = [], int $perPage = 50)
    {
        $query = DB::table('audit_logs')
            ->orderBy('created_at', 'desc');

        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (isset($filters['action'])) {
            $query->where('action', $filters['action']);
        }

        if (isset($filters['start_date'])) {
            $query->whereDate('created_at', '>=', $filters['start_date']);
        }

        if (isset($filters['end_date'])) {
            $query->whereDate('created_at', '<=', $filters['end_date']);
        }

        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('description', 'like', "%{$filters['search']}%")
                  ->orWhere('data', 'like', "%{$filters['search']}%");
            });
        }

        if (isset($filters['ip_address'])) {
            $query->where('ip_address', $filters['ip_address']);
        }

        return $query->paginate($perPage);
    }

    /**
     * Get logs for a specific user.
     *
     * @param int $userId
     * @param int $limit
     * @return \Illuminate\Support\Collection
     */
    public function getUserLogs(int $userId, int $limit = 100)
    {
        return DB::table('audit_logs')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get logs for a specific model.
     *
     * @param string $modelClass
     * @param int $modelId
     * @return \Illuminate\Support\Collection
     */
    public function getModelLogs(string $modelClass, int $modelId)
    {
        return DB::table('audit_logs')
            ->where('data', 'like', '%"model":"' . addslashes($modelClass) . '"%')
            ->where('data', 'like', '%"model_id":' . $modelId . '%')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get available actions.
     *
     * @return array
     */
    public function getAvailableActions(): array
    {
        return DB::table('audit_logs')
            ->distinct()
            ->pluck('action')
            ->toArray();
    }

    /**
     * Get audit statistics.
     *
     * @param string|null $startDate
     * @param string|null $endDate
     * @return array
     */
    public function getStatistics(?string $startDate = null, ?string $endDate = null): array
    {
        $query = DB::table('audit_logs');

        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        $total = (clone $query)->count();
        
        $byAction = (clone $query)
            ->select('action', DB::raw('count(*) as count'))
            ->groupBy('action')
            ->pluck('count', 'action')
            ->toArray();

        $byUser = (clone $query)
            ->select('user_id', DB::raw('count(*) as count'))
            ->whereNotNull('user_id')
            ->groupBy('user_id')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        $recentActivity = (clone $query)
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->limit(30)
            ->pluck('count', 'date')
            ->toArray();

        return [
            'total' => $total,
            'by_action' => $byAction,
            'top_users' => $byUser,
            'recent_activity' => $recentActivity,
        ];
    }

    /**
     * Cleanup old audit logs.
     *
     * @param int $daysOld
     * @return int
     */
    public function cleanup(int $daysOld = 90): int
    {
        $deleted = DB::table('audit_logs')
            ->where('created_at', '<', now()->subDays($daysOld))
            ->delete();

        Log::info('Audit logs cleaned up', [
            'deleted' => $deleted,
            'days_old' => $daysOld,
        ]);

        return $deleted;
    }

    /**
     * Filter sensitive data from attributes.
     *
     * @param array $data
     * @return array
     */
    protected function filterSensitiveData(array $data): array
    {
        $sensitiveFields = [
            'password',
            'password_hash',
            'remember_token',
            'api_token',
            'secret',
            'credit_card',
            'cvv',
            'ssn',
        ];

        foreach ($sensitiveFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = '[REDACTED]';
            }
        }

        return $data;
    }
}
