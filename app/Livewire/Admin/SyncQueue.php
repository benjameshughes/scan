<?php

namespace App\Livewire\Admin;

use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Livewire\Component;
use Livewire\WithPagination;

class SyncQueue extends Component
{
    use WithPagination;

    public $queueStats = [];

    public $pendingJobs = [];

    public $failedJobs = [];

    public $showFailedDetails = [];

    public $refreshing = false;

    public $processing = false;

    protected $paginationTheme = 'simple';

    public function mount()
    {
        // Check if user has permission to manage products
        if (! auth()->user()->can('manage products')) {
            abort(403, 'You do not have permission to access the sync queue.');
        }

        $this->loadQueueData();
    }

    public function loadQueueData()
    {
        $this->refreshing = true;

        $this->queueStats = $this->getQueueStats();
        $this->pendingJobs = $this->getPendingJobs();
        $this->failedJobs = $this->getFailedJobs();

        $this->refreshing = false;
    }

    public function refreshQueue()
    {
        $this->loadQueueData();
        $this->dispatch('queue-refreshed');
    }

    public function retryJob($failedJobId)
    {
        try {
            Artisan::call('queue:retry', ['id' => $failedJobId]);
            session()->flash('success', 'Job queued for retry.');
            $this->loadQueueData();
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to retry job: '.$e->getMessage());
        }
    }

    public function retryAllFailed()
    {
        $this->processing = true;

        try {
            Artisan::call('queue:retry', ['id' => 'all']);
            session()->flash('success', 'All failed jobs queued for retry.');
            $this->loadQueueData();
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to retry jobs: '.$e->getMessage());
        }

        $this->processing = false;
    }

    public function deleteFailedJob($failedJobId)
    {
        try {
            Artisan::call('queue:forget', ['id' => $failedJobId]);
            session()->flash('success', 'Failed job deleted.');
            $this->loadQueueData();
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to delete job: '.$e->getMessage());
        }
    }

    public function flushFailedJobs()
    {
        $this->processing = true;

        try {
            Artisan::call('queue:flush');
            session()->flash('success', 'All failed jobs have been deleted.');
            $this->loadQueueData();
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to flush jobs: '.$e->getMessage());
        }

        $this->processing = false;
    }

    public function pauseQueue()
    {
        try {
            // This would require a queue manager like Horizon
            session()->flash('info', 'Queue pause feature requires Horizon or similar queue manager.');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to pause queue: '.$e->getMessage());
        }
    }

    public function toggleFailedJobDetails($jobId)
    {
        if (isset($this->showFailedDetails[$jobId])) {
            unset($this->showFailedDetails[$jobId]);
        } else {
            $this->showFailedDetails[$jobId] = true;
        }
    }

    protected function getQueueStats()
    {
        try {
            $pending = DB::table('jobs')->count();
            $failed = DB::table('failed_jobs')->count();

            // Get job age distribution for pending jobs
            $oldJobs = DB::table('jobs')
                ->where('created_at', '<', Carbon::now()->subHours(1))
                ->count();

            // Get recent failure rate
            $recentFailures = DB::table('failed_jobs')
                ->where('failed_at', '>=', Carbon::now()->subHours(24))
                ->count();

            return [
                'pending_count' => $pending,
                'failed_count' => $failed,
                'old_jobs_count' => $oldJobs,
                'recent_failures' => $recentFailures,
                'queue_healthy' => $pending < 100 && $oldJobs < 10,
            ];
        } catch (\Exception $e) {
            return [
                'pending_count' => 0,
                'failed_count' => 0,
                'old_jobs_count' => 0,
                'recent_failures' => 0,
                'queue_healthy' => true,
                'error' => $e->getMessage(),
            ];
        }
    }

    protected function getPendingJobs()
    {
        try {
            return DB::table('jobs')
                ->select([
                    'id',
                    'queue',
                    'payload',
                    'attempts',
                    'created_at',
                    'available_at',
                ])
                ->orderBy('created_at', 'asc')
                ->limit(50)
                ->get()
                ->map(function ($job) {
                    $payload = json_decode($job->payload, true);

                    return [
                        'id' => $job->id,
                        'queue' => $job->queue,
                        'job_class' => $payload['displayName'] ?? 'Unknown Job',
                        'attempts' => $job->attempts,
                        'created_at' => Carbon::parse($job->created_at),
                        'available_at' => Carbon::parse($job->available_at),
                        'age_minutes' => Carbon::parse($job->created_at)->diffInMinutes(now()),
                        'is_delayed' => Carbon::parse($job->available_at)->isFuture(),
                    ];
                });
        } catch (\Exception $e) {
            return collect([]);
        }
    }

    protected function getFailedJobs()
    {
        try {
            return DB::table('failed_jobs')
                ->select([
                    'id',
                    'uuid',
                    'connection',
                    'queue',
                    'payload',
                    'exception',
                    'failed_at',
                ])
                ->orderBy('failed_at', 'desc')
                ->limit(100)
                ->get()
                ->map(function ($job) {
                    $payload = json_decode($job->payload, true);
                    $exception = $job->exception;

                    // Extract error type from exception
                    $errorType = 'Unknown Error';
                    if (str_contains($exception, 'GuzzleHttp')) {
                        $errorType = 'HTTP/Network Error';
                    } elseif (str_contains($exception, 'AuthenticationException')) {
                        $errorType = 'Authentication Error';
                    } elseif (str_contains($exception, 'ValidationException')) {
                        $errorType = 'Validation Error';
                    } elseif (str_contains($exception, 'TimeoutException')) {
                        $errorType = 'Timeout Error';
                    }

                    // Extract first line of error message
                    $errorLines = explode("\n", $exception);
                    $errorMessage = $errorLines[0] ?? 'No error message';

                    return [
                        'id' => $job->id,
                        'uuid' => $job->uuid,
                        'queue' => $job->queue,
                        'job_class' => $payload['displayName'] ?? 'Unknown Job',
                        'error_type' => $errorType,
                        'error_message' => $errorMessage,
                        'full_exception' => $exception,
                        'failed_at' => Carbon::parse($job->failed_at),
                        'payload' => $payload,
                    ];
                });
        } catch (\Exception $e) {
            return collect([]);
        }
    }

    public function render()
    {
        return view('livewire.admin.sync-queue')
            ->layout('layouts.app')
            ->title('Sync Queue Management');
    }
}
