<?php

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Patrón FACTORY: NotificationFactory concentra la creación de los canales.
 * Casos documentados en docs/CASOS-DE-PRUEBA.md (CP-07 a CP-10).
 */
#[CoversClass(NotificationFactory::class)]
#[CoversClass(EmailNotification::class)]
#[CoversClass(SmsNotification::class)]
final class NotificationFactoryTest extends TestCase
{
    /** CP-07 (positivo) */
    public function test_crea_un_email_que_envia_al_destinatario(): void
    {
        $notificacion = NotificationFactory::create('email', 'paciente@mail.com');

        $this->assertInstanceOf(EmailNotification::class, $notificacion);
        $this->expectOutputString('[EMAIL] para paciente@mail.com | Pedido del laboratorio: Pedido 1 creado<br>');
        $notificacion->send('Pedido 1 creado');
    }

    /** CP-08 (positivo) */
    public function test_crea_un_sms_que_envia_al_numero(): void
    {
        $notificacion = NotificationFactory::create('sms', '3704000000');

        $this->assertInstanceOf(SmsNotification::class, $notificacion);
        $this->expectOutputString('[SMS] a 3704000000: Pedido 1 creado<br>');
        $notificacion->send('Pedido 1 creado');
    }

    /** CP-09 (positivo) */
    public function test_el_canal_legacy_se_obtiene_a_traves_del_adapter(): void
    {
        $notificacion = NotificationFactory::create('legacy');

        $this->assertInstanceOf(LegacyNotifierAdapter::class, $notificacion);
        $this->assertInstanceOf(Notification::class, $notificacion);
    }

    /** CP-10 (negativo) */
    public function test_un_tipo_no_soportado_lanza_una_excepcion(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Tipo de notificacion no soportado: whatsapp');

        NotificationFactory::create('whatsapp', '3704000000');
    }
}
