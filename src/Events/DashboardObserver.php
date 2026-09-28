<?php

// El dashboard de Recepcion vive en el sistema heredado del proveedor:
// se le avisa por el canal 'legacy' (LegacyNotifierAdapter).
final class DashboardObserver implements OrderObserver
{
    public function update(Order $order): void
    {
        NotificationFactory::create('legacy')->send("Dashboard actualizado para el pedido {$order->id}");
    }
}
