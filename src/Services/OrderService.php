<?php
/**
 * ============================================================================
 *  SERVICIO DE PEDIDOS
 *  Principio aplicado: SRP
 * ============================================================================
 *
 *  OrderService solo COORDINA. Cada paso lo hace otra clase:
 *     Validacion            -> OrderValidator
 *     Precio                -> PriceCalculator + PricingStrategy
 *     Persistencia          -> Order::guardar() (pendiente: OrderRepository)
 *     Aviso al paciente     -> PatientNotifier
 *     Avisos internos       -> OrderSubject + observers
 *     Reporte               -> decoradores de Report
 *
 *  ⚠️ Si el servicio empieza a DECIDIR reglas de negocio, vuelve a ser la
 *     clase que hace todo.
 *  ⚠️ Los echo son de presentacion: se mueven a una View en el refactor MVC.
 * ============================================================================
 */

final class OrderService
{
    private OrderValidator $validator;
    private PatientNotifier $notifier;
    private OrderSubject $events;

    public function __construct(
        ?OrderValidator $validator = null,
        ?PatientNotifier $notifier = null,
        ?OrderSubject $events = null
    ) {
        $this->validator = $validator ?? new OrderValidator();
        $this->notifier  = $notifier ?? new PatientNotifier();
        $this->events    = $events ?? self::defaultEvents();
    }

    public function procesarPedidoCompleto(
        int $id,
        string $paciente,
        float $monto,
        string $tipoPaciente,
        string $tipoNotificacion,
        string $destino
    ): void {
        $error = $this->validator->validate($paciente, $monto);
        if ($error !== null) {
            echo "{$error}<br>";
            return;
        }

        $total = (new PriceCalculator(PricingStrategyResolver::forPatientType($tipoPaciente)))->calculate($monto);
        $order = new Order($id, $paciente, $total, $tipoPaciente);
        $order->guardar();

        $this->notifier->notify($order, $tipoNotificacion, $destino);
        $this->events->notify($order);

        echo (new PdfReportDecorator(new DigitalSignatureDecorator(new BasicReport("Pedido {$id}"))))->generate() . '<br>';
        echo "<p>Pedido {$id} procesado. Total: $ {$total}</p>";
    }

    private static function defaultEvents(): OrderSubject
    {
        $events = new OrderSubject();
        $events->subscribe(new EmailObserver('paciente@mail.com'));
        $events->subscribe(new SmsObserver('3704000000'));
        $events->subscribe(new DashboardObserver());

        return $events;
    }
}
