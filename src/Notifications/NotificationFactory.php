<?php
/**
 * ============================================================================
 *  ENVIO DE NOTIFICACIONES
 *  Patron aplicado: FACTORY METHOD (creacional)
 * ============================================================================
 *
 *  ✅ NotificationFactory concentra la creación: el resto del sistema deja
 *     de conocer las clases concretas EmailNotification / SmsNotification /
 *     LegacyNotifierAdapter.
 *  ✅ Un tipo no soportado ahora lanza una excepción, en vez de fallar en
 *     silencio.
 *  ✅ 'legacy' es el sistema de avisos del proveedor, visto a través de su
 *     Adapter. No tiene destinatario propio: $destino se ignora.
 * ============================================================================
 */

final class NotificationFactory
{
    public static function create(string $type, string $destino = ''): Notification
    {
        return match ($type) {
            'email'  => new EmailNotification($destino),
            'sms'    => new SmsNotification($destino),
            'legacy' => new LegacyNotifierAdapter(new LegacyNotifier()),
            default  => throw new InvalidArgumentException(
                "Tipo de notificacion no soportado: {$type}"
            ),
        };
    }
}
