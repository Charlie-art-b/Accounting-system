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
}

