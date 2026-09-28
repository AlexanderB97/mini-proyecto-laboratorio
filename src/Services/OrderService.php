<?php
/**
 * ============================================================================
 *  SERVICIO DE PEDIDOS
 *  Principio aplicado: SRP
 * ============================================================================
 *
 *  OrderService solo COORDINA la creacion de un pedido. Cada paso lo hace
 *  otra clase:
 *     Validacion            -> OrderValidator
 *     Precio                -> PriceCalculator + PricingStrategy
 *     Persistencia          -> Order::guardar() (pendiente: OrderRepository)
 *     Avisos                -> OrderSubject + observers (email y SMS al
 *                              paciente, dashboard via sistema heredado)
 *
 *  ⚠️ Si el servicio empieza a DECIDIR reglas de negocio, vuelve a ser la
 *     clase que hace todo.
 *  ✅ No imprime nada: la presentacion la hacen las vistas (MVC).
 * ============================================================================
 */

final class OrderService
{
    private OrderValidator $validator;
    private OrderSubject $events;

    public function __construct(
        ?OrderValidator $validator = null,
        ?OrderSubject $events = null
    ) {
        $this->validator = $validator ?? new OrderValidator();
        $this->events    = $events ?? self::defaultEvents();
    }

    /** @throws InvalidArgumentException si los datos de entrada no son validos. */
    public function createOrder(int $id, string $paciente, float $monto, string $tipoPaciente): Order
    {
        $error = $this->validator->validate($paciente, $monto);
        if ($error !== null) {
            throw new InvalidArgumentException($error);
        }

        $total = (new PriceCalculator(PricingStrategyResolver::forPatientType($tipoPaciente)))->calculate($monto);
        $order = new Order($id, $paciente, $total, $tipoPaciente);
        $order->guardar();

        $this->events->notify($order);

        return $order;
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
