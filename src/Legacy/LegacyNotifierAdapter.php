<?php

/**
 * ============================================================================
 *  ADAPTER DEL SISTEMA HEREDADO
 *  Patron aplicado: ADAPTER (estructural)
 * ============================================================================
 *
 *  Traduce Notification::send() al metodo sendMessage() del proveedor.
 *  Toda la incompatibilidad queda encerrada en este unico archivo.
 * ============================================================================
 */
final class LegacyNotifierAdapter implements Notification
{
    // COMPOSICION: el adapter TIENE el sistema viejo adentro
    public function __construct(private LegacyNotifier $legacy)
    {
    }

    public function send(string $message): void
    {
        $this->legacy->sendMessage($message);
    }
}
