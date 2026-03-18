# Sistema Contable Empresarial

Repositorio académico-profesional que documenta el desarrollo de un sistema contable web orientado a pequeñas y medianas empresas. El proyecto combina implementación de software, lógica contable, pruebas funcionales y evidencia de gestión ágil para demostrar competencias en desarrollo full stack y modelado de procesos financieros.

## Objetivo del proyecto

Diseñar e implementar una plataforma capaz de centralizar operaciones administrativas y contables frecuentes:

- Gestión de clientes y proveedores.
- Control de productos e inventarios.
- Cuentas por cobrar y cuentas por pagar.
- Registro y posteo de asientos contables.
- Gestión de cobros y reversión de pagos.
- Control de activos fijos.
- Generación de estados financieros y exportación de reportes.

## Qué demuestra este repositorio

Este repositorio no solo contiene código. También evidencia:

- Capacidad para traducir reglas contables a lógica de software.
- Diseño de módulos CRUD con validaciones de negocio.
- Uso de arquitectura MVC con servicios de dominio y panel administrativo.
- Implementación de roles y permisos para control de acceso.
- Automatización de pruebas funcionales y de interfaz.
- Trabajo colaborativo con artefactos de Scrum, actas y casos de prueba.

## Estructura del repositorio

- `sistema-contable/`: aplicación principal desarrollada con Laravel, Filament, Vite y Tailwind CSS.
- `Pruebas/`: casos de prueba manuales por sprint y por módulo.
- `Daily Scrum/`: reportes de seguimiento diario del proyecto.
- `Actas de Aceptación de Sprint/`: evidencia formal de revisión y aceptación incremental.

## Módulos principales del sistema

Dentro de `sistema-contable` se implementaron módulos para:

- Usuarios, roles y permisos.
- Clientes y proveedores.
- Productos e inventarios.
- Inventario por producto.
- Cuentas por cobrar.
- Cuentas por pagar.
- Gestión de cobros.
- Activos fijos.
- Catálogo de cuentas contables.
- Asientos contables.
- Reportes financieros.

## Stack tecnológico

- PHP 8.2
- Laravel 12
- Filament 4
- Tailwind CSS 4
- Vite 7
- SQLite por defecto, con soporte configurable para MySQL, MariaDB, PostgreSQL y SQL Server
- PHPUnit
- Playwright
- Laravel DOMPDF
- Laravel Excel
- Spatie Laravel Permission

## Dónde revisar el sistema

La documentación técnica y operativa más importante del producto está en:

- [`sistema-contable/README.md`]

Ahí se describe la arquitectura, módulos, ejecución local, credenciales semilla y enfoque contable implementado.
