<?php
/**
 * ============================================================================
 *  GENERACION DE REPORTES
 *  Patron aplicado: DECORATOR (estructural)
 * ============================================================================
 *
 *  Cada decorador ES un Report y TIENE un Report (composicion): envuelve al
 *  reporte y le suma su parte.
 *  - OCP: un extra nuevo es una clase nueva, no un parametro y un if mas.
 *
 *  ⚠️ El ORDEN de los decoradores cambia el resultado.
 *     Orden acordado: Firma -> PDF -> Marca de agua.
 * ============================================================================
 */

abstract class ReportDecorator implements Report
{
    public function __construct(protected Report $report) {}
}
