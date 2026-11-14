<?php

/**
 * Contrato comun de todos los canales de aviso (email, SMS y el sistema
 * heredado a traves de su Adapter). NotificationFactory devuelve siempre
 * un Notification, nunca una clase concreta.
 */
interface Notification
{
    public function send(string $message): void;
}
