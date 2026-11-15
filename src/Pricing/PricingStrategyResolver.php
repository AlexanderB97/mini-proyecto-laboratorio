<?php

/**
 * Elige la PricingStrategy según el tipo de paciente.
 *
 * Un tipo desconocido lanza una excepción en vez de cobrar precio de
 * particular sin avisar (BUG-01, ver docs/CASOS-DE-PRUEBA.md). Es el mismo
 * criterio que ya usa NotificationFactory con los canales no soportados.
 */
final class PricingStrategyResolver
{
    public static function forPatientType(string $patientType): PricingStrategy
    {
        return match ($patientType) {
            'particular'  => new ParticularStrategy(),
            'obra_social' => new ObraSocialStrategy(),
            'jubilado'    => new JubiladoStrategy(),
            'prepaga'     => new PrepagaStrategy(),
            default       => throw new InvalidArgumentException(
                "Tipo de paciente no soportado: {$patientType}"
            ),
        };
    }
}
