<?php

// Aviso por SMS: se agregó como observer nuevo, sin modificar OrderSubject (OCP).
final class SmsObserver implements OrderObserver
{
    public function __construct(private string $phone)
    {
    }

    public function update(Order $order): void
    {
        NotificationFactory::create('sms', $this->phone)->send("Pedido {$order->id} creado");
    }
}
