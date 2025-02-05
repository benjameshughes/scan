<?php

namespace App\DTOs;

/*
 * Data Transfer Object for Scan once a user has scanned and submitted a barcode
 */
class ScanDTO {

    public function __construct(
        public string $barcode,
        public int $quantity,
    ) {
        //
    }

    public function toArray(): array {
        return [
            'barcode' => $this->barcode,
            'quantity' => $this->quantity,
        ];
    }
}