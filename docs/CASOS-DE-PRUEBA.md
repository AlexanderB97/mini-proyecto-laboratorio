# Casos de prueba — laboratorio-pedidos

Casos de prueba de las funcionalidades refactorizadas en el TP N.º 2.1. Cada funcionalidad tiene al menos un caso **positivo** (camino feliz: datos válidos, el requisito se cumple) y uno **negativo** (camino alternativo: datos inválidos o inesperados, el sistema responde bien al error).

Todos los casos están automatizados con **PHPUnit** en `tests/Unit/` y se ejecutan en cada push y en cada Pull Request con GitHub Actions (`.github/workflows/ci.yml`).

## Cómo ejecutarlos

```bash
composer install
cp config/database.example.php config/database.php   # en Windows: copy config\database.example.php config\database.php
composer test     # pruebas unitarias
composer lint     # sintaxis (php -l) + estándar PSR-12 (phpcs)
```

## Precondiciones generales

- **PG-1.** PHP 8.2 o superior y las dependencias instaladas con `composer install`.
- **PG-2.** Existe `config/database.php` (copia de `config/database.example.php`). La persistencia es en memoria: no hace falta MySQL.
- **PG-3.** Los avisos reales (email, SMS) imprimen en pantalla; las pruebas que los disparan verifican esa salida o usan observers de prueba para no enviar nada.

## Resumen

| Funcionalidad | Patrón o principio | Casos | Positivos | Negativos | Archivo de prueba |
| --- | --- | --- | --- | --- | --- |
| F1 · Cálculo de precios | Strategy | CP-01 a CP-06 | 5 | 1 | `tests/Unit/PricingTest.php` |
| F2 · Creación de notificaciones | Factory | CP-07 a CP-10 | 3 | 1 | `tests/Unit/NotificationFactoryTest.php` |
| F3 · Avisos por el sistema heredado | Adapter | CP-11 y CP-12 | 1 | 1 | `tests/Unit/LegacyNotifierAdapterTest.php` |
| F4 · Conexión a la base de datos | Singleton | CP-13 a CP-15 | 2 | 1 | `tests/Unit/ConnectionTest.php` |
| F5 · Validación de pedidos | SRP | CP-16 a CP-18 | 1 | 2 | `tests/Unit/OrderValidatorTest.php` |
| F6 · Avisos al crear un pedido | Observer | CP-19 y CP-20 | 1 | 1 | `tests/Unit/OrderSubjectTest.php` |
| F7 · Creación de un pedido | SRP + Observer | CP-21 a CP-23 | 1 | 2 | `tests/Unit/OrderServiceTest.php` |
| F8 · Reporte diario | Decorator | CP-24 y CP-25 | 1 | 1 | `tests/Unit/ReportDecoratorTest.php` |
| **Total** | | **25** | **15** | **10** | |

Estado de todos los casos al momento de la entrega: **Aprobado** (25 de 25).

---

## F1 · Cálculo de precios (Strategy)

Requisito: el total del pedido aplica el descuento que corresponde al tipo de paciente — obra social 30 %, jubilado 50 %, prepaga 20 %, particular sin descuento — y el IVA (21 %) se calcula sobre el precio con descuento.

| ID | Tipo | Descripción | Precondiciones | Datos de entrada | Pasos | Resultado esperado | Estado |
| --- | --- | --- | --- | --- | --- | --- | --- |
| CP-01 | Positivo | Descuento de obra social | PG-1 | tipo `obra_social`, monto 15000 | 1. Obtener la estrategia con `PricingStrategyResolver::forPatientType()`. 2. Calcular con `PriceCalculator::calculate()`. | Total 10500 | Aprobado |
| CP-02 | Positivo | Descuento de jubilado | PG-1 | tipo `jubilado`, monto 15000 | Igual que CP-01 | Total 7500 | Aprobado |
| CP-03 | Positivo | Descuento de prepaga | PG-1 | tipo `prepaga`, monto 15000 | Igual que CP-01 | Total 12000 | Aprobado |
| CP-04 | Positivo | Particular sin descuento | PG-1 | tipo `particular`, monto 15000 | Igual que CP-01 | Total 15000 | Aprobado |
| CP-05 | Positivo | IVA sobre el precio con descuento | PG-1 | estrategia obra social, monto 15000 | 1. Crear `PriceCalculator` con `ObraSocialStrategy`. 2. Llamar a `calculateWithVat()`. | Total 12705 (10500 + 21 %) | Aprobado |
| CP-06 | Negativo | Tipo de paciente desconocido | PG-1 | tipo `obrasocial` (mal escrito) | 1. Llamar a `PricingStrategyResolver::forPatientType('obrasocial')`. | Se lanza `InvalidArgumentException` con el mensaje "Tipo de paciente no soportado: obrasocial". No se cobra precio de particular en silencio. | Aprobado (falló en la primera ejecución: ver BUG-01) |

## F2 · Creación de notificaciones (Factory)

Requisito: `NotificationFactory::create()` devuelve el canal pedido (email, SMS o el sistema heredado) sin que el resto del sistema conozca las clases concretas, y rechaza los canales que no existen.

| ID | Tipo | Descripción | Precondiciones | Datos de entrada | Pasos | Resultado esperado | Estado |
| --- | --- | --- | --- | --- | --- | --- | --- |
| CP-07 | Positivo | Crear y enviar un email | PG-1 | tipo `email`, destino `paciente@mail.com`, mensaje "Pedido 1 creado" | 1. Crear con la fábrica. 2. Llamar a `send()`. | Devuelve un `EmailNotification` y muestra "[EMAIL] para paciente@mail.com \| Pedido del laboratorio: Pedido 1 creado" | Aprobado |
| CP-08 | Positivo | Crear y enviar un SMS | PG-1 | tipo `sms`, destino `3704000000`, mensaje "Pedido 1 creado" | 1. Crear con la fábrica. 2. Llamar a `send()`. | Devuelve un `SmsNotification` y muestra "[SMS] a 3704000000: Pedido 1 creado" | Aprobado |
| CP-09 | Positivo | Canal del sistema heredado | PG-1 | tipo `legacy` | 1. Crear con la fábrica. | Devuelve un `LegacyNotifierAdapter`, que cumple la interfaz `Notification` | Aprobado |
| CP-10 | Negativo | Canal no soportado | PG-1 | tipo `whatsapp`, destino `3704000000` | 1. Crear con la fábrica. | Se lanza `InvalidArgumentException` con el mensaje "Tipo de notificacion no soportado: whatsapp" | Aprobado |

## F3 · Avisos por el sistema heredado (Adapter)

Requisito: el sistema heredado del proveedor se usa a través de `Notification::send()` sin modificar su código (`LegacyNotifier::sendMessage()`).

| ID | Tipo | Descripción | Precondiciones | Datos de entrada | Pasos | Resultado esperado | Estado |
| --- | --- | --- | --- | --- | --- | --- | --- |
| CP-11 | Positivo | El adapter traduce la llamada | PG-1. `LegacyNotifier` reemplazado por un doble de prueba (mock). | mensaje "Dashboard actualizado para el pedido 7" | 1. Crear el adapter con el doble. 2. Llamar a `send()`. | `sendMessage()` del proveedor se llama exactamente una vez, con el mismo mensaje | Aprobado |
| CP-12 | Negativo | El adapter solo envuelve al sistema heredado | PG-1 | un `SmsNotification` en lugar de un `LegacyNotifier` | 1. Construir el adapter con ese objeto. | Se lanza `TypeError`: el adapter no acepta otra cosa | Aprobado |

## F4 · Conexión a la base de datos (Singleton)

Requisito: todo el request comparte una única conexión, y no se pueden crear conexiones nuevas por fuera de `getInstance()`.

| ID | Tipo | Descripción | Precondiciones | Datos de entrada | Pasos | Resultado esperado | Estado |
| --- | --- | --- | --- | --- | --- | --- | --- |
| CP-13 | Positivo | Siempre la misma instancia | PG-1, PG-2 | — | 1. Llamar dos veces a `Connection::getInstance()`. | Las dos llamadas devuelven el mismo objeto | Aprobado |
| CP-14 | Positivo | La conexión compartida registra las consultas | PG-1, PG-2 | SQL `INSERT INTO orders (id) VALUES (999)` | 1. Ejecutar la consulta con `ejecutar()`. 2. Leer `consultas()` desde otra llamada a `getInstance()`. | La consulta figura en el registro de la conexión compartida | Aprobado |
| CP-15 | Negativo | No se puede usar `new` | PG-1 | — | 1. Ejecutar `new Connection()`. | Se lanza `Error` porque el constructor es privado | Aprobado |

## F5 · Validación de pedidos (SRP)

Requisito: un pedido necesita un paciente con nombre y un monto mayor a cero.

| ID | Tipo | Descripción | Precondiciones | Datos de entrada | Pasos | Resultado esperado | Estado |
| --- | --- | --- | --- | --- | --- | --- | --- |
| CP-16 | Positivo | Datos válidos | PG-1 | paciente "Juan Perez", monto 15000 | 1. Llamar a `OrderValidator::validate()`. | Devuelve `null` (sin error) | Aprobado |
| CP-17 | Negativo | Paciente vacío | PG-1 | paciente "" y paciente "   " (solo espacios), monto 15000 | 1. Validar cada combinación. | Devuelve "Falta el paciente" en los dos casos | Aprobado |
| CP-18 | Negativo | Monto cero o negativo | PG-1 | paciente "Juan Perez", montos 0 y -500 | 1. Validar cada combinación. | Devuelve "Monto invalido" en los dos casos | Aprobado |

## F6 · Avisos al crear un pedido (Observer)

Requisito: al crear un pedido se avisa a todos los interesados suscriptos, y si un aviso falla los demás se envían igual y el error queda registrado.

| ID | Tipo | Descripción | Precondiciones | Datos de entrada | Pasos | Resultado esperado | Estado |
| --- | --- | --- | --- | --- | --- | --- | --- |
| CP-19 | Positivo | Se avisa a todos, en orden | PG-1. Dos observers de prueba (email y SMS) que registran el aviso. | pedido 1, "Juan Perez", 10500, `obra_social` | 1. Suscribir los dos observers. 2. Llamar a `notify()`. | Se registra "email:1" y luego "sms:1" | Aprobado |
| CP-20 | Negativo | Un aviso falla | PG-1. Un observer que lanza una excepción ("Servidor de mail caído") y otro de SMS que registra. | pedido 2, "Ana Gomez", 22000, `particular` | 1. Suscribir primero el que falla y después el de SMS. 2. Llamar a `notify()`. | El SMS se envía igual ("sms:2") y el error queda en el log de errores | Aprobado |

## F7 · Creación de un pedido (SRP + Observer)

Requisito: `OrderService` valida los datos, calcula el total con la estrategia del paciente, guarda el pedido y avisa a los observers. Si algo es inválido, no guarda ni avisa.

| ID | Tipo | Descripción | Precondiciones | Datos de entrada | Pasos | Resultado esperado | Estado |
| --- | --- | --- | --- | --- | --- | --- | --- |
| CP-21 | Positivo | Pedido válido | PG-1, PG-2. Un observer de prueba en lugar de los avisos reales. | id 101, "Juan Perez", 15000, `obra_social` | 1. Llamar a `createOrder()`. | El pedido queda con total 10500, se guarda (el INSERT figura en la conexión) y se avisa una vez | Aprobado |
| CP-22 | Negativo | Paciente vacío | PG-1, PG-2. Un observer de prueba. | id 102, paciente "", 15000, `obra_social` | 1. Llamar a `createOrder()`. | Se lanza `InvalidArgumentException` ("Falta el paciente"), no se guarda nada y no se avisa a nadie | Aprobado |
| CP-23 | Negativo | Tipo de paciente desconocido | PG-1, PG-2. Un observer de prueba. | id 103, "Juan Perez", 15000, `obrasocial` | 1. Llamar a `createOrder()`. | Se lanza `InvalidArgumentException` y no se avisa a nadie (prueba de regresión de BUG-01) | Aprobado (falló en la primera ejecución: ver BUG-01) |

## F8 · Reporte diario (Decorator)

Requisito: el reporte diario agrega, en este orden, la firma digital, el formato PDF y la marca de agua; sin decoradores, el reporte no agrega nada.

| ID | Tipo | Descripción | Precondiciones | Datos de entrada | Pasos | Resultado esperado | Estado |
| --- | --- | --- | --- | --- | --- | --- | --- |
| CP-24 | Positivo | Reporte completo en el orden acordado | PG-1 | contenido "Pedidos del dia" | 1. Envolver `BasicReport` con firma, PDF y marca de agua. 2. Llamar a `generate()`. | "Reporte: Pedidos del dia + firma digital + PDF + marca de agua" | Aprobado |
| CP-25 | Negativo | Reporte sin decoradores | PG-1 | contenido "Pedidos del dia" | 1. Llamar a `generate()` sobre `BasicReport` solo. | "Reporte: Pedidos del dia", sin PDF ni firma | Aprobado |

---

## Registro de defectos

| Campo | BUG-01 | BUG-02 |
| --- | --- | --- |
| Título | Un tipo de paciente desconocido se cobra como particular, sin avisar | `Order::calcularTotal()` aplica el descuento dos veces |
| Reportado por | Casos CP-06 y CP-23 (pruebas automatizadas) | Revisión de código del equipo |
| Descripción | `PricingStrategyResolver` usaba `default => new ParticularStrategy()`. Un tipo mal escrito (`obrasocial` en vez de `obra_social`) creaba el pedido a precio lleno y avisaba al paciente. | `Order::amount` ya guarda el total con descuento, y `calcularTotal()` le volvía a aplicar la estrategia. El método no se usaba en ninguna parte. |
| Pasos de reproducción | 1. Abrir `public/index.php?accion=crear&tipo=obrasocial`. | 1. Crear un pedido de obra social de 15000 con `OrderService`. 2. Llamar a `$order->calcularTotal()`. |
| Resultado esperado | El pedido se rechaza con un mensaje de error | 10500 |
| Resultado obtenido | El pedido se crea con total 15000 y se envían los avisos | 7350 |
| Severidad / prioridad | Alta / alta: cobra un precio incorrecto sin que nadie se entere | Media / media: hoy es código muerto, pero un error latente para quien lo use |
| Corrección | El resolver lanza `InvalidArgumentException` ante un tipo desconocido y `particular` pasa a ser un caso explícito. El controlador ya mostraba ese error en la vista `order_error.php`. | Se eliminó el método: el total se calcula una sola vez, en `OrderService`. |
| Estado | Nuevo → Asignado → Corregido → Verificado (CP-06 y CP-23 en verde) → **Cerrado** | Nuevo → Asignado → Corregido → **Cerrado** |
