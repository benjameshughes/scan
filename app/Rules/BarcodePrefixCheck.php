<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Str;

class BarcodePrefixCheck implements ValidationRule
{
    /**
     * The prefix that the barcode should start with.
     */
    protected string $prefix;

    /**
     * Create a new rule instance.
     */
    public function __construct(string $prefix = '505903')
    {
        $this->prefix = $prefix;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Skip validation if value is null or empty (let required rule handle that)
        if (is_null($value) || $value === '' || $value === 0) {
            return;
        }

        // Convert value to string if it's not already
        $valueStr = (string) $value;

        // Check if the barcode starts with the required prefix
        if (! Str::startsWith($valueStr, $this->prefix)) {
            $fail("The {$attribute} must start with {$this->prefix}.");
            return;
        }

        // Check if the barcode has the correct length (prefix + 7 digits = 13 total)
        if (strlen($valueStr) !== 13) {
            $fail("The {$attribute} must be exactly 13 digits long.");
            return;
        }

        // Check if the barcode contains only digits
        if (! ctype_digit($valueStr)) {
            $fail("The {$attribute} must contain only digits.");
        }
    }
}
