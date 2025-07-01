<?php

namespace App\Livewire;

use App\Models\Product;
use App\Services\LinnworksApiService;
use App\Tables\Columns\ActionsColumn;
use App\Tables\Actions\CustomAction;
use App\Tables\Columns\DateColumn;
use App\Tables\Columns\TextColumn;
use App\Tables\Table;
use App\Tables\TableComponent;
use Illuminate\Support\Facades\Log;

class ProductsTable extends TableComponent
{
    protected ?string $model = Product::class;

    protected array $searchable = ['sku', 'name', 'barcode', 'barcode_2', 'barcode_3'];

    protected ?string $title = 'Products Management';

    // Stock History Modal Properties
    public $stockHistory = null;
    public $isLoadingHistory = false;
    public $errorMessage = null;
    public $showHistoryModal = false;
    public $selectedProduct = null;

    protected $linnworksService;

    public function boot(LinnworksApiService $linnworksService)
    {
        $this->linnworksService = $linnworksService;
    }

    public function table(Table $table): Table
    {
        return parent::table($table)
            ->columns([
                TextColumn::make('sku')->label('SKU')->sortable()->searchable(),
                TextColumn::make('name')->label('Name')->sortable()->searchable(),
                TextColumn::make('barcode')->label('Primary Barcode')->searchable(),
                TextColumn::make('barcode_2')->label('Barcode 2'),
                TextColumn::make('barcode_3')->label('Barcode 3'),
                DateColumn::make('updated_at')->label('Last Updated')->diffForHumans()->sortable(),
                ActionsColumn::make('actions')
                    ->edit()
                    ->delete()
                    ->view()
                    ->action(
                        (new CustomAction('Stock History'))
                            ->icon('chart-bar')
                            ->color('purple')
                            ->livewire('showStockHistory')
                    ),
            ])
            ->exportable(['csv', 'excel'])
            ->crud(
                createRoute: 'products.create',
                editRoute: 'products.edit',
                viewRoute: 'products.show',
                deleteAction: 'delete'
            )
            ->bulkActions([
                [
                    'name' => 'delete',
                    'label' => 'Delete Selected',
                    'handle' => function (array $ids) {
                        Product::whereIn('id', $ids)->delete();
                        session()->flash('message', count($ids).' products deleted.');
                    },
                ],
                [
                    'name' => 'sync',
                    'label' => 'Sync with Linnworks',
                    'handle' => function (array $ids) {
                        // Dispatch sync jobs for selected products
                        session()->flash('message', count($ids).' products queued for sync.');
                    },
                ],
            ])
            ->filters([
                [
                    'key' => 'has_barcode_2',
                    'label' => 'Has Secondary Barcode',
                    'type' => 'select',
                    'options' => [
                        '' => 'All',
                        '1' => 'Yes',
                        '0' => 'No',
                    ],
                    'default' => '',
                    'apply' => function ($query, $value) {
                        if ($value === '1') {
                            return $query->whereNotNull('barcode_2');
                        } elseif ($value === '0') {
                            return $query->whereNull('barcode_2');
                        }

                        return $query;
                    },
                ],
                [
                    'key' => 'updated_after',
                    'label' => 'Updated After',
                    'type' => 'date',
                    'default' => null,
                    'apply' => function ($query, $value) {
                        return $query->whereDate('updated_at', '>=', $value);
                    },
                ],
            ])
            ->defaultSort('name');
    }

    public function create(): void
    {
        $this->redirect(route('products.create'));
    }

    /**
     * Show stock history for a product
     */
    public function showStockHistory($productId)
    {
        $this->selectedProduct = Product::find($productId);
        if (!$this->selectedProduct) {
            $this->errorMessage = 'Product not found.';
            return;
        }

        $this->showHistoryModal = true;
        $this->getStockItemHistory();
    }

    /**
     * Get stock item history for the selected product
     */
    public function getStockItemHistory()
    {
        if (!$this->selectedProduct) {
            return;
        }

        $this->isLoadingHistory = true;
        $this->errorMessage = null;
        $this->stockHistory = null;

        try {
            Log::info("Fetching stock history for SKU: {$this->selectedProduct->sku}");

            // Get the stock history from the Linnworks API
            $history = $this->linnworksService->getStockItemHistory($this->selectedProduct->sku);

            // Store the history data
            $this->stockHistory = $history;

            Log::info("Retrieved stock history for SKU: {$this->selectedProduct->sku}");
        } catch (\Exception $e) {
            Log::error("Failed to get stock history for SKU: {$this->selectedProduct->sku} - ".$e->getMessage());
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
        $this->selectedProduct = null;
    }

    /**
     * Override render to include stock history modal
     */
    public function render()
    {
        $data = $this->getQuery()->paginate($this->perPage);

        return view('livewire.products-table', [
            'data' => $data,
            'table' => $this->getTable(),
        ]);
    }
}
