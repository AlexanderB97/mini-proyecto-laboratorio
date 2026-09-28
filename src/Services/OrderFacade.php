<?php
/**
 * ============================================================================
 *  FACHADA DE PEDIDOS
 *  Patron aplicado: FACADE (estructural)
 * ============================================================================
 *
 *  ✅ Expone operaciones simples al controlador (crear, listar, reporte) y
 *     esconde el subsistema: Pricing, persistencia, notificaciones y reportes.
 *  ✅ El controlador depende de UNA clase en vez de seis.
 *
 *  ⚠️ La fachada COORDINA, no DECIDE. Los descuentos los decide
 *     PricingStrategy, el canal lo crea NotificationFactory y el formato del
 *     reporte lo arman los decoradores. Si aca aparece un if de negocio, se
 *     convierte en la nueva clase que hace todo.
 * ============================================================================
 */
final class OrderFacade
{
    /** Crea el pedido: calcula el total, lo guarda y avisa al paciente. */
    public function createOrder(int $id, string $paciente, float $monto, string $tipoPaciente): Order
    {
        $total = $this->calculator($tipoPaciente)->calculate($monto);
        $order = new Order($id, $paciente, $total, $tipoPaciente);
        $order->guardar();

        NotificationFactory::create('email', 'paciente@mail.com')->send("Pedido {$id} creado");

        return $order;
    }

    /**
     * Pedidos con el total ya calculado, listos para mostrar.
     * Datos de demostracion (la persistencia esta simulada en memoria):
     * pendiente reemplazarlos por un OrderRepository.
     */
    public function listOrders(): array
    {
        $pedidos = [
            ['id' => 1, 'paciente' => 'Juan Perez', 'monto' => 15000, 'tipo' => 'obra_social'],
            ['id' => 2, 'paciente' => 'Ana Gomez',  'monto' => 22000, 'tipo' => 'particular'],
        ];

        return array_map(
            fn (array $p): array => $p + ['total' => $this->calculator($p['tipo'])->calculate($p['monto'])],
            $pedidos
        );
    }

    /** Reporte del dia. Orden de los decoradores: firma -> PDF -> marca de agua. */
    public function dailyReport(): string
    {
        $reporte = new BasicReport('Pedidos del dia');
        $reporte = new DigitalSignatureDecorator($reporte);
        $reporte = new PdfReportDecorator($reporte);
        $reporte = new WatermarkDecorator($reporte);

        return $reporte->generate();
    }

    private function calculator(string $tipoPaciente): PriceCalculator
    {
        return new PriceCalculator(PricingStrategyResolver::forPatientType($tipoPaciente));
    }
}
