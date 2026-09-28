<?php
/**
 * ============================================================================
 *  NOTIFICACION POR EMAIL
 *  Patron aplicado: FACTORY METHOD (creacional)
 * ============================================================================
 *
 *  ✅ Implementa Notification::send() para compartir contrato con SMS.
 *  ✅ Se mantiene enviarEmail() por compatibilidad, hasta que OrderEvents.php
 *     y OrderService.php se refactoricen para usar el Factory directamente.
 * ============================================================================
 */

interface Notification
{
    public function send(string $message): void;
}

class EmailNotification implements Notification
{
    public function __construct(private string $destinatario)
    {
    }

    public function send(string $message): void
    {
        $this->enviarEmail($this->destinatario, 'Pedido del laboratorio', $message);
    }

    public function enviarEmail(string $destinatario, string $asunto, string $cuerpo): void
    {
        echo "[EMAIL] para {$destinatario} | {$asunto}: {$cuerpo}<br>";
    }
}