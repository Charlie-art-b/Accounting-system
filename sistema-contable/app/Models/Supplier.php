<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    /**
     * Tabla asociada al modelo
     */
    protected $table = 'suppliers';

    /**
     * Atributos que se pueden llenar masivamente
     */
    protected $fillable = [
        'tipo_proveedor',
        'nombre_razon_social',
        'identificacion',
        'correo',
        'telefono',
        'estado',
    ];

    /**
     * Atributos visible
     */
    protected $visible = [
        'id',
        'tipo_proveedor',
        'nombre_razon_social',
        'identificacion',
        'correo',
        'telefono',
        'estado',
        'created_at',
        'updated_at',
    ];

    /**
     * Tipos de proveedor disponibles
     */
    public static array $tiposProveedor = [
        'persona' => 'Persona',
        'empresa' => 'Empresa',
    ];

    /**
     * Estados disponibles
     */
    public static array $estados = [
        'activo' => 'Activo',
        'inactivo' => 'Inactivo',
    ];

    /**
     * Mutadores: Convertir a minúsculas el correo
     */
    protected function correo(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            set: fn($value) => strtolower(trim($value))
        );
    }

    /**
     * Mutadores: Capitalizar nombre
     */
    protected function nombreRazonSocial(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            set: fn($value) => ucfirst(trim($value))
        );
    }

    /**
     * Validar que el identificación sea válida
     */
    public static function validarIdentificacion(string $identificacion): bool
    {
        // Solo números y guiones
        if (!preg_match('/^[0-9\-]+$/', $identificacion)) {
            return false;
        }

        // Mínimo 6 caracteres
        if (strlen($identificacion) < 6) {
            return false;
        }

        return true;
    }

    /**
     * Validar correo electrónico
     */
    public static function validarCorreo(string $correo): bool
    {
        return filter_var($correo, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Relación: Un proveedor pertenece a muchos clientes
     */
    public function customers()
    {
        return $this->belongsToMany(Customer::class, 'customer_supplier')
                    ->withTimestamps();
    }

    /**
     * Relación: Un proveedor tiene muchas cuentas por pagar
     */
    public function cuentasPorPagar()
    {
        return $this->hasMany(CuentaPorPagar::class, 'supplier_id');
    }

    /**
     * Relación: Un proveedor tiene muchos productos
     */
    public function productos()
    {
        return $this->hasMany(Producto::class, 'supplier_id');
    }

    /**
     * Verificar si el proveedor puede ser eliminado
     */
    public function puedeSerEliminado(): bool
    {
        // No eliminar si tiene cuentas por pagar
        if ($this->cuentasPorPagar()->exists()) {
            return false;
        }

        // No eliminar si tiene productos
        if ($this->productos()->exists()) {
            return false;
        }

        return true;
    }

    /**
     * Obtener mensaje de error si no puede ser eliminado
     */
    public function getMensajeNoEliminacion(): string
    {
        if ($this->cuentasPorPagar()->exists()) {
            return "No se puede eliminar: tiene cuentas por pagar asociadas.";
        }

        if ($this->productos()->exists()) {
            return "No se puede eliminar: tiene productos en inventario.";
        }

        return "No se puede eliminar este proveedor.";
    }
}


