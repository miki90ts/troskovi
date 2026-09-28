<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SerbianFiscalReceiptUrl implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || $value === '') {
            return;
        }

        $parts = parse_url($value);

        if (! is_array($parts)
            || strtolower((string) ($parts['scheme'] ?? '')) !== 'https'
            || isset($parts['user'])
            || isset($parts['pass'])
            || isset($parts['fragment'])
        ) {
            $fail('QR link mora biti bezbedan HTTPS link Poreske uprave.');

            return;
        }

        $host = strtolower(rtrim((string) ($parts['host'] ?? ''), '.'));
        $allowedHosts = config('features.receipt_verification_hosts', ['suf.purs.gov.rs']);
        $hostAllowed = collect($allowedHosts)->contains(
            static fn (string $allowed): bool => $host === $allowed || str_ends_with($host, '.'.$allowed)
        );

        $path = rtrim((string) ($parts['path'] ?? ''), '/');
        parse_str((string) ($parts['query'] ?? ''), $query);

        if (! $hostAllowed || $path !== '/v' || ! is_string($query['vl'] ?? null) || $query['vl'] === '') {
            $fail('QR link nije važeći verifikacioni link Poreske uprave.');
        }
    }
}
