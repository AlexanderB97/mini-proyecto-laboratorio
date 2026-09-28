<?php

final class EmailObserver implements OrderObserver
{
    public function __construct(private string $to) {}

    public function update(Order $order): void
    {
        NotificationFactory::create('email', $this->to)->send("Pedido {$order->id} creado");
    }
}
