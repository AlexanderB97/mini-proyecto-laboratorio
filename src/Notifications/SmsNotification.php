<?php
/**
 * ============================================================================
 *  NOTIFICACION POR SMS
 *  Patron aplicado: FACTORY METHOD (creacional)
 * ============================================================================
 *
 *  ✅ Implementa Notification::send(), mismo contrato que EmailNotification.
 *  ✅ Se mantiene mandarSms() por compatibilidad con el código que aún no
 *     fue refactorizado.
 * ============================================================================
 */

class SmsNotification implements Notification
{
    public function __construct(private string $numero)
    {
    }

    public function send(string $message): void
    {
        $this->mandarSms($this->numero, $message);
    }

    public function mandarSms(string $numero, string $texto): void
    {
        echo "[SMS] a {$numero}: {$texto}<br>";
    }
}