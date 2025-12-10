<?php

namespace App\Actions\Scanner;

use App\DTOs\Scanner\ScanData;
use App\Rules\BarcodePrefixCheck;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ValidateScanDataAction
{
    /**
     * Validate scan data before submission
     */
    public function handle(ScanData $scanData): array
    {
        $validator = Validator::make([
            'barcode' => $scanData->barcode,
            'quantity' => $scanData->quantity,
            'action' => $scanData->action,
            'user_id' => $scanData->userId,
        ], [
            'barcode' => ['required', new BarcodePrefixCheck('505903')],
            'quantity' => 'required|integer|min:1',
            'action' => 'required|in:increase,decrease',
            'user_id' => 'required|integer|exists:users,id',
        ], [
            'quantity.integer' => 'Quantity must be an integer.',
            'quantity.min' => 'Quantity must be at least 1.',
            'action.in' => 'Action must be either increase or decrease.',
            'user_id.exists' => 'Invalid user.',
        ]);

        if ($validator->fails()) {
            return [
                'valid' => false,
                'errors' => $validator->errors()->toArray(),
                'messages' => $validator->errors()->all(),
            ];
        }

        return [
            'valid' => true,
            'errors' => [],
            'messages' => [],
        ];
    }

    /**
     * Validate and throw exception if invalid
     */
    public function validateOrFail(ScanData $scanData): void
    {
        $result = $this->handle($scanData);

        if (! $result['valid']) {
            $validator = Validator::make([], []); // Empty validator for exception

            foreach ($result['errors'] as $field => $messages) {
                foreach ($messages as $message) {
                    $validator->errors()->add($field, $message);
                }
            }

            throw new ValidationException($validator);
        }
    }

    /**
     * Get validation rules for scan data
     */
    public function getRules(): array
    {
        return [
            'barcode' => ['required', new BarcodePrefixCheck('505903')],
            'quantity' => 'required|integer|min:1',
            'action' => 'required|in:increase,decrease',
            'user_id' => 'required|integer|exists:users,id',
        ];
    }

    /**
     * Get validation messages
     */
    public function getMessages(): array
    {
        return [
            'quantity.integer' => 'Quantity must be an integer.',
            'quantity.min' => 'Quantity must be at least 1.',
            'action.in' => 'Action must be either increase or decrease.',
            'user_id.exists' => 'Invalid user.',
        ];
    }
}
