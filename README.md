# Bagisto Manual Payments: Pasarela de Pagos Manuales Verificados

[![Latest Version on Packagist](https://img.shields.io/packagist/v/wilbere/bagisto-manual-payments.svg?style=flat-square)](https://packagist.org/packages/wilbere/bagisto-manual-payments)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)
[![PHP Version](https://img.shields.io/badge/php-%5E8.4-blue.svg?style=flat-square)](composer.json)
[![Bagisto](https://img.shields.io/badge/bagisto-%5E2.5-orange.svg?style=flat-square)](https://bagisto.com)

Paquete open-source para **Bagisto 2.5+ (Laravel / PHP 8.4+)** diseñado específicamente para el mercado venezolano y latinoamericano, donde más del 90% del comercio electrónico opera mediante transferencias manuales directas: **Pago Móvil (VES)**, **Transferencias Bancarias Nacionales (VES)**, **Zelle (USD)** y **Binance Pay / Crypto (USDT)**.

Resuelve la fricción del checkout creando un flujo post-pedido seguro con reserva de inventario temporal (TTL), comprobantes almacenados fuera del webroot público y una consola administrativa de conciliación con facturación automática nativa en Bagisto.

---

## 🚀 Características Principales

* **4 Drivers de Pago Independientes**:
  * `manual_pagomovil`: Pago Móvil interbancario en Bolívares (VES).
  * `manual_bank_transfer`: Transferencias bancarias nacionales en Bolívares (VES).
  * `manual_zelle`: Pagos directos en dólares (USD).
  * `manual_binance`: Transferencias inmediatas sin comisiones en USDT vía Binance Pay.
  * *Cada método puede encenderse o apagarse de forma independiente en Configuración › Ventas › Métodos de Pago.*

* **Catálogo de Cuentas Receptoras Dinámicas**:
  * En lugar de configurar una sola cuenta estática, el comerciante puede registrar múltiples cuentas receptoras desde el panel de control:
    * Ejemplos: Banesco Pago Móvil, Mercantil Pago Móvil, Banco de Venezuela Transferencia, Zelle Principal, Zelle Secundario, Binance Pay ID.
    * Campos específicos por riel: Banco emisor, Titular, Cédula/RIF, Teléfono, Número de cuenta de 20 dígitos, Correo electrónico, Binance ID y Código QR.

* **Flujo Post-Checkout Dedicado y Seguro (Opción A)**:
  * Al completar el checkout, la orden de Bagisto se genera en estado `pending_payment` y reserva el inventario.
  * El comprador es redirigido inmediatamente a una pantalla dedicada `/checkout/manual-payments/{token}` protegida con un token criptográfico de 64 caracteres.
  * Muestra las cuentas bancarias con botones de **copiado rápido al portapapeles** (RIF, Teléfono, Cuenta, Email).
  * Formulario con drag-and-drop y previsualización en vivo para subir el voucher de pago.

* **Sinergia Nativa con `wilbere/bagisto-bcv`**:
  * Si el cliente selecciona Pago Móvil o Transferencia Bancaria, el total a transferir se calcula automáticamente en **Bolívares (VES)** tomando la **tasa oficial BCV congelada en la orden** (`bcv_order_rates`), desplegando el desglose exacto para evitar pagos incompletos por variaciones cambiarias.

* **Reserva de Inventario con Temporizador TTL**:
  * Temporizador regresivo en vivo en la pantalla del cliente.
  * Si el cliente no reporta el pago dentro del tiempo configurado (ej. 60 minutos), el comando automático `php artisan manual-payments:expire` cancela la orden y libera el stock para otros compradores.

* **Seguridad y Anti-Fraude**:
  * **Comprobantes en Storage Privado**: Los comprobantes se guardan en el disco privado del servidor fuera del alcance público, previniendo exposición de datos financieros y subida de archivos maliciosos.
  * **Unicidad de Referencia**: Validación en base de datos que impide registrar el mismo número de referencia bancaria en más de un pedido activo.

* **Consola de Conciliación en Bagisto Admin**:
  * Menú dedicado en *Ventas › Pagos Manuales*.
  * DataGrid con filtros por estado (`Por Enviar`, `Por Conciliar`, `Aprobado`, `Rechazado`, `Expirado`).
  * Pantalla de detalle con visor de imágenes y visor de PDFs con zoom.
  * **Aprobación en 1 Clic**: Invoca internamente `InvoiceRepository::create()`, generando la Factura oficial en Bagisto y transicionando la orden a `processing` de forma 100% nativa.
  * **Rechazo Auditado**: Permite ingresar un motivo formal que se notifica al cliente, permitiéndole corregir y reenviar el comprobante si la tienda lo autoriza.

---

## 📋 Requisitos del Sistema

* **PHP**: `^8.4` o `^8.5` (con extensiones `gd` o `imagick`, `bcmath`, `fileinfo`, `pdo_mysql`).
* **Bagisto**: `^2.5.0` (Laravel `^11.0`, `^12.0` o `^13.0`).
* **Base de Datos**: MariaDB `10.11+` o MySQL `8.0+`.

---

## 📦 Instalación

### 1. Requerir el paquete vía Composer

```bash
composer require wilbere/bagisto-manual-payments
```

### 2. Ejecutar el Asistente de Instalación

```bash
php artisan manual-payments:install
```

Este comando:
* Publica el archivo de configuración `config/manual-payments.php`.
* Ejecuta las migraciones de base de datos (`manual_payment_accounts` y `manual_payment_receipts`).

Si prefieres hacerlo manualmente:

```bash
php artisan vendor:publish --tag=manual-payments-config
php artisan migrate
```

---

## ⚙️ Configuración

El archivo de configuración principal se encuentra en `config/manual-payments.php`. Puedes personalizar los siguientes parámetros en tu archivo `.env`:

```env
# Tiempo límite en minutos para reportar el pago antes de liberar inventario (Default: 60)
MANUAL_PAYMENTS_TTL_MINUTES=60

# Permitir al comprador reenviar un comprobante corregido si fue rechazado
MANUAL_PAYMENTS_ALLOW_RESUBMISSION=true

# Disco de almacenamiento privado para comprobantes (Default: local)
MANUAL_PAYMENTS_DISK=local

# Tamaño máximo del comprobante en Kilobytes (Default: 5120 = 5MB)
MANUAL_PAYMENTS_MAX_FILE_SIZE_KB=5120
```

### Programar la Liberación Automática de Inventario (Cron)

El paquete incluye un comando para cancelar órdenes vencidas:

```bash
php artisan manual-payments:expire
```

Este comando está registrado automáticamente en el Scheduler de Laravel para ejecutarse cada 15 minutos siempre que tengas activo el cron maestro de Bagisto (`* * * * * php artisan schedule:run`).

---

## 🛠️ Guía de Uso

### 1. Registrar las Cuentas de la Tienda
1. En el panel de Bagisto Admin, dirígete a *Configuración › Cuentas de Pago* (`admin/settings/manual-payment-accounts`).
2. Haz clic en **Añadir Cuenta**.
3. Selecciona el riel (**Pago Móvil**, **Transferencia**, **Zelle** o **Binance Pay**) e ingresa los datos bancarios correspondientes.
4. Puedes subir opcionalmente el código QR para agilizar la transferencia del cliente.

### 2. Habilitar los Métodos de Pago en Bagisto
1. En Bagisto Admin, dirígete a *Configuración › Ventas › Métodos de Pago*.
2. Encontrarás los 4 métodos disponibles:
   * **Pago Móvil (VES)**
   * **Transferencia Bancaria Nacional (VES)**
   * **Zelle (USD)**
   * **Binance Pay / Crypto (USDT)**
3. Activa los métodos que desees ofrecer en tu checkout.

### 3. Conciliar Pagos Recibidos
1. Cuando un cliente realiza una compra y sube su comprobante, el pedido ingresa en estado **"Por Conciliar"**.
2. Ingresa a *Ventas › Pagos Manuales* (`admin/sales/manual-payments`).
3. Haz clic en **Ver / Conciliar** sobre el registro.
4. Audita el comprobante bancario, verifica el número de referencia en tu banca y presiona **Aprobar Pago**.
5. Bagisto generará inmediatamente la Factura oficial y el pedido cambiará automáticamente a estado `processing`.

---

## 🧪 Pruebas Automatizadas

El paquete cuenta con una suite completa de pruebas unitarias y de integración desarrolladas con **Pest PHP**:

```bash
composer test
```

---

## 📄 Licencia

Este proyecto está bajo la Licencia **MIT**. Consulta el archivo [LICENSE](LICENSE) para más detalles.
