<?php

namespace App\Livewire\Products;

use App\Models\Product;
use App\Services\LinnworksApiService;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class Show extends Component
{
    public Product $product;

    // Stock History Modal Properties
    public $stockHistory = null;
    public $isLoadingHistory = false;
    public $errorMessage = null;
    public $showHistoryModal = false;
    public $historyCurrentPage = 1;
    public $historyTotalPages = 1;
    public $historyTotalEntries = 0;


    public function mount(Product $product)
    {
        $this->product = $product;
    }


    /**
     * Show stock history for this product
     */
    public function showStockHistory()
    {
        Log::info("showStockHistory method called for product: {$this->product->sku}");
        $this->showHistoryModal = true;
        Log::info("Modal flag set to: " . ($this->showHistoryModal ? 'true' : 'false'));
        $this->getStockItemHistory();
    }

    /**
     * Get stock item history for the product
     */
    public function getStockItemHistory($page = 1)
    {
        $this->isLoadingHistory = true;
        $this->errorMessage = null;
        
        // Only clear history if loading first page
        if ($page === 1) {
            $this->stockHistory = null;
        }

        try {
            Log::info("Fetching stock history for SKU: {$this->product->sku}, Page: {$page}");

            // Get the stock history from the Linnworks API
            $linnworksService = app(LinnworksApiService::class);
            $history = $linnworksService->getStockItemHistory($this->product->sku, $page, 20);

            // Extract pagination info and data
            $this->historyCurrentPage = $history['PageNumber'] ?? 1;
            $this->historyTotalPages = $history['TotalPages'] ?? 1;
            $this->historyTotalEntries = $history['TotalEntries'] ?? 0;
            $this->stockHistory = $history['Data'] ?? [];

            Log::info("Retrieved stock history for SKU: {$this->product->sku} - Page {$this->historyCurrentPage} of {$this->historyTotalPages}");
        } catch (\Exception $e) {
            Log::error("Failed to get stock history for SKU: {$this->product->sku} - ".$e->getMessage());
            $this->errorMessage = 'Failed to load stock history: '.$e->getMessage();
        } finally {
            $this->isLoadingHistory = false;
        }
    }

    /**
     * Navigate to previous page of stock history
     */
    public function previousHistoryPage()
    {
        if ($this->historyCurrentPage > 1) {
            $this->getStockItemHistory($this->historyCurrentPage - 1);
        }
    }

    /**
     * Navigate to next page of stock history
     */
    public function nextHistoryPage()
    {
        if ($this->historyCurrentPage < $this->historyTotalPages) {
            $this->getStockItemHistory($this->historyCurrentPage + 1);
        }
    }

    /**
     * Close the stock history modal
     */
    public function closeHistoryModal()
    {
        $this->showHistoryModal = false;
        $this->stockHistory = null;
        $this->errorMessage = null;
        $this->historyCurrentPage = 1;
        $this->historyTotalPages = 1;
        $this->historyTotalEntries = 0;
    }

    public function render()
    {
        // Get recent scans for this product
        $recentScans = $this->product->scans()
            ->with('user')
            ->latest()
            ->limit(10)
            ->get();

        return view('livewire.products.show', [
            'recentScans' => $recentScans,
        ]);
    }
}
