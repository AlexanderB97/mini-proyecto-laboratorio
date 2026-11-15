<?php

/**
 * ============================================================================
 *  laboratorio-pedidos — PUNTO DE ENTRADA
 *  Metodología de Sistemas II — UTN FRRe (sede Formosa)
 * ============================================================================
 *
 *  ✅ 1. Autoload: las clases se cargan solas desde src/<Carpeta>/<Clase>.php.
 *        Una clase nueva ya no obliga a editar este archivo.
 *  ✅ 2. Credenciales en config/database.php, fuera del repositorio.
 *  ✅ 3. Tabla de rutas: una pantalla nueva es una entrada más en el array,
 *        no otro elseif (Abierto/Cerrado).
 * ============================================================================
 */

spl_autoload_register(function (string $class): void {
    foreach (glob(__DIR__ . '/../src/*', GLOB_ONLYDIR) as $carpeta) {
        $archivo = "{$carpeta}/{$class}.php";
        if (is_file($archivo)) {
            require_once $archivo;
            return;
        }
    }
});

$controller = new OrderController();

$rutas = [
    'crear'   => fn () => $controller->create(),
    'listar'  => fn () => $controller->index(),
    'reporte' => fn () => $controller->report(),
];

$accion = $_GET['accion'] ?? 'crear';

$ruta = $rutas[$accion] ?? function (): void {
    http_response_code(404);
    echo 'Accion no encontrada';
};

$ruta();
