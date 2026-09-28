<?php

final class PricingStrategyResolver
{
    public static function forPatientType(string $patientType): PricingStrategy
    {
        return match ($patientType) {
            'obra_social' => new ObraSocialStrategy(),
            'jubilado'    => new JubiladoStrategy(),
            'prepaga'     => new PrepagaStrategy(),
            default       => new ParticularStrategy(),
        };
    }
}
