<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Str;

class BarcodeOrSkuCheck implements ValidationRule
{
    protected string $prefix;

    public function __construct(string $prefix = '505903')
    {
        $this->prefix = $prefix;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (is_null($value) || $value === '' || $value === 0) {
            return;
        }

        $valueStr = (string) $value;

        // SKU format: 000-000
        if (preg_match('/^\d{3}-\d{3}$/', $valueStr)) {
            return;
        }

        // Barcode format: 13 digits starting with prefix
        if (ctype_digit($valueStr) && strlen($valueStr) === 13 && Str::startsWith($valueStr, $this->prefix)) {
            return;
        }

        $fail("The {$attribute} must be a valid barcode (13 digits starting with {$this->prefix}) or SKU (format: 000-000).");
    }
}
