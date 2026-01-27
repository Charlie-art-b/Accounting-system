# Pruebas Unitarias del Modelo Customer

Este documento describe las pruebas unitarias implementadas para el modelo `Customer`, enfocándose en las funcionalidades de **crear** y **editar** clientes.

## Ubicación

Las pruebas se encuentran en: `tests/Unit/Models/CustomerTest.php`

## Cómo Ejecutar las Pruebas

### Ejecutar todas las pruebas del modelo Customer
```bash
php artisan test tests/Unit/Models/CustomerTest.php
```

### Ejecutar todas las pruebas del proyecto
```bash
php artisan test
```

### Ejecutar solo pruebas unitarias
```bash
php artisan test tests/Unit
```

## Estructura de las Pruebas

### 1. Pruebas de Creación (CREATE)

#### `test_crear_customer_con_datos_validos`
Verifica que se puede crear un cliente con todos los datos válidos y que estos se guardan correctamente.

**Datos testeados:**
- Nombre, apellidos
- Tipo de identificación (identification, dimex, passport)
- Número de identificación
- Email
- Teléfono
- Dirección
- Tipo de cliente (individual, legal_person)
- Estado (activo/inactivo)
- Notas

#### `test_email_se_guarda_en_minusculas`
Verifica que los emails se guardan siempre en minúsculas, sin importar cómo se ingresen.

**Ejemplo:**
- Input: `JUAN@EXAMPLE.COM`
- Output: `juan@example.com`

#### `test_email_se_trimea`
Verifica que se eliminan espacios en blanco al inicio y final del email.

**Ejemplo:**
- Input: `  maria@example.com  `
- Output: `maria@example.com`

#### `test_crear_customer_con_id_type_dimex`
Prueba creación de cliente con identificación tipo DIMEX.

#### `test_crear_customer_con_id_type_passport`
Prueba creación de cliente con identificación tipo Pasaporte.

#### `test_crear_customer_tipo_persona_juridica`
Verifica la creación de clientes tipo persona jurídica (empresa).

#### `test_estado_por_defecto_es_activo`
Confirma que cuando no se especifica el estado, el cliente se crea activo (true).

#### `test_crear_customer_inactivo`
Prueba la creación de un cliente inactivo explícitamente.

### 2. Pruebas de Actualización (UPDATE)

#### `test_editar_nombre_cliente`
Verifica que se puede editar el nombre del cliente.

#### `test_editar_apellidos_cliente`
Prueba la actualización de primer y segundo apellido.

#### `test_editar_email_cliente`
Verifica que al editar el email, se aplican las transformaciones (minúsculas, trim).

**Ejemplo:**
- Input: `  LAURA.NUEVA@EXAMPLE.COM  `
- Output: `laura.nueva@example.com`

#### `test_editar_telefono_cliente`
Prueba la actualización del número telefónico.

#### `test_editar_direccion_cliente`
Verifica la actualización de la dirección.

#### `test_editar_notas_cliente`
Prueba la edición del campo de notas.

#### `test_editar_estado_activo_a_inactivo`
Verifica el cambio de estado de activo a inactivo.

#### `test_editar_estado_inactivo_a_activo`
Prueba el cambio de estado de inactivo a activo.

#### `test_editar_tipo_cliente`
Verifica el cambio de tipo de cliente (individual ↔ legal_person).

#### `test_editar_multiples_campos`
Prueba la actualización simultánea de varios campos:
- Nombre
- Apellidos
- Teléfono
- Dirección
- Estado
- Notas

### 3. Pruebas de Validación de Integridad

#### `test_identificacion_debe_ser_unica`
Verifica que no se pueden crear dos clientes con el mismo número de identificación.

**Comportamiento esperado:** Se genera una excepción `QueryException`.

#### `test_email_debe_ser_unico`
Prueba que no se permiten emails duplicados en la base de datos.

**Comportamiento esperado:** Se genera una excepción `QueryException`.

### 4. Pruebas de Configuración del Modelo

#### `test_fillable_fields_configurados`
Verifica que todos los campos esperados están en el array `fillable` del modelo.

**Campos validados:**
- name
- first_last_name
- second_last_name
- id_type
- identification
- email
- phone
- address
- customer_type
- status
- notes

#### `test_casts_configurados`
Comprueba que los tipos de datos (casts) están correctamente configurados:
- `status` → booleano
- `customer_type` → string

#### `test_obtener_customer_por_id`
Verifica que se puede recuperar un cliente por su ID.

#### `test_actualizar_y_refrescar_customer`
Prueba el ciclo completo de actualizar un cliente y refrescarlo desde la base de datos.

## Resultados de las Pruebas

```
PASS  Tests\Unit\Models\CustomerTest

✓ crear customer con datos validos
✓ email se guarda en minusculas
✓ email se trimea
✓ crear customer con id type dimex
✓ crear customer con id type passport
✓ crear customer tipo persona juridica
✓ estado por defecto es activo
✓ crear customer inactivo
✓ editar nombre cliente
✓ editar apellidos cliente
✓ editar email cliente
✓ editar telefono cliente
✓ editar direccion cliente
✓ editar notas cliente
✓ editar estado activo a inactivo
✓ editar estado inactivo a activo
✓ editar tipo cliente
✓ editar multiples campos
✓ identificacion debe ser unica
✓ email debe ser unico
✓ fillable fields configurados
✓ casts configurados
✓ obtener customer por id
✓ actualizar y refrescar customer

Tests: 24 passed (65 assertions)
Duration: 1.76s
```

## Notas Técnicas

### DatabaseMigrations Trait
Las pruebas utilizan el trait `DatabaseMigrations` que:
- Ejecuta las migraciones antes de cada prueba
- Utiliza una BD SQLite en memoria
- Revierte las migraciones después de cada prueba

Esto asegura que:
- Las pruebas sean independientes
- No haya efectos secundarios entre pruebas
- La BD esté siempre en un estado limpio

### Transformaciones de Datos
El modelo Customer aplica automáticamente transformaciones al email:
- Convierte a minúsculas
- Elimina espacios en blanco

Estas transformaciones se definen en el método `email()` usando el casting de atributos de Eloquent.

### Validación de Integridad
Las pruebas de unicidad verifican:
- No se pueden crear dos clientes con el mismo `identification`
- No se pueden crear dos clientes con el mismo `email`

Estos contraints están definidos en la migración y en la BD.

## Extensiones Futuras

Se pueden añadir más pruebas para:
- Validaciones de campos (máximo de caracteres, formatos)
- Relaciones del modelo (suppliers)
- Métodos personalizados del modelo
- Pruebas de integración con Filament
- Pruebas de API/Controllers

## Mantenimiento

Cuando se hagan cambios al modelo Customer:
1. Actualizar las pruebas correspondientes
2. Añadir nuevas pruebas para nuevas funcionalidades
3. Ejecutar siempre `php artisan test` antes de hacer commit
4. Mantener una cobertura de código alta (>80%)
