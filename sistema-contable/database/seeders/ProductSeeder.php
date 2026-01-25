<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            [
                'name' => 'Laptop Dell Inspiron 15',
                'description' => 'Laptop de 15 pulgadas con procesador Intel Core i5, 8GB RAM, 256GB SSD',
                'supplier_id' => 1,
            ],
            [
                'name' => 'Monitor LG 24"',
                'description' => 'Monitor LED de 24 pulgadas Full HD con resolución 1920x1080',
                'supplier_id' => 2,
            ],
            [
                'name' => 'Teclado Mecánico Logitech',
                'description' => 'Teclado mecánico con switches rojos, retroiluminación RGB',
                'supplier_id' => 3,
            ],
            [
                'name' => 'Mouse Inalámbrico Microsoft',
                'description' => 'Mouse óptico inalámbrico con batería recargable',
                'supplier_id' => 4,
            ],
            [
                'name' => 'Impresora HP LaserJet',
                'description' => 'Impresora láser monocromática de alta velocidad',
                'supplier_id' => 5,
            ],
            [
                'name' => 'Router TP-Link AC1200',
                'description' => 'Router inalámbrico dual banda con velocidad de hasta 1200 Mbps',
                'supplier_id' => 1,
            ],
            [
                'name' => 'Disco Duro Externo 1TB',
                'description' => 'Disco duro externo USB 3.0 de 1TB de capacidad',
                'supplier_id' => 2,
            ],
            [
                'name' => 'Webcam HD Logitech',
                'description' => 'Cámara web Full HD 1080p con micrófono integrado',
                'supplier_id' => 3,
            ],
            [
                'name' => 'Audífonos Sony WH-1000XM4',
                'description' => 'Audífonos inalámbricos con cancelación de ruido activa',
                'supplier_id' => 4,
            ],
            [
                'name' => 'Proyector Epson EB-S41',
                'description' => 'Proyector SVGA de 3.400 lúmenes para presentaciones',
                'supplier_id' => 5,
            ],
        ];

        foreach ($products as $product) {
            Product::updateOrCreate(
                ['name' => $product['name']],
                $product
            );
        }
    }
}
