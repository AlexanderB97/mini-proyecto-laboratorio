<?php

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Patrón STRATEGY: cada tipo de paciente tiene su propio cálculo de precio.
 * Casos documentados en docs/CASOS-DE-PRUEBA.md (CP-01 a CP-06).
 */
#[CoversClass(PriceCalculator::class)]
#[CoversClass(PricingStrategyResolver::class)]
#[CoversClass(ParticularStrategy::class)]
#[CoversClass(ObraSocialStrategy::class)]
#[CoversClass(JubiladoStrategy::class)]
#[CoversClass(PrepagaStrategy::class)]
final class PricingTest extends TestCase
{
    /** Tipo de paciente, monto base y total esperado. */
    public static function descuentosPorTipo(): array
    {
        return [
            'CP-01 obra social: 30 % de descuento' => ['obra_social', 15000.0, 10500.0],
            'CP-02 jubilado: 50 % de descuento'    => ['jubilado', 15000.0, 7500.0],
            'CP-03 prepaga: 20 % de descuento'     => ['prepaga', 15000.0, 12000.0],
            'CP-04 particular: sin descuento'      => ['particular', 15000.0, 15000.0],
        ];
    }

    /** CP-01 a CP-04 (positivos) */
    #[DataProvider('descuentosPorTipo')]
    public function test_aplica_el_descuento_que_corresponde_al_tipo_de_paciente(
        string $tipo,
        float $monto,
        float $esperado
    ): void {
        $calculadora = new PriceCalculator(PricingStrategyResolver::forPatientType($tipo));

        $this->assertEqualsWithDelta($esperado, $calculadora->calculate($monto), 0.001);
    }

    /** CP-05 (positivo) */
    public function test_el_iva_se_aplica_sobre_el_precio_con_descuento(): void
    {
        $calculadora = new PriceCalculator(new ObraSocialStrategy());

        // 15000 - 30 % = 10500; 10500 + 21 % de IVA = 12705
        $this->assertEqualsWithDelta(12705.0, $calculadora->calculateWithVat(15000), 0.001);
    }

    /** CP-06 (negativo). Al escribir este caso se encontró el defecto BUG-01. */
    public function test_un_tipo_de_paciente_desconocido_lanza_una_excepcion(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Tipo de paciente no soportado: obrasocial');

        PricingStrategyResolver::forPatientType('obrasocial');
    }
}
