<?php

namespace App\Livewire;

use App\Actions\Dashboard\MarkNotificationAsRead;
use App\Actions\MarkScanAsSubmitted;
use App\Jobs\SyncBarcode;
use App\Models\Scan;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
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
        $userTodayScans = Scan::where('user_id', $user->id)->whereDate('created_at', today())->count();
        $userMonthlyScans = Scan::where('user_id', $user->id)->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])->count();

        // User's ranking and performance metrics
        $userRanking = DB::table('scans')
            ->select('user_id', DB::raw('COUNT(*) as total_scans'))
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('user_id')
            ->orderByDesc('total_scans')
            ->get()
            ->pluck('user_id')
            ->search($user->id);
        
        $userRank = $userRanking !== false ? $userRanking + 1 : null;
        $totalActiveUsers = DB::table('scans')
            ->where('created_at', '>=', now()->subDays(30))
            ->distinct('user_id')
            ->count('user_id');

        // User's daily average over past 30 days
        $userDailyAverage = $userTotalScans > 0 ? round($userTotalScans / max(1, $user->created_at->diffInDays(now())), 1) : 0;

        // User's streak (consecutive days with scans)
        $userStreak = 0;
        $currentDate = now()->startOfDay();
        while ($currentDate->greaterThan($user->created_at)) {
            $dayScans = Scan::where('user_id', $user->id)
                ->whereDate('created_at', $currentDate)
                ->count();
            
            if ($dayScans > 0) {
                $userStreak++;
                $currentDate->subDay();
            } else {
                break;
            }
        }

        // Generate scanning trends for last 7 days
        $scanningTrends = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $count = Scan::whereDate('created_at', $date)->count();
            $scanningTrends[] = [
                'date' => $date->format('M j'),
                'count' => $count,
            ];
        }

        // Top performing users
        $topUsers = DB::table('scans')
            ->join('users', 'scans.user_id', '=', 'users.id')
            ->select('users.name', DB::raw('COUNT(scans.id) as scan_count'))
            ->where('scans.created_at', '>=', now()->subDays(30))
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('scan_count')
            ->limit(5)
            ->get();

        // Recent top scanned products - using barcode relationships
        $topProducts = DB::table('scans')
            ->leftJoin('products', function($join) {
                $join->on('scans.barcode', '=', 'products.barcode')
                     ->orOn('scans.barcode', '=', 'products.barcode_2')
                     ->orOn('scans.barcode', '=', 'products.barcode_3');
            })
            ->select('products.name', 'products.sku', DB::raw('COUNT(scans.id) as scan_count'))
            ->where('scans.created_at', '>=', now()->subDays(7))
            ->whereNotNull('products.id')
            ->groupBy('products.id', 'products.name', 'products.sku')
            ->orderByDesc('scan_count')
            ->limit(5)
            ->get();

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
                'user_today_scans' => $userTodayScans,
                'user_monthly_scans' => $userMonthlyScans,
                'user_rank' => $userRank,
                'total_active_users' => $totalActiveUsers,
                'user_daily_average' => $userDailyAverage,
                'user_streak' => $userStreak,
            ],
            'recent_scans' => $recentScans,
            'scanning_trends' => $scanningTrends,
            'top_users' => $topUsers,
            'top_products' => $topProducts,
        ]);
    }
}
