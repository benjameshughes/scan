<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class RegisterAllowedDomains implements ValidationRule
{
    protected $allowedDomains;

    public function __construct(?array $allowedDomains = null)
    {
        $this->allowedDomains = $allowedDomains ?? config('allowedDomains.domains');
    }

    public function validate(string $attribute, $value, Closure $fail): void
    {
        $domain = strtolower(substr(strrchr($value, '@'), 1));
        $allowedDomainsLower = array_map('strtolower', $this->allowedDomains);

        if (! in_array($domain, $allowedDomainsLower)) {
            $fail('The domain is not allowed');
        }
    }
}
