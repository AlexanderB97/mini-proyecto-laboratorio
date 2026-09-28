<?php

// Ejercicio 5: se agrego sin modificar OrderSubject.
final class SmsObserver implements OrderObserver
{
    public function __construct(private string $phone) {}

    public function update(Order $order): void
    {
        NotificationFactory::create('sms', $this->phone)->send("Pedido {$order->id} creado");
    }
}
