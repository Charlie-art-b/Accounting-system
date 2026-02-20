# 🚀 CÓMO EJECUTAR LAS PRUEBAS - Guía Rápida

## 3 Formas de Hacer las Pruebas

---

### **OPCIÓN 1: Comando Artisan (MÁS FÁCIL)** ⭐

```bash
# Ejecutar todas las pruebas
php artisan estados:test

# Resultado esperado:
# ══════════════════════════════════════════════
#   Pruebas de Estados Financieros
# ══════════════════════════════════════════════
# 
# ▶ Ejecutando EstadoFinancieroServiceTest
# ✓ EstadoFinancieroServiceTest: PASADO
#
# ▶ Ejecutando AnalisisDeudoresServiceTest
# ✓ AnalisisDeudoresServiceTest: PASADO
#
# ▶ Ejecutando AnalisisAcreedoresServiceTest
# ✓ AnalisisAcreedoresServiceTest: PASADO
#
# ══════════════════════════════════════════════
# Total de tests ejecutados: 3
# ✓ Tests pasados: 3
# ══════════════════════════════════════════════
```

---

### **OPCIÓN 2: PHPUnit Directo** 

```bash
# Correr todos los tests de estados financieros
vendor/bin/phpunit tests/Feature/EstadoFinancieroServiceTest.php --colors
vendor/bin/phpunit tests/Feature/AnalisisDeudoresServiceTest.php --colors
vendor/bin/phpunit tests/Feature/AnalisisAcreedoresServiceTest.php --colors

# O todo junto:
vendor/bin/phpunit tests/Feature/ --filter "Estado\|Deudores\|Acreedores" --colors
```

---

### **OPCIÓN 3: Script Manual (Sin PHPUnit)** 

```bash
# Ejecutar prueba manual que crea datos y verifica todo
php tests/scripts/manual_test.php

# Resultado esperado:
# ════════════════════════════════════════════════════
#    PRUEBAS MANUALES - ESTADOS FINANCIEROS
# ════════════════════════════════════════════════════
#
# ▶ Creando cliente de prueba
# ✓ Cliente creado: Cliente Prueba (ID: 1)
#
# ▶ Creando plan de cuentas
# ✓ Cuenta 1100 - Caja
# ✓ Cuenta 1110 - Bancos
# ... (más cuentas)
#
# ▶ Registrando transacciones
# ✓ Asiento 1: Aporte de capital 100,000
# ✓ Asiento 2: Ventas 50,000
# ✓ Asiento 3: Salarios 15,000
#
# ▶ Generando estados financieros
# ✓ Balance General generado
#   - Total Activos: 135,000.00
#   - Total Pasivos: 0.00
#   - Patrimonio: 135,000.00
#   - Ecuación balanceada: Sí ✓
# ...
```

---

## 📋 Paso a Paso: OPCIÓN 1 (Recomendada)

### Paso 1: Abre la terminal
```
Navega a: C:\proyectos\grupo_1_proyecto_2026\sistema-contable
```

### Paso 2: Ejecuta el comando
```bash
php artisan estados:test
```

### Paso 3: Espera a que terminen las pruebas
Las pruebas automáticamente:
- ✓ Crean un cliente
- ✓ Crean cuentas contables
- ✓ Registran transacciones
- ✓ Generan estados financieros
- ✓ Validan que todo funcione

### Paso 4: Verifica el resultado
- Si ves "✓ Tests pasados: 3" → **TODO FUNCIONA** ✓
- Si ves "✗ Tests fallidos" → Revisa el error mostrado

---

## 📊 Qué Prueban los Tests

### EstadoFinancieroServiceTest (11 pruebas)
```
✓ Generar Balance General
✓ Generar Estado de Resultados  
✓ Generar Balance de Comprobación
✓ Generar Ratios Financieros
✓ Validar estructura de datos
✓ Soportar múltiples clientes
✓ Usar fechas comparativas
```

### AnalisisDeudoresServiceTest (10 pruebas)
```
✓ Resumen de Cuentas por Cobrar
✓ Clasificación por antigüedad (30/60/90 días)
✓ Análisis por cliente
✓ Indicadores de cobranza
✓ Evaluación de riesgo
✓ Cálculos correctos
```

### AnalisisAcreedoresServiceTest (11 pruebas)
```
✓ Resumen de Cuentas por Pagar
✓ Clasificación por vencimiento
✓ Análisis por proveedor
✓ Proyección de flujo de caja
✓ Análisis de condiciones de pago
✓ Calificación de proveedores
```

---

## 🎯 Total: 32 Pruebas

Probar que el sistema funciona correctamente en:
- ✓ 12 cuentas contables
- ✓ 4+ asientos con transacciones
- ✓ 3+ estados financieros diferentes
- ✓ 5+ cuentas por cobrar
- ✓ 5+ cuentas por pagar
- ✓ Cálculos de ratios y análisis
- ✓ Validaciones de integridad

---

## 🔥 Soluciones a Errores Comunes

### Error: "Class not found"
```bash
composer dump-autoload
php artisan estados:test
```

### Error: "Database error"
Asegúrate que Laravel está instalado:
```bash
php artisan migrate
php artisan estados:test
```

### Error: "Command not found"
El comando se registra automáticamente. Si no funciona:
```bash
php artisan list | grep estados
```

---

## 💡 TIPS

1. **Primera vez?** Usa OPCIÓN 1 (más simple)
2. **Necesitas más detalle?** Agrega `--verbose`:
   ```bash
   php artisan estados:test --verbose
   ```
3. **Quieres probar un servicio específico?**
   ```bash
   php artisan estados:test --test=estados
   php artisan estados:test --test=deudores
   php artisan estados:test --test=acreedores
   ```
4. **Debes ver datos creados?** Usa OPCIÓN 3 (manual_test.php)

---

## ✅ Verificación Final

Si todos los tests pasan:

```
Total de tests ejecutados: 3
✓ Tests pasados: 3
```

**¡FELICIDADES!** Sistema está 100% funcional ✓

---

## 📞 Ayuda Rápida

| Situación | Comando |
|-----------|---------|
| Ejecutar todas las pruebas | `php artisan estados:test` |
| Ver más detalles | `php artisan estados:test --verbose` |
| Probar Balance General | `php artisan estados:test --test=estados` |
| Probar CxC | `php artisan estados:test --test=deudores` |
| Probar CxP | `php artisan estados:test --test=acreedores` |
| Prueba manual | `php tests/scripts/manual_test.php` |
| Limpiar caché | `php artisan cache:clear` |

---

**Creado:** Febrero 2026
**Última actualización:** 19/02/2026
