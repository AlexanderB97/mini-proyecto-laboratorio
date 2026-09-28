<?php
/**
 * ============================================================================
 *  NOTIFICACION POR SMS
 *  Patron aplicado: FACTORY METHOD (creacional)
 * ============================================================================
 *
 *  ✅ Implementa Notification::send(), mismo contrato que EmailNotification.
 *  ✅ Se crea solo a través de NotificationFactory.
 * ============================================================================
 */

class SmsNotification implements Notification
{
    public function __construct(private string $numero)
    {
    }

    public function send(string $message): void
    {
        echo "[SMS] a {$this->numero}: {$message}<br>";
    }
}