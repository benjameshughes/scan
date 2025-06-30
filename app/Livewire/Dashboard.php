<?php

namespace App\Livewire;

use App\Actions\Dashboard\MarkNotificationAsRead;
use App\Actions\MarkScanAsSubmitted;
use App\Jobs\SyncBarcode;
use App\Models\Scan;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\WithPagination;

class Dashboard extends Component
{
    use WithPagination;

    public Collection $notifications;

    public int $retryCount = 0;

    // Mark notification as read
    public function markAsRead($id)
    {
        new MarkNotificationAsRead($id)->handle();

        $this->notifications = auth()->user()->unreadNotifications()->get();
        $this->dispatch('notification.markAsRead');
    }

    // Mark all notifications as read
    public function readAll()
    {
        $notifications = auth()->user()->unreadNotifications();

        $notifications->each(function ($notification) {
            $notification->markAsRead();
        });

        $this->dispatch('notification.markAllAsRead');

        $this->notifications = auth()->user()->unreadNotifications()->get();
    }

    /**
     * Redispatch all jobs that have not been submitted
     * Only do the scans with a product model
     * Restricted to admin users only
     */
    public function redispatch()
    {
        // Check if user has admin permissions
        if (!auth()->user()->hasRole('admin')) {
            $this->dispatch('error', 'You do not have permission to perform this action.');
            return 0;
        }

        $this->retryCount = 0;

        // Process in chunks to avoid memory issues
        Scan::whereFalse('submitted')
            ->with('product')
            ->get()
            ->chunk(100)
            ->each(function ($chunk) {
                $chunk->each(function ($scan) {
                    if ($scan->product) {
                        SyncBarcode::dispatch($scan);
                        $this->retryCount++;
                    }
                });
            });

        return $this->retryCount;
    }

    public function markAsSubmitted(int $id)
    {
        $scan = Scan::findOrFail($id);

        // Pass the scan variable to the action
        new MarkScanAsSubmitted($scan)->handle();
    }

    public function mount()
    {
        $this->notifications = auth()->user()->unreadNotifications()->get();
    }

    public function render()
    {
        $user = auth()->user();

        // Get scan statistics
        $totalScans = Scan::count();
        $pendingScans = Scan::where('submitted', false)->count();
        $completedScans = Scan::where('submitted', true)->count();
        $failedScans = Scan::where('sync_status', 'failed')->count();

        // Recent activity
        $recentScans = Scan::with(['user', 'product'])
            ->latest()
            ->limit(10)
            ->get();

        // This week's activity
        $weeklyScans = Scan::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count();
        $todayScans = Scan::whereDate('created_at', today())->count();

        // User's personal stats
        $userScans = Scan::where('user_id', $user->id);
        $userTotalScans = $userScans->count();
        $userWeeklyScans = $userScans->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count();

        return view('livewire.dashboard', [
            'scans' => Scan::where(function ($query) {
                $query->whereNull('submitted_at')
                      ->orWhere('sync_status', 'failed');
            })->orderBy('created_at', 'desc')->paginate(5),
            'stats' => [
                'total_scans' => $totalScans,
                'pending_scans' => $pendingScans,
                'completed_scans' => $completedScans,
                'failed_scans' => $failedScans,
                'weekly_scans' => $weeklyScans,
                'today_scans' => $todayScans,
                'user_total_scans' => $userTotalScans,
                'user_weekly_scans' => $userWeeklyScans,
            ],
            'recent_scans' => $recentScans,
        ]);
    }
}
