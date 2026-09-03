<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use App\Support\Document;

class ValidCpf implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!Document::isValidCpf($value)) {
            $fail('O CPF informado é inválido.');
        }
    }
}
