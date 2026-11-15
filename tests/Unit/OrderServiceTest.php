<?php

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * SRP + Observer: OrderService coordina validar, calcular, guardar y avisar.
 * Se le inyecta un OrderSubject propio para no mandar avisos reales.
 * Casos documentados en docs/CASOS-DE-PRUEBA.md (CP-21 a CP-23).
 */
#[CoversClass(OrderService::class)]
final class OrderServiceTest extends TestCase
{
    private ArrayObject $avisados;
    private OrderService $service;

    protected function setUp(): void
    {
        $this->avisados = new ArrayObject();
        $eventos = new OrderSubject();
        $eventos->subscribe(new class ($this->avisados) implements OrderObserver {
            public function __construct(private ArrayObject $avisados)
            {
            }

            public function update(Order $order): void
            {
                $this->avisados[] = $order->id;
            }
        });

        $this->service = new OrderService(new OrderValidator(), $eventos);
    }

    /** CP-21 (positivo) */
    public function test_un_pedido_valido_se_crea_con_el_descuento_se_guarda_y_se_avisa(): void
    {
        $order = $this->service->createOrder(101, 'Juan Perez', 15000, 'obra_social');

        $this->assertSame('Juan Perez', $order->patient);
        $this->assertEqualsWithDelta(10500.0, $order->amount, 0.001);
        $this->assertSame([101], $this->avisados->getArrayCopy());
        $this->assertContains(
            "INSERT INTO orders (id, patient, amount) VALUES (101, 'Juan Perez', 10500)",
            Connection::getInstance()->consultas()
        );
    }

    /** CP-22 (negativo) */
    public function test_un_pedido_con_datos_invalidos_no_se_guarda_ni_se_avisa(): void
    {
        $consultasAntes = count(Connection::getInstance()->consultas());

        try {
            $this->service->createOrder(102, '', 15000, 'obra_social');
            $this->fail('Se esperaba InvalidArgumentException');
        } catch (InvalidArgumentException $e) {
            $this->assertSame('Falta el paciente', $e->getMessage());
        }

        $this->assertSame([], $this->avisados->getArrayCopy());
        $this->assertCount($consultasAntes, Connection::getInstance()->consultas());
    }

    /** CP-23 (negativo). Regresión de BUG-01. */
    public function test_un_tipo_de_paciente_desconocido_no_crea_el_pedido(): void
    {
        $this->expectException(InvalidArgumentException::class);

        try {
            $this->service->createOrder(103, 'Juan Perez', 15000, 'obrasocial');
        } finally {
            $this->assertSame([], $this->avisados->getArrayCopy());
        }
    }
}
