<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Str;

class BarcodePrefixCheck implements ValidationRule
{
    /**
     * The prefix that the barcode should start with.
     *
     * @var string
     */
    protected string $prefix;

    /**
     * Create a new rule instance.
     *
     * @param string $prefix
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
        // Convert value to string if it's not already
        $valueStr = (string) $value;

        // Check if the barcode starts with the required prefix
        if (!Str::startsWith($valueStr, $this->prefix)) {
            $fail("The {$attribute} must start with {$this->prefix}.");
        }
    }
}
