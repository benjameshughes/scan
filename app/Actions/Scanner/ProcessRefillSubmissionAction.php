<?php

namespace App\Actions\Scanner;

use App\Actions\Stock\ExecuteStockTransferAction;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ProcessRefillSubmissionAction
{
    public function __construct(
        private ExecuteStockTransferAction $executeTransferAction,
    ) {}

    /**
     * Process refill form submission
     */
    public function handle(
        Product $product,
        string $selectedLocationId,
        int $refillQuantity,
        User $user,
        ?string $toLocationId = null
    ): array {
        Log::info('Processing refill submission', [
            'product_sku' => $product->sku,
            'location_id' => $selectedLocationId,
            'quantity' => $refillQuantity,
            'user_id' => $user->id,
        ]);

        try {
            // Validate input data
            $this->validateRefillData($selectedLocationId, $refillQuantity, $toLocationId);

            // Determine the destination location
            $destinationLocationId = $toLocationId ?? config('linnworks.default_location_id');

            // Execute the stock transfer
            $result = $this->executeTransferAction->handle(
                user: $user,
                product: $product,
                quantity: $refillQuantity,
                operationType: 'refill',
                fromLocationId: $selectedLocationId,
                toLocationId: $destinationLocationId,
                autoSelectSource: false, // User already selected source
                additionalMetadata: [
                    'refilled_via_scanner' => true,
                    'scanner_session_id' => session()->getId(),
                ]
            );

            Log::info('Refill submission completed successfully', [
                'product_sku' => $product->sku,
                'location_id' => $selectedLocationId,
                'quantity' => $refillQuantity,
                'user_id' => $user->id,
            ]);

            return [
                'success' => true,
                'message' => $result['message'],
                'error' => null,
                'shouldResetScan' => true,
                'shouldRestartCamera' => true,
            ];

        } catch (ValidationException $e) {
            Log::warning('Refill submission validation failed', [
                'product_sku' => $product->sku,
                'errors' => $e->errors(),
            ]);

            // Re-throw validation exceptions to be handled by Livewire
            throw $e;
        } catch (\Exception $e) {
            Log::error('Refill submission failed', [
                'product_sku' => $product->sku,
                'location_id' => $selectedLocationId,
                'quantity' => $refillQuantity,
                'error' => $e->getMessage(),
                'user_id' => $user->id,
            ]);

            return [
                'success' => false,
                'message' => null,
                'error' => $e->getMessage(),
                'shouldResetScan' => false,
                'shouldRestartCamera' => false,
            ];
        }
    }

    /**
     * Validate refill submission data
     */
    private function validateRefillData(string $selectedLocationId, int $refillQuantity, ?string $toLocationId = null): void
    {
        $rules = [
            'selectedLocationId' => 'required|string',
            'refillQuantity' => 'required|integer|min:1',
        ];

        $messages = [
            'selectedLocationId.required' => 'Please select a location to transfer from.',
            'refillQuantity.required' => 'Please enter a quantity to transfer.',
            'refillQuantity.min' => 'Quantity must be at least 1.',
        ];

        $data = [
            'selectedLocationId' => $selectedLocationId,
            'refillQuantity' => $refillQuantity,
        ];

        // Add toLocationId validation if provided
        if ($toLocationId !== null) {
            $rules['toLocationId'] = 'required|string|different:selectedLocationId';
            $messages['toLocationId.required'] = 'Please select a location to transfer to.';
            $messages['toLocationId.different'] = 'The from and to locations must be different.';
            $data['toLocationId'] = $toLocationId;
        }

        $validator = Validator::make($data, $rules, $messages);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    /**
     * Validate refill permissions
     */
    public function validateRefillPermissions(User $user, Product $product): array
    {
        if (! $user->can('refill bays')) {
            return [
                'valid' => false,
                'error' => 'You do not have permission to refill bays.',
            ];
        }

        if (! $product) {
            return [
                'valid' => false,
                'error' => 'No product selected for refill.',
            ];
        }

        return [
            'valid' => true,
            'error' => null,
        ];
    }

    /**
     * Prepare refill success state
     */
    public function prepareSuccessState(string $successMessage): array
    {
        return [
            'refillSuccess' => $successMessage,
            'refillError' => '',
            'isProcessingRefill' => false,
            'showRefillForm' => false,
            'selectedLocationId' => '',
            'refillQuantity' => 1,
            'availableLocations' => [],
        ];
    }

    /**
     * Prepare refill error state
     */
    public function prepareErrorState(string $errorMessage): array
    {
        return [
            'refillError' => $errorMessage,
            'refillSuccess' => '',
            'isProcessingRefill' => false,
            // Keep form open on error so user can retry
        ];
    }

    /**
     * Set processing state
     */
    public function setProcessingState(): array
    {
        return [
            'isProcessingRefill' => true,
            'refillError' => '',
        ];
    }

    /**
     * Validate location and quantity combination
     */
    public function validateLocationAndQuantity(
        string $locationId,
        int $quantity,
        array $availableLocations
    ): array {
        // Find the selected location
        $selectedLocation = collect($availableLocations)->first(function ($location, $index) use ($locationId) {
            $id = $location['Location']['StockLocationId']
                ?? $location['LocationId']
                ?? $location['locationId']
                ?? $location['id']
                ?? $index;

            return $id == $locationId;
        });

        if (! $selectedLocation) {
            return [
                'valid' => false,
                'error' => 'Selected location is not available.',
            ];
        }

        $maxStock = $selectedLocation['StockLevel']
            ?? $selectedLocation['stockLevel']
            ?? $selectedLocation['stock']
            ?? 0;

        if ($quantity > $maxStock) {
            return [
                'valid' => false,
                'error' => "Maximum available quantity is {$maxStock} units.",
            ];
        }

        return [
            'valid' => true,
            'error' => null,
            'maxStock' => $maxStock,
        ];
    }

    /**
     * Get refill submission metadata
     */
    public function getSubmissionMetadata(User $user): array
    {
        return [
            'refilled_via_scanner' => true,
            'scanner_session_id' => session()->getId(),
            'user_agent' => request()->userAgent(),
            'ip_address' => request()->ip(),
            'submitted_at' => now()->toISOString(),
            'user_id' => $user->id,
        ];
    }
}
