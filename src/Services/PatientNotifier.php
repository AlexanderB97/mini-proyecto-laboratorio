<?php

// Responsabilidad única: avisarle al paciente por el canal que eligió.
final class PatientNotifier
{
    public function notify(Order $order, string $canal, string $destino): void
    {
        NotificationFactory::create($canal, $destino)->send("Pedido {$order->id} por $ {$order->amount}");
    }
}
