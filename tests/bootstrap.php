<?php

/**
 * Arranque de las pruebas.
 *
 * Composer carga todas las clases de src/ (classmap), así que las pruebas
 * no necesitan los require_once de public/index.php.
 *
 * Precondición: config/database.php tiene que existir, porque Connection
 * lo lee al crearse. En GitHub Actions el workflow lo copia desde
 * config/database.example.php antes de correr las pruebas.
 */

require __DIR__ . '/../vendor/autoload.php';

if (!is_file(__DIR__ . '/../config/database.php')) {
    fwrite(STDERR, "Falta config/database.php. Copiá config/database.example.php antes de correr las pruebas.\n");
    exit(1);
}
