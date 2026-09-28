<?php
/**
 * VISTA: confirmacion de pedido creado.
 * Recibe $order desde el controlador. Solo muestra, y escapa toda la salida.
 */
?>
<h1>Pedido creado</h1><p>Paciente: <?= htmlspecialchars($order->patient, ENT_QUOTES, 'UTF-8') ?></p><p>Total: $ <?= htmlspecialchars((string) $order->amount, ENT_QUOTES, 'UTF-8') ?></p>
