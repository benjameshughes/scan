<?php

namespace App\DTOs;

/**
 * DTO for sending an empty bay notification from the scan form
 */
readonly class EmptyBayDTO
{
    public function __construct(
        public int $barcode,
    ) {}

    public function toArray(): array
    {
        return [
            'barcode' => $this->barcode,
        ];
    }

}
