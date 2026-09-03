<?php

namespace App\Support;

class Document
{
    /**
     * Remove tudo que não for dígito.
     */
    public static function onlyDigits(?string $value): string
    {
        return preg_replace('/\D/', '', $value ?? '');
    }

    /**
     * Valida um CPF pelo algoritmo dos dígitos verificadores.
     * Espera receber o CPF com ou sem máscara.
     */
    public static function isValidCpf(?string $cpf): bool
    {
        $cpf = self::onlyDigits($cpf);

        // Precisa ter 11 dígitos
        if (strlen($cpf) !== 11) {
            return false;
        }

        // Rejeita sequências repetidas (000.000.000-00, 111..., etc.)
        if (preg_match('/^(\d)\1{10}$/', $cpf)) {
            return false;
        }

        // Valida os dois dígitos verificadores
        for ($t = 9; $t < 11; $t++) {
            $sum = 0;
            for ($i = 0; $i < $t; $i++) {
                $sum += $cpf[$i] * (($t + 1) - $i);
            }
            $digit = ((10 * $sum) % 11) % 10;
            if ($cpf[$t] != $digit) {
                return false;
            }
        }

        return true;
    }

    /**
     * Formata um CPF (11 dígitos) na máscara 000.000.000-00.
     */
    public static function formatCpf(?string $cpf): string
    {
        $cpf = self::onlyDigits($cpf);

        if (strlen($cpf) !== 11) {
            return $cpf;
        }

        return substr($cpf, 0, 3) . '.' .
            substr($cpf, 3, 3) . '.' .
            substr($cpf, 6, 3) . '-' .
            substr($cpf, 9, 2);
    }
}
