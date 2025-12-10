<?php

namespace App\Actions\Scanner;

use App\Models\User;
use Illuminate\Support\Facades\Log;

class HandleEmailRefillAction
{
    public function __construct(
        private ProcessBarcodeAction $processBarcodeAction,
    ) {}

    /**
     * Handle email refill request from URL parameters
     */
    public function handle(string $barcodeParam, User $user): array
    {
        Log::info('Processing email refill request', [
            'barcode' => $barcodeParam,
            'user_id' => $user->id,
        ]);

        try {
            // Process the barcode from email
            $barcodeResult = $this->processBarcodeAction->handleEmailRefill($barcodeParam);

            if (! $barcodeResult->isValid) {
                Log::warning('Email refill failed - invalid barcode', [
                    'barcode' => $barcodeParam,
                    'error' => $barcodeResult->error,
                ]);

                return [
                    'success' => false,
                    'error' => "Invalid barcode from email: {$barcodeResult->error}",
                    'isEmailRefill' => false,
                    'barcodeScanned' => false,
                    'product' => null,
                    'showRefillForm' => false,
                ];
            }

            if (! $barcodeResult->hasProduct()) {
                Log::warning('Email refill failed - product not found', [
                    'barcode' => $barcodeParam,
                ]);

                return [
                    'success' => false,
                    'error' => 'Product not found for email refill request',
                    'isEmailRefill' => false,
                    'barcodeScanned' => false,
                    'product' => null,
                    'showRefillForm' => false,
                ];
            }

            // Check refill permissions
            if (! $user->can('refill bays')) {
                Log::warning('Email refill failed - insufficient permissions', [
                    'barcode' => $barcodeParam,
                    'user_id' => $user->id,
                ]);

                return [
                    'success' => false,
                    'error' => 'You do not have permission to refill bays.',
                    'isEmailRefill' => true,
                    'barcodeScanned' => true,
                    'product' => $barcodeResult->product,
                    'showRefillForm' => false,
                ];
            }

            Log::info('Email refill request processed successfully', [
                'barcode' => $barcodeParam,
                'product_sku' => $barcodeResult->product->sku,
                'user_id' => $user->id,
            ]);

            return [
                'success' => true,
                'error' => null,
                'isEmailRefill' => true,
                'barcodeScanned' => true,
                'product' => $barcodeResult->product,
                'barcode' => $barcodeParam,
                'showRefillForm' => true, // Auto-trigger refill form
            ];

        } catch (\Exception $e) {
            Log::error('Email refill request failed with exception', [
                'barcode' => $barcodeParam,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => "Failed to process email refill: {$e->getMessage()}",
                'isEmailRefill' => false,
                'barcodeScanned' => false,
                'product' => null,
                'showRefillForm' => false,
            ];
        }
    }

    /**
     * Validate email refill parameters
     */
    public function validateEmailRefillParams(?string $action, ?string $barcode): array
    {
        if (! $action || ! $barcode) {
            return [
                'valid' => false,
                'error' => 'Missing email refill parameters',
            ];
        }

        if ($action !== 'refill') {
            return [
                'valid' => false,
                'error' => 'Invalid email refill action',
            ];
        }

        if (empty(trim($barcode))) {
            return [
                'valid' => false,
                'error' => 'Empty barcode in email refill request',
            ];
        }

        return [
            'valid' => true,
            'error' => null,
        ];
    }

    /**
     * Reset email refill state
     */
    public function resetEmailRefillState(): array
    {
        return [
            'isEmailRefill' => false,
            'showRefillForm' => false,
        ];
    }

    /**
     * Get email refill context data for UI
     */
    public function getEmailRefillContext(): array
    {
        return [
            'banner_title' => 'Email Refill Request',
            'banner_description' => 'You\'ve been directed here from an empty bay notification email',
            'reset_button_text' => 'Normal Scanning',
        ];
    }
}
