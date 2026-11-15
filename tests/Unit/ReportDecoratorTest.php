<?php

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Patrón DECORATOR: cada extra del reporte es una capa que envuelve al anterior.
 * Casos documentados en docs/CASOS-DE-PRUEBA.md (CP-24 y CP-25).
 */
#[CoversClass(BasicReport::class)]
#[CoversClass(ReportDecorator::class)]
#[CoversClass(DigitalSignatureDecorator::class)]
#[CoversClass(PdfReportDecorator::class)]
#[CoversClass(WatermarkDecorator::class)]
final class ReportDecoratorTest extends TestCase
{
    /** CP-24 (positivo) */
    public function test_los_decoradores_se_aplican_en_el_orden_acordado(): void
    {
        $reporte = new WatermarkDecorator(
            new PdfReportDecorator(
                new DigitalSignatureDecorator(
                    new BasicReport('Pedidos del dia')
                )
            )
        );

        $this->assertSame(
            'Reporte: Pedidos del dia + firma digital + PDF + marca de agua',
            $reporte->generate()
        );
    }

    /** CP-25 (negativo) */
    public function test_un_reporte_sin_decoradores_no_agrega_ningun_extra(): void
    {
        $reporte = (new BasicReport('Pedidos del dia'))->generate();

        $this->assertSame('Reporte: Pedidos del dia', $reporte);
        $this->assertStringNotContainsString('PDF', $reporte);
        $this->assertStringNotContainsString('firma', $reporte);
    }
}
