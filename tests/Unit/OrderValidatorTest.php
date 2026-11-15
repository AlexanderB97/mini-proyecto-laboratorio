<?php

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * SRP: la validación de datos de entrada vive en su propia clase.
 * Casos documentados en docs/CASOS-DE-PRUEBA.md (CP-16 a CP-18).
 */
#[CoversClass(OrderValidator::class)]
final class OrderValidatorTest extends TestCase
{
    private OrderValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new OrderValidator();
    }

    /** CP-16 (positivo) */
    public function test_datos_validos_no_devuelven_error(): void
    {
        $this->assertNull($this->validator->validate('Juan Perez', 15000));
    }

    /** CP-17 (negativo) */
    public function test_un_paciente_vacio_o_con_solo_espacios_es_rechazado(): void
    {
        $this->assertSame('Falta el paciente', $this->validator->validate('', 15000));
        $this->assertSame('Falta el paciente', $this->validator->validate('   ', 15000));
    }

    /** CP-18 (negativo) */
    public function test_un_monto_cero_o_negativo_es_rechazado(): void
    {
        $this->assertSame('Monto invalido', $this->validator->validate('Juan Perez', 0));
        $this->assertSame('Monto invalido', $this->validator->validate('Juan Perez', -500));
    }
}
