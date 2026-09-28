<?php

/**
 * ============================================================================
 *  CONEXION A LA BASE DE DATOS
 *  Patron aplicado: SINGLETON (creacional)
 * ============================================================================
 *
 *  ✅ Constructor privado + instancia estática + getInstance()
 *  ✅ Una sola instancia para todo el request, sin importar cuántas veces
 *     se llame a getInstance()
 *  ✅ Si algo falla al ejecutar, el error se propaga (no se traga en silencio)
 * ============================================================================
 */

class Connection
{
    private static ?Connection $instance = null;

    /** Almacen en memoria para poder correr la demo sin MySQL levantado. */
    private array $tablaEnMemoria = [];

    private function __construct()
    {
        // En un entorno con MySQL real, acá se abriría la conexión PDO:
        // $dsn = "mysql:host={$config['host']};dbname={$config['name']};charset=utf8mb4";
        // $this->pdo = new PDO($dsn, $config['user'], $config['pass'], [
        //     PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        // ]);
    }

    public static function getInstance(): Connection
    {
        if (self::$instance === null) {
            self::$instance = new Connection();
        }

        return self::$instance;
    }

    public function ejecutar(string $sql): void
    {
        try {
            $this->tablaEnMemoria[] = $sql;
        } catch (Throwable $e) {
            throw new RuntimeException('Error al ejecutar la consulta: ' . $sql, 0, $e);
        }
    }

    public function consultas(): array
    {
        return $this->tablaEnMemoria;
    }
}
