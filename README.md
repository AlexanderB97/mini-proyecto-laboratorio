# laboratorio-pedidos

Sistema de pedidos de un laboratorio de análisis clínicos: crea pedidos, calcula el total según el tipo de paciente, avisa al paciente, lista pedidos y genera un reporte diario.

- **PHP vanilla**: sin frameworks y sin Composer.
- **Persistencia simulada en memoria**: no hace falta MySQL ni crear ninguna base de datos.
- Materia: **Metodología de Sistemas II** — Tecnicatura Universitaria en Programación (TUP), UTN FRRe (sede Formosa).
- **TP Integrador, Unidades 1 y 2**: refactorizar un proyecto con deuda técnica sembrada, aplicando un patrón por rama y un Pull Request por patrón.

---

## Requisitos

- **XAMPP con PHP 8 o superior.** Solo se usa **Apache**; MySQL no hace falta.
- **Git.**

Para ver qué versión de PHP trae tu XAMPP:

```powershell
C:\xampp\php\php.exe -v
```

---

## Instalación (PowerShell)

**1. Clonar el repositorio dentro de `C:\xampp\htdocs\`**

```powershell
cd C:\xampp\htdocs
git clone https://github.com/AlexanderB97/mini-proyecto-laboratorio.git
cd mini-proyecto-laboratorio
```

> La carpeta tiene que llamarse `mini-proyecto-laboratorio`, porque es parte de la URL que se abre en el navegador.

**2. Crear el archivo de configuración a partir de la plantilla**

```powershell
Copy-Item config\database.example.php config\database.php
```

No hace falta editarlo: como la persistencia es en memoria, los valores vacíos de la plantilla alcanzan.

- `config/database.php` está en **`.gitignore`**, así que **nunca se sube al repositorio**. Ahí van las credenciales de la base de datos: si se versionaran, cualquiera con acceso al repo las vería, y cada integrante pisaría las del otro en cada merge.
- Lo que sí se versiona es `config/database.example.php`, que es la plantilla con los campos vacíos.
- Si te olvidás este paso, la app se detiene con un error que empieza así:
  `Fatal error: Uncaught RuntimeException: Falta config/database.php. Copiá config/database.example.php y completá los datos.`

**3. Encender Apache**

Abrí el **XAMPP Control Panel** (`C:\xampp\xampp-control.exe`) y hacé clic en **Start** en la fila de **Apache**. Tiene que quedar en verde.

---

## Cómo probarlo

Abrí en el navegador:

```
http://localhost/mini-proyecto-laboratorio/public/
```

Sin parámetros ejecuta la acción `crear`. Las tres acciones disponibles son:

| URL | Qué se ve |
|---|---|
| `http://localhost/mini-proyecto-laboratorio/public/?accion=crear` | Tres avisos (uno por cada observer), el título **Pedido creado**, `Paciente: Juan Perez` y `Total: $ 10500` (ver detalle abajo) |
| `http://localhost/mini-proyecto-laboratorio/public/?accion=listar` | El título **Pedidos** y una tabla: `1 · Juan Perez · 10500` y `2 · Ana Gomez · 22000` |
| `http://localhost/mini-proyecto-laboratorio/public/?accion=reporte` | `Reporte: Pedidos del dia + firma digital + PDF + marca de agua` |

**Si ves "Pedido creado" con total `$ 10500`, el proyecto funciona.** (15000 con 30 % de descuento de obra social.)

La pantalla de `crear` completa se ve así:

```
[EMAIL] para paciente@mail.com | Pedido del laboratorio: Pedido 1 creado
[SMS] a 3704000000: Pedido 1 creado
[LEGACY] Dashboard actualizado para el pedido 1
Pedido creado
Paciente: Juan Perez
Total: $ 10500
```

Las tres primeras líneas son los avisos que disparan los observers al guardarse el pedido: email y SMS al paciente, y el dashboard de Recepción a través del sistema heredado (`[LEGACY]`, vía el Adapter). En este proyecto los envíos están simulados: solo se imprimen.

Cualquier otra acción muestra `Accion no encontrada`.

Si los datos del pedido no son válidos, no se crea y se ve **No se pudo crear el pedido** con el motivo. Por ejemplo, `?accion=crear&monto=0` muestra `Monto invalido`, y `?accion=crear&paciente=%20` muestra `Falta el paciente`.

`?accion=crear` acepta parámetros opcionales para probar otros casos: `id`, `paciente`, `monto` y `tipo` (`obra_social`, `jubilado`, `prepaga` o `particular`). Por ejemplo, `?accion=crear&monto=10000&tipo=jubilado` da un total de `$ 5000`.

También se puede probar sin Apache, desde la carpeta del proyecto:

```powershell
C:\xampp\php\php.exe -r "`$_GET['accion']='crear'; chdir('public'); require 'index.php';"
```

---

## Problemas frecuentes

| Síntoma | Causa | Solución |
|---|---|---|
| El navegador muestra `ERR_CONNECTION_REFUSED` / "No se puede acceder a este sitio" | Apache está apagado | Encenderlo en el XAMPP Control Panel (paso 3 de la instalación) |
| `Fatal error: Uncaught RuntimeException: Falta config/database.php...` | No se creó el archivo de configuración | `Copy-Item config\database.example.php config\database.php` desde la carpeta del proyecto |
| **No se pudo crear el pedido** (`Monto invalido` o `Falta el paciente`) | Los parámetros de la URL no pasan la validación de `OrderValidator` | No es una falla: el monto tiene que ser mayor que 0 y el paciente no puede estar vacío |
| `404 Not Found` | La carpeta no está en `C:\xampp\htdocs\` o tiene otro nombre | Tiene que quedar `C:\xampp\htdocs\mini-proyecto-laboratorio\public\index.php` |
| En la terminal aparecen caracteres raros como `âœ…`, `âŒ` o `estÃ¡tica` | Los comentarios del código tienen emojis y acentos en UTF-8, y `Get-Content` de PowerShell 5.1 los lee con otra codificación | **No es un error del archivo.** Usar `Get-Content -Encoding UTF8 <archivo>` o abrirlo con VS Code |

---

## Estructura de carpetas

```
mini-proyecto-laboratorio/
├── config/              Configuración local: database.example.php (plantilla versionada) y database.php (ignorado por git)
├── docs/                Consigna del TP y mapa de deudas original de la cátedra
├── public/              Punto de entrada (index.php): carga las clases y rutea según ?accion=
├── src/
│   ├── Controllers/     OrderController: lee la entrada, llama a la fachada y elige la vista
│   ├── Database/        Connection: conexión única (Singleton) que lee config/database.php
│   ├── Events/          Avisos al crearse un pedido (Observer): OrderSubject y sus observers
│   ├── Legacy/          Sistema de avisos heredado del proveedor y su Adapter
│   ├── Models/          Order: el modelo de pedido
│   ├── Notifications/   Canales de aviso (email, SMS y el sistema heredado), creados por NotificationFactory
│   ├── Pricing/         Cálculo de precios (Strategy): una estrategia por tipo de paciente
│   ├── Reports/         Reporte básico y sus decoradores (firma digital, PDF, marca de agua)
│   └── Services/        OrderFacade (fachada), OrderService (crea el pedido) y OrderValidator
└── views/               Plantillas HTML: solo muestran datos y escapan la salida
```

---

## Patrones aplicados

Así recorre el código un pedido nuevo (`?accion=crear`):

```
public/index.php
  └─ OrderController::create()            MVC: lee la entrada y elige la vista
       └─ OrderFacade::createOrder()      Facade: solo delega
            └─ OrderService::createOrder()   SRP: coordina los pasos
                 ├─ OrderValidator             valida paciente y monto
                 ├─ PriceCalculator            Strategy: descuento según el tipo de paciente
                 ├─ Order::guardar()           Singleton: Connection::getInstance()
                 └─ OrderSubject::notify()     Observer
                      ├─ EmailObserver     ─┐
                      ├─ SmsObserver        ├─ Factory Method: NotificationFactory::create()
                      └─ DashboardObserver ─┘   (el canal 'legacy' es el Adapter)
  └─ views/order_created.php               MVC: solo muestra, escapado
```

| Patrón | Archivos principales | PR |
|---|---|---|
| Strategy | `src/Pricing/PricingStrategy.php`, `ParticularStrategy.php`, `ObraSocialStrategy.php`, `JubiladoStrategy.php`, `PrepagaStrategy.php`, `PricingStrategyResolver.php`, `PriceCalculator.php` | [#7](https://github.com/AlexanderB97/mini-proyecto-laboratorio/pull/7) |
| Observer | `src/Events/OrderObserver.php`, `OrderSubject.php`, `EmailObserver.php`, `SmsObserver.php`, `DashboardObserver.php` (se suscriben en `OrderService`) | [#8](https://github.com/AlexanderB97/mini-proyecto-laboratorio/pull/8) |
| Decorator | `src/Reports/Report.php`, `BasicReport.php`, `ReportDecorator.php`, `DigitalSignatureDecorator.php`, `PdfReportDecorator.php`, `WatermarkDecorator.php` | [#9](https://github.com/AlexanderB97/mini-proyecto-laboratorio/pull/9) |
| Factory Method | `src/Notifications/NotificationFactory.php`, `EmailNotification.php` (interfaz `Notification`), `SmsNotification.php` | [#2](https://github.com/AlexanderB97/mini-proyecto-laboratorio/pull/2) |
| Adapter | `src/Legacy/LegacyNotifier.php` (`LegacyNotifier` + `LegacyNotifierAdapter`), registrado como canal `legacy` en `NotificationFactory` y usado por `DashboardObserver` | [#10](https://github.com/AlexanderB97/mini-proyecto-laboratorio/pull/10) |
| Singleton | `src/Database/Connection.php` | [#11](https://github.com/AlexanderB97/mini-proyecto-laboratorio/pull/11) |
| Configuración externa | `config/database.example.php`, `src/Database/Connection.php`, `.gitignore` | [#17](https://github.com/AlexanderB97/mini-proyecto-laboratorio/pull/17) |
| Facade | `src/Services/OrderFacade.php`, `src/Controllers/OrderController.php` | [#19](https://github.com/AlexanderB97/mini-proyecto-laboratorio/pull/19) |
| SRP | `src/Services/OrderService.php`, `OrderValidator.php` | [#18](https://github.com/AlexanderB97/mini-proyecto-laboratorio/pull/18) |
| Separación MVC | `src/Controllers/OrderController.php`, `views/order_created.php`, `views/order_error.php`, `views/orders.php`, `views/report.php` | [#20](https://github.com/AlexanderB97/mini-proyecto-laboratorio/pull/20) |

Además, [#21](https://github.com/AlexanderB97/mini-proyecto-laboratorio/pull/21) conectó la fachada con `OrderService`. Hasta ese PR, el flujo web no pasaba por el servicio, así que Observer, SRP y Adapter estaban aplicados pero no se ejecutaban. También eliminó código duplicado (`PatientNotifier`, `NotificationSender`) y renombró el archivo de la fábrica a `NotificationFactory.php`.

---

## Deuda técnica: síntoma, patrón y consecuencia

| Síntoma | Deuda | Patrón | Consecuencia asumida |
|---|---|---|---|
| `PriceCalculator` tenía un `switch` con todos los algoritmos de precio, y el descuento de obra social estaba repetido en 5 archivos | Cada tipo de paciente nuevo obligaba a modificar el `switch`, y cambiar un descuento implicaba buscarlo en varios lugares (viola OCP y DRY) | **Strategy**: una clase por tipo de paciente detrás de `PricingStrategy` | Más clases, y alguien tiene que decidir qué estrategia usar: esa decisión quedó concentrada en `PricingStrategyResolver` |
| Crear un pedido disparaba avisos encadenados a mano, llamando a clases concretas; si un aviso fallaba, se cortaba la cadena | Agregar un aviso obligaba a modificar el código que crea el pedido (viola DIP y OCP) | **Observer**: `OrderSubject` notifica a cualquier `OrderObserver` suscripto y registra el error si uno falla | El flujo es más difícil de seguir y depurar: no se ve en un solo lugar quién reacciona a un pedido |
| El reporte recibía parámetros booleanos que activaban funciones extra (firma, PDF, marca de agua) | *Boolean trap*: cada extra nuevo es un parámetro y un `if` más | **Decorator**: cada extra es una clase que envuelve a un `Report` | Muchas clases chicas, y el orden de envoltura cambia el resultado (orden acordado: firma → PDF → marca de agua) |
| El mismo `if` por tipo de notificación (email / SMS) estaba repetido en varios archivos, y un tipo desconocido fallaba en silencio | Cada canal nuevo obligaba a tocar todos esos `if` | **Factory Method**: `NotificationFactory::create()` concentra la creación y lanza una excepción ante un tipo no soportado | Cada canal nuevo requiere una clase más y registrarla en el `match` de la fábrica |
| La biblioteca de avisos del proveedor tenía una interfaz incompatible (`sendMessage()`) y se la había modificado o copiado para adaptarla | Tocar código de terceros que también usan otras áreas del laboratorio | **Adapter**: `LegacyNotifierAdapter` traduce `sendMessage()` al contrato `Notification::send()` sin modificar `LegacyNotifier` | Una capa más de indirección entre el sistema y el proveedor, y el canal `legacy` ignora el destinatario porque el sistema heredado no lo usa |
| Se abría una conexión nueva por cada consulta, y las excepciones se silenciaban | Recursos desperdiciados y errores que no se veían | **Singleton**: `Connection::getInstance()` devuelve siempre la misma instancia y los errores se propagan | Estado global compartido: es más difícil de testear y de reemplazar por un doble de prueba |
| Las credenciales de la base estaban escritas con `define()` en `public/index.php` y versionadas | Si el repo es público, la credencial es pública, y cada integrante editaba esas líneas generando conflictos en cada merge | **Configuración externa**: `config/database.php` fuera del repositorio, con `config/database.example.php` como plantilla | Quien clona tiene que crear `config/database.php`, y si falta el error aparece al ejecutar, no antes |
| `OrderService` tenía un método largo que validaba, calculaba, guardaba, notificaba y generaba el reporte | Una clase con muchos motivos de cambio (viola SRP) | **SRP**: la validación pasó a `OrderValidator`, los avisos a `OrderSubject` y sus observers, y la presentación a las vistas; el servicio solo coordina | Más archivos para seguir el flujo completo |
| El controlador mezclaba SQL, HTML y reglas de negocio, y coordinaba varios servicios a la vez | El controlador dependía de todo el subsistema | **Facade**: `OrderFacade` expone `createOrder()`, `listOrders()` y `dailyReport()`, y el controlador depende solo de ella | Riesgo de que la fachada se convierta en la nueva clase que hace todo: tiene que coordinar, no decidir |
| La vista `orders.php` consultaba los datos, calculaba precios y mostraba la salida sin escapar | Lógica en la presentación y riesgo de XSS | **Separación MVC**: el controlador prepara los datos (vía la fachada) y las vistas solo muestran, con `htmlspecialchars()` | El controlador tiene que preparar todos los datos que la vista necesita, y un cambio de pantalla puede tocar dos archivos |

---

## Métrica: el descuento de obra social

Antes del refactor, el descuento de obra social estaba escrito como `0.7` en **5 archivos (6 apariciones)**: `src/Models/Order.php`, `src/Pricing/PriceCalculator.php` (dos veces), `src/Services/OrderService.php`, `src/Controllers/OrderController.php` y `views/orders.php`.

Ahora vive **en un solo lugar**: `src/Pricing/ObraSocialStrategy.php`, como descuento de `0.30`.

Para verificarlo, desde la carpeta del proyecto:

```powershell
Get-ChildItem -Path . -Filter *.php -Recurse | Select-String -Pattern "0\.7|0\.30"
```

Resultado esperado (una sola línea):

```
src\Pricing\ObraSocialStrategy.php:5:    public function __construct(private float $discount = 0.30) {}
```

---

## Deuda conocida pendiente

Estas deudas siguen en el código y **no se abordaron** en este TP:

| Dónde | Deuda | Solución sugerida |
|---|---|---|
| `public/index.php` | Carga manual de dependencias: casi 30 `require_once`. Cada clase nueva obliga a editar este archivo y el orden importa | `spl_autoload_register()` que resuelva `src/<Carpeta>/<Clase>.php`, o Composer con PSR-4 |
| `public/index.php` | Ruteo con `if / elseif` encadenados: cada pantalla nueva obliga a modificar el bloque (viola OCP) | Un mapa de rutas: `array` de `accion => callable`, con 404 si la acción no existe |
| `src/Models/Order.php` | Propiedades públicas y mutables, sin validación en el constructor. `guardar()` arma el SQL por concatenación (riesgo de inyección) y mezcla dominio con persistencia | Propiedades `readonly` con validación, y un `OrderRepository` con consultas preparadas |
| `src/Services/OrderFacade.php` | `listOrders()` devuelve datos de demostración fijos (la persistencia está simulada) | Leerlos desde el futuro `OrderRepository` |

---

## Flujo de trabajo del equipo

- **Nadie escribe en `main`.** Es una rama protegida: solo se modifica por Pull Request, con **1 aprobación obligatoria**.
- **Una rama por patrón**, creada desde `main` actualizado: `feat/patron-<nombre>`, `docs/<tema>`, etc.
- **Un PR por rama**, con cuatro secciones: **Deuda identificada**, **Patrón aplicado**, **Consecuencia asumida** y **Cómo probarlo**.
- **Cada PR lo aprueba otro integrante**, nunca quien lo abrió.
- **Commits con convención**: `feat:` (funcionalidad), `fix:` (corrección), `refactor:` (cambio de estructura sin cambiar comportamiento), `docs:` (documentación).
- **Issues con etiquetas** para cada deuda, y un **tablero Kanban** en GitHub Projects para seguir su estado. Cada PR cierra su Issue con `Closes #N`.

---

## Integrantes

- [AlexanderB97](https://github.com/AlexanderB97)
- [Angelf5](https://github.com/Angelf5)
- [Fabian-C-program](https://github.com/Fabian-C-program)
