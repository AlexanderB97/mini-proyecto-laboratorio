<?php

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Patrón ADAPTER: el sistema heredado se usa a través de Notification::send().
 * Casos documentados en docs/CASOS-DE-PRUEBA.md (CP-11 y CP-12).
 */
#[CoversClass(LegacyNotifierAdapter::class)]
#[CoversClass(LegacyNotifier::class)]
final class LegacyNotifierAdapterTest extends TestCase
{
    /** CP-11 (positivo) */
    public function test_send_delega_en_send_message_del_proveedor(): void
    {
        $proveedor = $this->createMock(LegacyNotifier::class);
        $proveedor->expects($this->once())
            ->method('sendMessage')
            ->with('Dashboard actualizado para el pedido 7');

        (new LegacyNotifierAdapter($proveedor))->send('Dashboard actualizado para el pedido 7');
    }

    /** CP-12 (negativo) */
    public function test_no_acepta_un_objeto_que_no_sea_el_sistema_heredado(): void
    {
        $this->expectException(TypeError::class);

        new LegacyNotifierAdapter(new SmsNotification('3704000000'));
    }
}
