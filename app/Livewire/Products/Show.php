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


    public function mount(Product $product)
    {
        $this->product = $product;
    }

    /**
     * Test method to debug Livewire
     */
    public function testLivewire()
    {
        Log::info("TEST: Livewire is working for product: {$this->product->sku}");
        session()->flash('message', 'Livewire is working!');
    }

    /**
     * Show stock history for this product
     */
    public function showStockHistory()
    {
        Log::info("showStockHistory method called for product: {$this->product->sku}");
        $this->showHistoryModal = true;
        $this->getStockItemHistory();
    }

    /**
     * Get stock item history for the product
     */
    public function getStockItemHistory()
    {
        $this->isLoadingHistory = true;
        $this->errorMessage = null;
        $this->stockHistory = null;

        try {
            Log::info("Fetching stock history for SKU: {$this->product->sku}");

            // Get the stock history from the Linnworks API
            $linnworksService = app(LinnworksApiService::class);
            $history = $linnworksService->getStockItemHistory($this->product->sku);

            // Store the history data
            $this->stockHistory = $history;

            Log::info("Retrieved stock history for SKU: {$this->product->sku}");
        } catch (\Exception $e) {
            Log::error("Failed to get stock history for SKU: {$this->product->sku} - ".$e->getMessage());
            $this->errorMessage = 'Failed to load stock history: '.$e->getMessage();
        } finally {
            $this->isLoadingHistory = false;
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
