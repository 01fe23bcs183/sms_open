<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\NotificationService;
use App\Services\NotificationPreferencesService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

/**
 * Send Bulk Notification Job
 * 
 * Prompt 443: Create Bulk Notification Queue Job
 * 
 * Queued job for sending notifications to multiple users in bulk.
 * Handles large recipient lists efficiently with batching.
 * 
 * Features:
 * - Bulk notification delivery
 * - User preference filtering
 * - Progress tracking
 * - Batch processing
 * - Failure handling per recipient
 */
class SendBulkNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $type;
    protected array $data;
    protected array $channels;
    protected array $recipients;
    protected ?string $audience;
    protected array $audienceFilters;
    protected ?int $sentBy;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 2;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 600;

    /**
     * Create a new job instance.
     *
     * @param string $type
     * @param array $data
     * @param array $channels
     * @param array $recipients User IDs or empty for audience-based
     * @param string|null $audience 'all', 'students', 'teachers', 'parents', 'staff'
     * @param array $audienceFilters Additional filters like class_id, section_id
     * @param int|null $sentBy
     */
    public function __construct(
        string $type,
        array $data,
        array $channels = ['database'],
        array $recipients = [],
        ?string $audience = null,
        array $audienceFilters = [],
        ?int $sentBy = null
    ) {
        $this->type = $type;
        $this->data = $data;
        $this->channels = $channels;
        $this->recipients = $recipients;
        $this->audience = $audience;
        $this->audienceFilters = $audienceFilters;
        $this->sentBy = $sentBy;
        $this->onQueue('notifications');
    }

    /**
     * Execute the job.
     *
     * @param NotificationService $notificationService
     * @param NotificationPreferencesService $preferencesService
     * @return void
     */
    public function handle(
        NotificationService $notificationService,
        NotificationPreferencesService $preferencesService
    ): void {
        $startTime = microtime(true);

        Log::info('Starting bulk notification job', [
            'type' => $this->type,
            'channels' => $this->channels,
            'audience' => $this->audience,
            'recipient_count' => count($this->recipients),
        ]);

        try {
            // Get recipients
            $users = $this->getRecipients();

            $sent = 0;
            $failed = 0;
            $skipped = 0;
            $batchSize = 100;

            // Process in batches
            foreach ($users->chunk($batchSize) as $batch) {
                foreach ($batch as $user) {
                    try {
                        // Get channels user wants for this notification type
                        $userChannels = [];
                        foreach ($this->channels as $channel) {
                            if ($preferencesService->shouldNotify($user, $this->type, $channel)) {
                                $userChannels[] = $channel;
                            }
                        }

                        if (empty($userChannels)) {
                            $skipped++;
                            continue;
                        }

                        // Send notification
                        $result = $notificationService->send($user, $this->type, $this->data, $userChannels);

                        // Check if any channel succeeded
                        $anySuccess = false;
                        foreach ($result as $channelResult) {
                            if ($channelResult['success'] ?? false) {
                                $anySuccess = true;
                                break;
                            }
                        }

                        if ($anySuccess) {
                            $sent++;
                        } else {
                            $failed++;
                        }
                    } catch (\Exception $e) {
                        $failed++;
                        Log::warning('Failed to send notification to user', [
                            'user_id' => $user->id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            }

            $duration = round(microtime(true) - $startTime, 2);

            // Store bulk notification record
            $this->storeBulkNotificationRecord($users->count(), $sent, $failed, $skipped, $duration);

            Log::info('Bulk notification job completed', [
                'type' => $this->type,
                'total_recipients' => $users->count(),
                'sent' => $sent,
                'failed' => $failed,
                'skipped' => $skipped,
                'duration_seconds' => $duration,
            ]);

        } catch (\Exception $e) {
            Log::error('Bulk notification job failed', [
                'type' => $this->type,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Get recipients based on configuration.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function getRecipients()
    {
        // If specific recipients provided
        if (!empty($this->recipients)) {
            return User::whereIn('id', $this->recipients)
                ->where('is_active', true)
                ->get();
        }

        // Otherwise, get by audience
        $query = User::where('is_active', true);

        switch ($this->audience) {
            case 'students':
                $query->role('student');
                if (isset($this->audienceFilters['class_id'])) {
                    $query->whereHas('student', function ($q) {
                        $q->where('class_id', $this->audienceFilters['class_id']);
                        if (isset($this->audienceFilters['section_id'])) {
                            $q->where('section_id', $this->audienceFilters['section_id']);
                        }
                    });
                }
                break;

            case 'teachers':
                $query->role('teacher');
                break;

            case 'parents':
                $query->role('parent');
                if (isset($this->audienceFilters['class_id'])) {
                    $query->whereHas('children.student', function ($q) {
                        $q->where('class_id', $this->audienceFilters['class_id']);
                    });
                }
                break;

            case 'staff':
                $query->whereHas('roles', function ($q) {
                    $q->whereIn('name', ['admin', 'accountant', 'librarian']);
                });
                break;

            case 'all':
            default:
                // No additional filters
                break;
        }

        return $query->get();
    }

    /**
     * Store bulk notification record.
     *
     * @param int $total
     * @param int $sent
     * @param int $failed
     * @param int $skipped
     * @param float $duration
     * @return void
     */
    protected function storeBulkNotificationRecord(
        int $total,
        int $sent,
        int $failed,
        int $skipped,
        float $duration
    ): void {
        DB::table('bulk_notifications')->insert([
            'type' => $this->type,
            'data' => json_encode($this->data),
            'channels' => json_encode($this->channels),
            'audience' => $this->audience,
            'audience_filters' => json_encode($this->audienceFilters),
            'total_recipients' => $total,
            'sent_count' => $sent,
            'failed_count' => $failed,
            'skipped_count' => $skipped,
            'sent_by' => $this->sentBy,
            'processing_time' => $duration,
            'created_at' => now(),
        ]);
    }

    /**
     * Handle a job failure.
     *
     * @param \Throwable $exception
     * @return void
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Bulk notification job permanently failed', [
            'type' => $this->type,
            'audience' => $this->audience,
            'error' => $exception->getMessage(),
        ]);
    }

    /**
     * Get the tags that should be assigned to the job.
     *
     * @return array
     */
    public function tags(): array
    {
        return [
            'bulk-notification',
            $this->type,
            'audience:' . ($this->audience ?? 'specific'),
        ];
    }
}
