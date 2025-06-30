<?php

use App\Livewire\Dashboard;
use App\Livewire\Dashboard\FailedScanList;
use App\Models\Scan;
use App\Models\User;
use Livewire\Livewire;

describe('Pagination System', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    });

    describe('Dashboard Failed Scans Simple Pagination', function () {
        it('shows correct failed scan filtering', function () {
            // Create mix of scans
            $failedScan = Scan::factory()->create(['sync_status' => 'failed']);
            $pendingScan = Scan::factory()->create(['submitted_at' => null]);
            $completedScan = Scan::factory()->create(['submitted_at' => now(), 'sync_status' => 'completed']);

            $component = Livewire::test(Dashboard::class);
            
            // Should see failed and pending scans
            $scans = $component->viewData('scans');
            expect($scans->pluck('id')->toArray())
                ->toContain($failedScan->id)
                ->toContain($pendingScan->id)
                ->not->toContain($completedScan->id);
        });

        it('paginates failed scans when there are many', function () {
            // Create enough failed scans for multiple pages (Dashboard shows 5 per page)
            Scan::factory()->count(12)->create([
                'submitted_at' => null,
                'sync_status' => 'pending'
            ]);

            $component = Livewire::test(Dashboard::class);
            $scans = $component->viewData('scans');
            
            // Should have pagination
            expect($scans->hasPages())->toBeTrue();
            expect($scans->total())->toBe(12);
            expect($scans->perPage())->toBe(5);
        });
    });

    describe('Failed Scan List Component', function () {
        it('only shows scans without submitted_at or with failed status', function () {
            $failedScan = Scan::factory()->create(['sync_status' => 'failed']);
            $unsubmittedScan = Scan::factory()->create(['submitted_at' => null]);
            $completedScan = Scan::factory()->create(['submitted_at' => now()]);

            $component = Livewire::test(FailedScanList::class);
            $scans = $component->viewData('scans');
            
            expect($scans->pluck('id')->toArray())
                ->toContain($failedScan->id)
                ->toContain($unsubmittedScan->id)
                ->not->toContain($completedScan->id);
        });

        it('paginates when there are many failed scans', function () {
            Scan::factory()->count(25)->create(['submitted_at' => null]);

            $component = Livewire::test(FailedScanList::class);
            $scans = $component->viewData('scans');
            
            // Should have pagination
            expect($scans->hasPages())->toBeTrue();
            expect($scans->total())->toBe(25);
            expect($scans->perPage())->toBe(10);
        });
    });

    describe('Pagination Views', function () {
        it('renders simple pagination view with multiple pages', function () {
            Scan::factory()->count(20)->create();
            $paginated = Scan::query()->paginate(5);
            
            // Only test if pagination actually renders (multiple pages)
            if ($paginated->hasPages()) {
                $view = view('pagination.simple', ['paginator' => $paginated]);
                $html = $view->render();
                
                expect($html)
                    ->toContain('Previous')
                    ->toContain('Next')
                    ->toContain('role="navigation"')
                    ->toContain('aria-label="Simple Pagination Navigation"');
            } else {
                // If no pagination needed, view should be empty
                $view = view('pagination.simple', ['paginator' => $paginated]);
                $html = $view->render();
                expect($html)->toBe('');
            }
        });

        it('simple pagination shows page numbers correctly', function () {
            Scan::factory()->count(20)->create();
            $paginated = Scan::query()->paginate(5);
            
            if ($paginated->hasPages()) {
                $view = view('pagination.simple', ['paginator' => $paginated]);
                $html = $view->render();
                
                expect($html)
                    ->toContain('Page')
                    ->toContain('of')
                    ->toContain($paginated->currentPage())
                    ->toContain($paginated->lastPage());
            }
        });

        it('simple pagination handles single page correctly', function () {
            Scan::factory()->count(3)->create();
            $paginated = Scan::query()->paginate(10); // Single page
            
            $view = view('pagination.simple', ['paginator' => $paginated]);
            $html = $view->render();
            
            // Should be minimal when only one page (just blade comments)
            expect(trim(strip_tags($html)))->toBe('');
        });

        it('simple pagination includes dark mode classes when paginated', function () {
            Scan::factory()->count(15)->create();
            $paginated = Scan::query()->paginate(5);
            
            if ($paginated->hasPages()) {
                $view = view('pagination.simple', ['paginator' => $paginated]);
                $html = $view->render();
                
                expect($html)
                    ->toContain('dark:bg-zinc-800')
                    ->toContain('dark:text-gray-200')
                    ->toContain('dark:border-zinc-600');
            }
        });
    });

    describe('Pagination Configuration', function () {
        it('uses simple pagination as default for simplePaginate', function () {
            // Test that the AppServiceProvider configuration is working
            expect(\Illuminate\Pagination\Paginator::$defaultSimpleView)->toBe('pagination.simple');
        });

        it('uses custom pagination as default for paginate', function () {
            // Test that the AppServiceProvider configuration is working
            expect(\Illuminate\Pagination\Paginator::$defaultView)->toBe('pagination.custom');
        });
    });
});