<?php

namespace App\DataTransferObjects;

/*
 * Data Transfer Object for Scan once a user has scanned and submitted a barcode
 */
class ScanDTO {

    public function __construct(
        public string $barcode,
        public int $quantity,
        public bool $submitted,
        public string $submitted_at,
        public string $created_at,
    ) {
        //
    }

    public function toArray(): array {
        return [
            'barcode' => $this->barcode,
            'quantity' => $this->quantity,
            'submitted' => $this->submitted,
            'submitted_at' => $this->submitted_at,
            'created_at' => $this->created_at,
        ];
    }

    public function toJson(): string {
        return json_encode($this->toArray());
    }
}