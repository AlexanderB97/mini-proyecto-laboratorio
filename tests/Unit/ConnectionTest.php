<?php

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Patrón SINGLETON: una sola conexión para todo el request.
 * Casos documentados en docs/CASOS-DE-PRUEBA.md (CP-13 a CP-15).
 */
#[CoversClass(Connection::class)]
final class ConnectionTest extends TestCase
{
    /** CP-13 (positivo) */
    public function test_get_instance_devuelve_siempre_la_misma_conexion(): void
    {
        $primera = Connection::getInstance();
        $segunda = Connection::getInstance();

        $this->assertSame($primera, $segunda);
    }

    /** CP-14 (positivo) */
    public function test_ejecutar_registra_la_consulta_en_la_conexion_compartida(): void
    {
        $sql = 'INSERT INTO orders (id) VALUES (999)';

        Connection::getInstance()->ejecutar($sql);

        $this->assertContains($sql, Connection::getInstance()->consultas());
    }

    /** CP-15 (negativo) */
    public function test_no_se_puede_crear_una_conexion_con_new(): void
    {
        $this->expectException(Error::class);
        $this->expectExceptionMessageMatches('/private/');

        new Connection();
    }
}
