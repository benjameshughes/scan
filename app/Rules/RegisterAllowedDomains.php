<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class RegisterAllowedDomains implements ValidationRule
{
    protected $allowedDomains;

    public function __construct(array $allowedDomains)
    {
        $this->allowedDomains = config('allowedDomains.domains');
    }

    public function validate(string $attribute, $value, Closure $fail): void
    {
        $domain = substr(strrchr($value, '@'), 1);
        if (!in_array($domain, $this->allowedDomains)) {
            $fail('The domain is not allowed');
        }
    }
}
