<?php
/**
 * ============================================================================
 *  CALCULO DE PRECIOS
 *  Patron aplicado: STRATEGY (comportamiento)
 * ============================================================================
 *
 *  Cada algoritmo de precio vive en su propia PricingStrategy.
 *  - OCP: un tipo de paciente nuevo es una clase nueva, esta no se modifica.
 *  - DRY: el IVA se aplica sobre calculate(), sin repetir los descuentos.
 * ============================================================================
 */

final class PriceCalculator
{
    private const IVA = 0.21;

    public function __construct(private PricingStrategy $strategy) {}

    public function calculate(float $amount): float
    {
        return $this->strategy->calculate($amount);
    }

    public function calculateWithVat(float $amount): float
    {
        return $this->calculate($amount) * (1 + self::IVA);
    }
}
