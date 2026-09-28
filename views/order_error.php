<?php
/**
 * VISTA: el pedido no se pudo crear.
 * Recibe $error (string) desde el controlador. Solo muestra, escapado.
 */
?>
<h1>No se pudo crear el pedido</h1><p><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
