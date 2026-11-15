<?php

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Patrón OBSERVER: el sujeto avisa a todos los interesados sin conocerlos.
 * Casos documentados en docs/CASOS-DE-PRUEBA.md (CP-19 y CP-20).
 */
#[CoversClass(OrderSubject::class)]
final class OrderSubjectTest extends TestCase
{
    /** CP-19 (positivo) */
    public function test_avisa_a_todos_los_observers_en_el_orden_en_que_se_suscribieron(): void
    {
        $avisos = new ArrayObject();
        $subject = new OrderSubject();
        $subject->subscribe($this->observerQueRegistra($avisos, 'email'));
        $subject->subscribe($this->observerQueRegistra($avisos, 'sms'));

        $subject->notify(new Order(1, 'Juan Perez', 10500, 'obra_social'));

        $this->assertSame(['email:1', 'sms:1'], $avisos->getArrayCopy());
    }

    /** CP-20 (negativo) */
    public function test_un_observer_que_falla_no_corta_la_cadena_y_queda_registrado(): void
    {
        $log = tempnam(sys_get_temp_dir(), 'log');
        $logAnterior = ini_set('error_log', $log);

        $avisos = new ArrayObject();
        $subject = new OrderSubject();
        $subject->subscribe(new class implements OrderObserver {
            public function update(Order $order): void
            {
                throw new RuntimeException('Servidor de mail caído');
            }
        });
        $subject->subscribe($this->observerQueRegistra($avisos, 'sms'));

        $subject->notify(new Order(2, 'Ana Gomez', 22000, 'particular'));

        ini_set('error_log', $logAnterior);
        $this->assertSame(['sms:2'], $avisos->getArrayCopy());
        $this->assertStringContainsString('Servidor de mail caído', file_get_contents($log));
        unlink($log);
    }

    private function observerQueRegistra(ArrayObject $avisos, string $canal): OrderObserver
    {
        return new class ($avisos, $canal) implements OrderObserver {
            public function __construct(private ArrayObject $avisos, private string $canal)
            {
            }

            public function update(Order $order): void
            {
                $this->avisos[] = "{$this->canal}:{$order->id}";
            }
        };
    }
}
