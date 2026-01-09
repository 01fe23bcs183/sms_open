<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\EmailLog;
use App\Notifications\Channels\EmailChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Send Email Job
 * 
 * Prompt 439: Create Email Queue Job
 * 
 * Queued job for sending email notifications in the background.
 * Supports retry logic, failure handling, and delivery tracking.
 * 
 * Features:
 * - Background email delivery
 * - Automatic retry on failure
 * - Delivery status logging
 * - Template-based content
 * - Attachment support
 */
class SendEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected User $user;
    protected string $type;
    protected array $data;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var int
     */
    public $backoff = 60;

    /**
     * Create a new job instance.
     *
     * @param User $user
     * @param string $type
     * @param array $data
     */
    public function __construct(User $user, string $type, array $data = [])
    {
        $this->user = $user;
        $this->type = $type;
        $this->data = $data;
        $this->onQueue('emails');
    }

    /**
     * Execute the job.
     *
     * @param EmailChannel $emailChannel
     * @return void
     */
    public function handle(EmailChannel $emailChannel): void
    {
        Log::info('Processing email job', [
            'user_id' => $this->user->id,
            'type' => $this->type,
            'attempt' => $this->attempts(),
        ]);

        $result = $emailChannel->send($this->user, $this->type, $this->data);

        if (!$result['success']) {
            Log::warning('Email job failed', [
                'user_id' => $this->user->id,
                'type' => $this->type,
                'error' => $result['error'] ?? 'Unknown error',
            ]);

            // Throw exception to trigger retry
            throw new \Exception($result['error'] ?? 'Email sending failed');
        }

        Log::info('Email job completed', [
            'user_id' => $this->user->id,
            'type' => $this->type,
            'log_id' => $result['log_id'] ?? null,
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
        Log::error('Email job permanently failed', [
            'user_id' => $this->user->id,
            'type' => $this->type,
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts(),
        ]);

        // Update log entry if exists
        if (isset($this->data['log_id'])) {
            EmailLog::where('id', $this->data['log_id'])
                ->update([
                    'status' => 'failed',
                    'error_message' => $exception->getMessage(),
                ]);
        }
    }

    /**
     * Get the tags that should be assigned to the job.
     *
     * @return array
     */
    public function tags(): array
    {
        return ['email', 'notification', 'user:' . $this->user->id];
    }

    /**
     * Determine the time at which the job should timeout.
     *
     * @return \DateTime
     */
    public function retryUntil(): \DateTime
    {
        return now()->addMinutes(30);
    }

    /**
     * Calculate the number of seconds to wait before retrying the job.
     *
     * @return array
     */
    public function backoff(): array
    {
        return [60, 300, 900]; // 1 min, 5 min, 15 min
    }
}
