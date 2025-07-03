<?php

namespace App\Livewire;

use App\Actions\GetProductFromScannedBarcode;
use App\Models\Product;
use Illuminate\Support\Collection;
use Livewire\Component;

class SmartProductSelector extends Component
{
    public string $search = '';
    
    public string $selectedProductId = '';
    
    public string $placeholder = 'Search by SKU, name, or barcode...';
    
    public bool $showDropdown = false;
    
    public bool $required = false;
    
    public string $label = 'Product';
    
    public string $errorMessage = '';
    
    public bool $showBarcodeScanner = false;
    
    // Component properties
    public Collection $searchResults;
    
    public Collection $recentProducts;
    
    public ?Product $selectedProduct = null;
    
    public function mount()
    {
        $this->searchResults = collect();
        $this->recentProducts = collect();
        $this->loadRecentProducts();
    }
    
    public function updatedSearch()
    {
        if (strlen($this->search) >= 2) {
            $this->searchProducts();
            $this->showDropdown = true;
        } else {
            $this->searchResults = collect();
            $this->showDropdown = false;
        }
    }
    
    public function searchProducts()
    {
        // Search by SKU, name, and barcodes
        $products = Product::where('sku', 'like', '%' . $this->search . '%')
            ->orWhere('name', 'like', '%' . $this->search . '%')
            ->orWhere('barcode', 'like', '%' . $this->search . '%')
            ->orWhere('barcode_2', 'like', '%' . $this->search . '%')
            ->orWhere('barcode_3', 'like', '%' . $this->search . '%')
            ->orderBy('name')
            ->limit(10)
            ->get();
        
        $this->searchResults = $products;
        
        // If search looks like a barcode, try barcode lookup
        if (strlen($this->search) >= 8 && is_numeric($this->search)) {
            $barcodeProduct = (new GetProductFromScannedBarcode($this->search))->handle();
            if ($barcodeProduct && !$this->searchResults->contains('id', $barcodeProduct->id)) {
                $this->searchResults->prepend($barcodeProduct);
            }
        }
    }
    
    public function selectProduct($productId)
    {
        $product = Product::find($productId);
        
        if ($product) {
            $this->selectedProduct = $product;
            $this->selectedProductId = $productId;
            $this->search = $product->sku . ' - ' . $product->name;
            $this->showDropdown = false;
            $this->errorMessage = '';
            
            // Add to recent products
            $this->addToRecentProducts($product);
            
            // Emit event for parent components
            $this->dispatch('productSelected', $productId, $product->sku, $product->name);
        }
    }
    
    public function clearSelection()
    {
        $this->selectedProduct = null;
        $this->selectedProductId = '';
        $this->search = '';
        $this->showDropdown = false;
        $this->dispatch('productCleared');
    }
    
    public function showSuggestions()
    {
        $this->showDropdown = true;
    }
    
    public function hideSuggestions()
    {
        // Delay hiding to allow for clicks
        $this->dispatch('hideDropdown');
    }
    
    public function hideDropdown()
    {
        $this->showDropdown = false;
    }
    
    public function toggleBarcodeScanner()
    {
        $this->showBarcodeScanner = !$this->showBarcodeScanner;
        
        if ($this->showBarcodeScanner) {
            $this->dispatch('initBarcodeScanner');
        } else {
            $this->dispatch('stopBarcodeScanner');
        }
    }
    
    public function processBarcode($barcode)
    {
        $product = (new GetProductFromScannedBarcode($barcode))->handle();
        
        if ($product) {
            $this->selectProduct($product->id);
            $this->showBarcodeScanner = false;
            $this->dispatch('stopBarcodeScanner');
        } else {
            $this->errorMessage = 'Product not found for barcode: ' . $barcode;
        }
    }
    
    protected function loadRecentProducts()
    {
        try {
            // Get recent products from session first, then fall back to database
            $recentProductIds = session()->get('recent_products', []);
            
            if (!empty($recentProductIds) && is_array($recentProductIds)) {
                // Load products from session storage
                $validIds = array_filter(array_map('intval', $recentProductIds));
                if (!empty($validIds)) {
                    // Get products and then sort them manually to maintain order
                    $products = Product::whereIn('id', $validIds)->get();
                    $sortedProducts = collect();
                    foreach ($validIds as $id) {
                        $product = $products->firstWhere('id', $id);
                        if ($product) {
                            $sortedProducts->push($product);
                        }
                    }
                    $this->recentProducts = $sortedProducts->take(5);
                    return;
                }
            }
            
            // Fall back to products from recent scans by the current user
            if (auth()->check()) {
                $this->recentProducts = Product::whereHas('scans', function ($query) {
                    $query->where('user_id', auth()->id());
                })
                ->withCount(['scans' => function ($query) {
                    $query->where('user_id', auth()->id());
                }])
                ->orderBy('scans_count', 'desc')
                ->limit(5)
                ->get();
            } else {
                $this->recentProducts = collect();
            }
        } catch (\Exception $e) {
            // If anything fails, just return empty collection
            $this->recentProducts = collect();
        }
    }
    
    protected function addToRecentProducts(Product $product)
    {
        // Store in session for this user
        $recentProducts = session()->get('recent_products', []);
        
        // Remove if already exists
        $recentProducts = array_filter($recentProducts, fn($id) => $id !== $product->id);
        
        // Add to beginning
        array_unshift($recentProducts, $product->id);
        
        // Keep only last 5
        $recentProducts = array_slice($recentProducts, 0, 5);
        
        session()->put('recent_products', $recentProducts);
        
        // Reload recent products
        $this->loadRecentProducts();
    }
    
    public function render()
    {
        return view('livewire.smart-product-selector');
    }
}