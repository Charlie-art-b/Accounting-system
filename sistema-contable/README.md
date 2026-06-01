# Sistema Contable Web

Aplicacion web orientada a la administracion contable y operativa de una empresa. El sistema fue construido para resolver procesos reales de registro, control y analisis financiero mediante una interfaz administrativa centralizada.

Este proyecto funciona como evidencia de conocimientos en:

- desarrollo backend con Laravel;
- construccion de paneles administrativos con Filament;
- modelado de entidades empresariales;
- implementacion de reglas de negocio contables;
- control de acceso por roles y permisos;
- automatizacion de pruebas funcionales y de interfaz.

## 🚀 Despliegue en Producción

> **Este proyecto está 100% listo para despliegue en producción con SQLite.**

Para información sobre cómo desplegar la aplicación, consultar:

- **[QUICK_START.md](QUICK_START.md)** - Guía rápida (15-20 minutos)
- **[DEPLOYMENT_GUIDE.md](DEPLOYMENT_GUIDE.md)** - Guía completa con todos los detalles
- **[PRODUCTION_CHECKLIST.md](PRODUCTION_CHECKLIST.md)** - Checklist de seguridad pre-deploy
- **[PRODUCTION_CHANGES.md](PRODUCTION_CHANGES.md)** - Resumen de cambios realizados

**Base de datos:** SQLite (single-file deployment, sin dependencias externas)

## Resumen ejecutivo

El sistema integra modulos de operacion administrativa y contable en una sola plataforma. Permite registrar clientes, proveedores, productos, inventarios, cuentas por cobrar, cuentas por pagar, activos fijos y asientos contables, y a partir de esa informacion genera reportes financieros exportables.

Desde una perspectiva empresarial, el valor del proyecto esta en que traduce procesos contables a componentes de software auditables, reutilizables y escalables.

## Problema que resuelve

Muchas operaciones de pequenas organizaciones se manejan en archivos dispersos o procesos manuales, lo que dificulta:

- consolidar informacion financiera;
- controlar saldos pendientes;
- mantener trazabilidad de pagos y cobros;
- generar reportes con consistencia;
- restringir accesos segun perfil del usuario.

Este sistema centraliza esos procesos y aplica validaciones para reducir errores operativos.

## Modulos implementados

### Administracion y seguridad

- gestion de usuarios;
- roles y permisos con `spatie/laravel-permission`;
- panel administrativo con autenticacion y recuperacion de contrasena.

### Operacion comercial

- gestion de clientes;
- gestion de proveedores;
- gestion de productos;
- gestion de inventarios;
- relacion inventario-producto.

### Contabilidad

- catalogo de cuentas contables;
- registro de asientos contables;
- validacion de partida doble;
- posteo de asientos;
- reverso de asientos contables.

### Gestion financiera

- cuentas por cobrar;
- cuentas por pagar;
- gestion de cobros;
- registro y reverso de pagos;
- activos fijos.

### Reporteria y analisis

- balance general;
- estado de resultados;
- balance de comprobacion;
- flujo de efectivo;
- cambios en el patrimonio;
- estado de resultados integral;
- exportacion a PDF y Excel;
- historial de reportes generados.

## Evidencia de logica contable implementada

El proyecto no se limita a formularios CRUD. Incluye reglas contables y financieras concretas:

- el servicio `LedgerService` valida que un asiento tenga montos mayores a cero y que el total del debe sea igual al total del haber antes de postearlo;
- el servicio `PaymentService` controla fechas de pago, evita sobrepagos, impide duplicados exactos y soporta reversos;
- el servicio `EstadoFinancieroService` calcula saldos contables por cuenta y genera estados financieros a partir de movimientos registrados;
- el observador `AccountReceivableObserver` crea automaticamente el seguimiento de cobro cuando nace una cuenta por cobrar;
- el sistema contempla clasificacion de cuentas para activos, pasivos, patrimonio, ingresos y gastos.

## Caracteristicas tecnicas destacables

- arquitectura MVC sobre Laravel 12;
- servicios de dominio para encapsular reglas de negocio;
- recursos administrativos con Filament 4;
- carga inicial de datos mediante seeders;
- importacion y exportacion de datos en Excel, CSV y PDF;
- uso de transacciones y `lockForUpdate()` en operaciones sensibles;
- soporte de permisos por modulo y accion;
- pruebas feature con PHPUnit;
- pruebas de interfaz con Playwright.

## Tecnologias utilizadas

- PHP 8.2
- Laravel 12
- Filament 4
- Tailwind CSS 4
- Vite 7
- SQLite por defecto
- compatibilidad configurable con MySQL, MariaDB, PostgreSQL y SQL Server
- PHPUnit 11
- Playwright
- Laravel DOMPDF
- Laravel Excel

## Arquitectura funcional resumida

La aplicacion concentra su interfaz en el panel `/admin`, construido con Filament. La logica de negocio sensible se delega a servicios, entre ellos:

- `app/Services/EstadoFinancieroService.php`
- `app/Services/LedgerService.php`
- `app/Services/PaymentService.php`
- `app/Services/GenericModelImportService.php`
- `app/Services/ExportacionesService.php`

Los modulos administrativos se organizan en recursos de Filament dentro de `app/Filament/Resources`, lo que facilita mantenimiento, escalabilidad y consistencia visual.

## Pruebas y calidad

El repositorio contiene pruebas automatizadas para modulos como:

- clientes;
- proveedores;
- productos;
- inventarios;
- inventario por producto;
- cuentas por cobrar;
- cuentas por pagar;
- gestion de cobros;
- activos fijos;
- asientos contables;
- estados financieros.

Tambien incluye pruebas de interfaz en `proyecto-playwright/tests` para flujos como login, usuarios, productos, inventarios, cuentas contables y gestion de cobros.

Ademas, el repositorio principal conserva evidencia documental del proceso de calidad:

- casos de prueba manuales;
- reportes Daily Scrum;
- actas de aceptacion por sprint.

## Estructura base del proyecto

```text
sistema-contable/
|-- app/
|   |-- Filament/
|   |-- Observers/
|   `-- Services/
|-- database/
|   |-- migrations/
|   |-- factories/
|   `-- seeders/
|-- proyecto-playwright/
|-- resources/
|-- routes/
`-- tests/
```

## Ejecucion local

### Requisitos

- PHP 8.2 o superior
- Composer
- Node.js y npm
- extensiones necesarias para Laravel y SQLite o el motor de base de datos elegido

### Instalacion

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm install
npm run build
php artisan serve
```

Si prefieres usar el script definido en Composer:

```bash
composer run setup
composer run dev
```

## Acceso inicial

Los seeders crean usuarios base para pruebas funcionales:

- administrador: `admin@sistema.com` / `1234`
- gerente: `gerente@sistema.com` / `1234`

## Rutas y uso general

- la raiz `/` redirige al login administrativo en `/admin/login`;
- desde el panel se accede a los modulos CRUD y a la pagina de reportes financieros;
- el sistema incluye endpoints de exportacion de reportes financieros en PDF y Excel.
