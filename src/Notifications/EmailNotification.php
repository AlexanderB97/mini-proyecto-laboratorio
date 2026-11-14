<?php

/**
 * ============================================================================
 *  NOTIFICACION POR EMAIL
 *  Patron aplicado: FACTORY (creacional)
 * ============================================================================
 *
 *  ✅ Implementa Notification::send() para compartir contrato con SMS.
 *  ✅ Se crea solo a través de NotificationFactory.
 * ============================================================================
 */

class EmailNotification implements Notification
{
    public function __construct(private string $destinatario)
    {
    }

    public function send(string $message): void
    {
        echo "[EMAIL] para {$this->destinatario} | Pedido del laboratorio: {$message}<br>";
    }
}
