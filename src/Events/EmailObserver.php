<?php

final class EmailObserver implements OrderObserver
{
    public function __construct(private string $to) {}

    public function update(Order $order): void
    {
        (new NotificationSender())->enviar('email', $this->to, "Pedido {$order->id} creado");
    }
}
