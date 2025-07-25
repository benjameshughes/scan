<?php

namespace App\Jobs;

use App\Actions\Stock\ProcessStockTransferAction;
use App\Models\StockMovement;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessStockMovement implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public StockMovement $stockMovement;

    /**
     * The number of times the job may be attempted.
     */
    public $tries = 3;

    /**
     * The maximum number of unhandled exceptions to allow before failing.
     */
    public $maxExceptions = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(StockMovement $stockMovement)
    {
        $this->stockMovement = $stockMovement;
    }

    /**
     * Execute the job.
     *
     * @throws \Exception
     */
    public function handle()
    {
        // Refresh stock movement from database to get latest state
        $this->stockMovement->refresh();

        // Skip if already processed
        if ($this->stockMovement->processed_at !== null || $this->stockMovement->sync_status === 'synced') {
            Log::channel('inventory')->info('Skipping already processed stock movement '.$this->stockMovement->id);

            return;
        }

        try {
            // Update status to processing
            $this->stockMovement->update([
                'sync_status' => 'processing',
                'sync_attempts' => $this->stockMovement->sync_attempts + 1,
                'last_sync_attempt_at' => now(),
            ]);

            // Execute the stock transfer via Linnworks
            $processStockTransferAction = app(ProcessStockTransferAction::class);
            $result = $processStockTransferAction->handle(
                $this->stockMovement->product,
                $this->stockMovement->from_location_id,
                $this->stockMovement->to_location_id ?? config('linnworks.default_location_id'),
                $this->stockMovement->quantity,
                $this->stockMovement->notes ?? "Stock movement #{$this->stockMovement->id}"
            );

            if (! $result['success']) {
                throw new \Exception($result['message']);
            }

            // Mark as successfully processed
            $this->stockMovement->update([
                'sync_status' => 'synced',
                'processed_at' => now(),
                'sync_error_message' => null,
                'sync_error_type' => null,
                'metadata' => array_merge($this->stockMovement->metadata ?? [], [
                    'linnworks_result' => $result['linnworks_result'] ?? null,
                    'processed_by_job' => true,
                    'job_completed_at' => now()->toISOString(),
                ]),
            ]);

            Log::channel('inventory')->info('Successfully processed stock movement '.$this->stockMovement->id.' on attempt '.$this->stockMovement->sync_attempts, [
                'type' => $this->stockMovement->type,
                'product_sku' => $this->stockMovement->product->sku,
                'quantity' => $this->stockMovement->quantity,
                'from_location' => $this->stockMovement->from_location_code,
                'to_location' => $this->stockMovement->to_location_code,
            ]);

        } catch (\Throwable $exception) {
            // Categorize the error type
            $errorType = $this->categorizeError($exception);

            // Update with error details
            $this->stockMovement->update([
                'sync_status' => 'failed',
                'sync_error_message' => $exception->getMessage(),
                'sync_error_type' => $errorType,
                'sync_attempts' => $this->stockMovement->sync_attempts,
                'last_sync_attempt_at' => now(),
                'metadata' => array_merge($this->stockMovement->metadata ?? [], [
                    'error_details' => [
                        'exception_class' => get_class($exception),
                        'error_message' => $exception->getMessage(),
                        'failed_at' => now()->toISOString(),
                        'attempt_number' => $this->stockMovement->sync_attempts,
                    ],
                ]),
            ]);

            Log::channel('inventory')->error('Stock movement '.$this->stockMovement->id.' failed on attempt '.$this->stockMovement->sync_attempts, [
                'error_type' => $errorType,
                'error_message' => $exception->getMessage(),
                'product_sku' => $this->stockMovement->product->sku,
                'type' => $this->stockMovement->type,
            ]);

            // Fire event to notify relevant users about the failure
            event(new \App\Events\RefillOperationFailed(
                $this->stockMovement,
                $exception->getMessage(),
                $errorType
            ));

            // Re-throw the exception to let Laravel handle retries
            throw $exception;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception)
    {
        // Refresh stock movement to get latest state
        $this->stockMovement->refresh();

        // Log the final failure
        Log::channel('inventory')->error('Job permanently failed for stock movement '.$this->stockMovement->id, [
            'exception' => get_class($exception),
            'message' => $exception->getMessage(),
            'final_attempt' => $this->stockMovement->sync_attempts,
            'type' => $this->stockMovement->type,
            'product_sku' => $this->stockMovement->product->sku,
        ]);

        // Mark as permanently failed
        $this->stockMovement->update([
            'sync_status' => 'failed',
            'sync_error_message' => 'Job permanently failed: '.$exception->getMessage(),
            'sync_error_type' => 'job_failure',
            'metadata' => array_merge($this->stockMovement->metadata ?? [], [
                'job_failed' => true,
                'final_exception' => get_class($exception),
                'failed_permanently_at' => now()->toISOString(),
            ]),
        ]);
    }

    /**
     * Categorize error types for better handling
     */
    private function categorizeError(\Throwable $exception): string
    {
        $message = strtolower($exception->getMessage());

        if (str_contains($message, 'network') || str_contains($message, 'connection')) {
            return 'network_error';
        }

        if (str_contains($message, 'timeout')) {
            return 'timeout_error';
        }

        if (str_contains($message, 'authentication') || str_contains($message, 'unauthorized')) {
            return 'auth_error';
        }

        if (str_contains($message, 'not found') || str_contains($message, '404')) {
            return 'not_found_error';
        }

        if (str_contains($message, 'insufficient stock') || str_contains($message, 'stock level')) {
            return 'stock_error';
        }

        return 'general_error';
    }

    /**
     * Get the tags that should be assigned to the job.
     */
    public function tags()
    {
        return [
            'stock-movement',
            'movement:'.$this->stockMovement->id,
            'type:'.$this->stockMovement->type,
            'product:'.$this->stockMovement->product->sku,
        ];
    }
}
