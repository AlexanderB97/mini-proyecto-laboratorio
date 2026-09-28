<?php

/**
 * ============================================================================
 *  SISTEMA HEREDADO DEL LABORATORIO
 *  Patron aplicado: ADAPTER (estructural)
 * ============================================================================
 *
 *  CONTEXTO: el laboratorio ya tenía un sistema de avisos hecho por otro
 *  proveedor. No lo podemos modificar: lo usan también Recepción y Facturación.
 *
 *  ✅ FORMA CORRECTA
 *     LegacyNotifier queda intacto, tal cual lo entrega el proveedor.
 *     LegacyNotifierAdapter traduce su método sendMessage() a nuestro
 *     contrato Notification::send(), y toda la incompatibilidad queda
 *     encerrada en este único archivo.
 * ============================================================================
 */

class LegacyNotifier
{
    /** Metodo original del proveedor. No lo tocamos. */
    public function sendMessage(string $text): void
    {
        echo "[LEGACY] {$text}<br>";
    }
}

final class LegacyNotifierAdapter implements Notification
{
    // COMPOSICION: el adapter TIENE el sistema viejo adentro
    public function __construct(private LegacyNotifier $legacy) {}

    public function send(string $message): void
    {
        $this->legacy->sendMessage($message);
    }
}
