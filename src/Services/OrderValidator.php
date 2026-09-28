<?php

// Responsabilidad única: validar los datos de entrada.
final class OrderValidator
{
    public function validate(string $paciente, float $monto): ?string
    {
        if (trim($paciente) === '') {
            return 'Falta el paciente';
        }

        if ($monto <= 0) {
            return 'Monto invalido';
        }

        return null;
    }
}
