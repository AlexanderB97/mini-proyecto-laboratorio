<?php
/**
 * ============================================================================
 *  ENVIO DE NOTIFICACIONES
 *  Patron aplicado: FACTORY METHOD (creacional)
 * ============================================================================
 *
 *  ✅ NotificationFactory concentra la creación: el resto del sistema deja
 *     de conocer las clases concretas EmailNotification / SmsNotification.
 *  ✅ Un tipo no soportado ahora lanza una excepción, en vez de fallar en
 *     silencio.
 *  ✅ enviar() mantiene su firma original para no romper a quien ya la usa
 *     (OrderController.php), pero ahora delega en la fábrica.
 * ============================================================================
 */

final class NotificationFactory
{
    public static function create(string $type, string $destino): Notification
    {
        return match ($type) {
            'email' => new EmailNotification($destino),
            'sms'   => new SmsNotification($destino),
            default => throw new InvalidArgumentException(
                "Tipo de notificacion no soportado: {$type}"
            ),
        };
    }
}

class NotificationSender
{
    public function enviar(string $tipo, string $destino, string $mensaje): void
    {
        NotificationFactory::create($tipo, $destino)->send($mensaje);
    }
}