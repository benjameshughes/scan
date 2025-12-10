<?php

namespace App\DTOs\Scanner;

readonly class ScanData
{
    public function __construct(
        public string $barcode,
        public int $quantity,
        public string $action,
        public int $userId,
        public array $metadata = [],
    ) {}

    /**
     * Create from form input
     */
    public static function fromForm(string $barcode, int $quantity, bool $scanAction, int $userId): self
    {
        return new self(
            barcode: $barcode,
            quantity: $quantity,
            action: $scanAction ? 'increase' : 'decrease',
            userId: $userId,
        );
    }

    /**
     * Create with additional metadata
     */
    public function withMetadata(array $metadata): self
    {
        return new self(
            barcode: $this->barcode,
            quantity: $this->quantity,
            action: $this->action,
            userId: $this->userId,
            metadata: array_merge($this->metadata, $metadata),
        );
    }

    /**
     * Convert to Scan model attributes
     */
    public function toScanAttributes(): array
    {
        return [
            'barcode' => $this->barcode,
            'quantity' => $this->quantity,
            'submitted' => false,
            'action' => $this->action,
            'sync_status' => 'pending',
            'user_id' => $this->userId,
        ];
    }

    /**
     * Check if this is an increase action
     */
    public function isIncrease(): bool
    {
        return $this->action === 'increase';
    }

    /**
     * Check if this is a decrease action
     */
    public function isDecrease(): bool
    {
        return $this->action === 'decrease';
    }

    /**
     * Get action display text
     */
    public function getActionDisplay(): string
    {
        return match ($this->action) {
            'increase' => 'Increase Stock',
            'decrease' => 'Decrease Stock',
            default => ucfirst($this->action),
        };
    }

    /**
     * Validate scan data
     */
    public function validate(): array
    {
        $errors = [];

        if (empty($this->barcode)) {
            $errors['barcode'] = 'Barcode is required';
        }

        if ($this->quantity < 1) {
            $errors['quantity'] = 'Quantity must be at least 1';
        }

        if (! in_array($this->action, ['increase', 'decrease'])) {
            $errors['action'] = 'Action must be increase or decrease';
        }

        if ($this->userId < 1) {
            $errors['userId'] = 'Valid user ID is required';
        }

        return $errors;
    }

    /**
     * Check if scan data is valid
     */
    public function isValid(): bool
    {
        return empty($this->validate());
    }
}
