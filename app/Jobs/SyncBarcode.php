<?php

namespace App\Jobs;

use App\Actions\Concerns\UpdateScanStatus;
use App\Actions\SyncBarcodeAction;
use App\Models\Scan;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncBarcode implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels, UpdateScanStatus;

    public Scan $scan;

    /**
     * The number of times the job may be attempted.
     */
    public $tries = 1; // We handle retries manually for better control

    /**
     * The maximum number of unhandled exceptions to allow before failing.
     */
    public $maxExceptions = 1;

    /**
     * Create a new job instance.
     */
    public function __construct(Scan $scan)
    {
        $this->scan = $scan;
    }

    /**
     * Execute the job.
     *
     * @throws \Exception
     */
    public function handle()
    {
        // Refresh scan from database to get latest state
        $this->scan->refresh();

        // Skip if already processed
        if ($this->scan->submitted || $this->scan->sync_status === 'synced') {
            Log::channel('inventory')->info('Skipping already processed scan '.$this->scan->id);

            return;
        }

        try {
            // Try using the sync barcode action
            $action = new SyncBarcodeAction($this->scan);
            $action->handle();

            Log::channel('inventory')->info('Successfully synced scan '.$this->scan->id.' on attempt '.($this->scan->sync_attempts + 1));

        } catch (\Throwable $exception) {
            // Get the updated scan state after the action processed the error
            $this->scan->refresh();

            // Check if we should retry this job
            if ($this->scan->sync_error_type && $this->shouldRetry($this->scan, $this->scan->sync_error_type)) {
                $delay = $this->getRetryDelay($this->scan->sync_attempts, $this->scan->sync_error_type);

                Log::channel('inventory')->info('Retrying scan '.$this->scan->id.' in '.$delay.' seconds (attempt '.$this->scan->sync_attempts.')', [
                    'error_type' => $this->scan->sync_error_type,
                    'delay_seconds' => $delay,
                ]);

                // Dispatch a new job with delay
                static::dispatch($this->scan)->delay(now()->addSeconds($delay));

                // Don't re-throw the exception as we've handled the retry
                return;
            }

            // Log final failure
            Log::channel('inventory')->error('Scan '.$this->scan->id.' failed permanently after '.$this->scan->sync_attempts.' attempts', [
                'error_type' => $this->scan->sync_error_type,
                'error_message' => $this->scan->sync_error_message,
            ]);

            // Let the job fail normally for non-retryable errors
            throw $exception;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception)
    {
        // Refresh scan to get latest state
        $this->scan->refresh();

        // Log the final failure
        Log::channel('inventory')->error('Job permanently failed for scan '.$this->scan->id, [
            'exception' => get_class($exception),
            'message' => $exception->getMessage(),
            'final_attempt' => $this->scan->sync_attempts,
        ]);

        // Ensure the scan is marked as failed if not already
        if ($this->scan->sync_status !== 'failed') {
            [$errorType, $errorMessage] = $this->categorizeError($exception);
            $this->markScanAsFailed($this->scan, $errorMessage, $errorType, [
                'job_failed' => true,
                'final_exception' => get_class($exception),
            ]);
        }
    }

    /**
     * Get the tags that should be assigned to the job.
     */
    public function tags()
    {
        return ['sync', 'scan:'.$this->scan->id, 'barcode:'.$this->scan->barcode];
    }
}
