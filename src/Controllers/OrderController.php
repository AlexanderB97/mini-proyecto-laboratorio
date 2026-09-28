<?php
/**
 * ============================================================================
 *  CONTROLADOR DE PEDIDOS
 *  Patron aplicado: MVC + FACADE
 * ============================================================================
 *
 *  ✅ El controlador solo: lee la entrada, llama a la fachada y elige la vista.
 *  ✅ Sin SQL (lo hace el modelo), sin reglas de negocio (Strategy, Factory,
 *     Decorator, detras de OrderFacade) y sin HTML (lo hacen las vistas).
 *  ✅ Depende de una sola clase, OrderFacade, que se recibe por constructor.
 * ============================================================================
 */

class OrderController
{
    private OrderFacade $facade;

    public function __construct(?OrderFacade $facade = null)
    {
        $this->facade = $facade ?? new OrderFacade();
    }

    public function create(): void
    {
        $order = $this->facade->createOrder(
            (int) ($_GET['id'] ?? 1),
            (string) ($_GET['paciente'] ?? 'Juan Perez'),
            (float) ($_GET['monto'] ?? 15000),
            (string) ($_GET['tipo'] ?? 'obra_social')
        );

        require __DIR__ . '/../../views/order_created.php';
    }

    public function index(): void
    {
        $pedidos = $this->facade->listOrders();

        require __DIR__ . '/../../views/orders.php';
    }

    public function report(): void
    {
        $reporte = $this->facade->dailyReport();

        require __DIR__ . '/../../views/report.php';
    }
}
