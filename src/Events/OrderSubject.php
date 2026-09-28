<?php
/**
 * ============================================================================
 *  AVISOS AL CREARSE UN PEDIDO
 *  Patron aplicado: OBSERVER (comportamiento)
 * ============================================================================
 *
 *  El sujeto conoce solo la interfaz OrderObserver, no las clases concretas.
 *  - OCP: un aviso nuevo es un observer nuevo, esta clase no se modifica.
 *  - Un aviso roto ya no corta la cadena: se registra y se sigue con el resto.
 * ============================================================================
 */

final class OrderSubject
{
    /** @var OrderObserver[] */
    private array $observers = [];

    public function subscribe(OrderObserver $observer): void
    {
        $this->observers[] = $observer;
    }

    public function notify(Order $order): void
    {
        foreach ($this->observers as $observer) {
            try {
                $observer->update($order);
            } catch (Throwable $e) {
                error_log(sprintf(
                    '[OrderSubject] %s fallo para el pedido %d: %s',
                    get_debug_type($observer),
                    $order->id,
                    $e->getMessage()
                ));
            }
        }
    }
}
