<?php
/**
 * VISTA: reporte.
 * Recibe $reporte (string) desde el controlador. Solo muestra, escapado.
 */
?>
<?= htmlspecialchars($reporte, ENT_QUOTES, 'UTF-8') ?>
