<?php

namespace App\Exports;

use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class GenericModelExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    public function __construct(
        private readonly string $modelClass,
        private readonly array $fields,
        private readonly array $fieldLabels = [],
    ) {}

    public function collection()
    {
        /** @var Model $model */
        $model = new $this->modelClass();

        // Detectar relaciones necesarias (campos con puntos)
        $relations = [];
        $selectFields = [];
        
        foreach ($this->fields as $field) {
            if (str_contains($field, '.')) {
                $relation = explode('.', $field)[0];
                $relations[] = $relation;
            } else {
                $selectFields[] = $field;
            }
        }

        $query = $model->newQuery();
        
        if (!empty($relations)) {
            $query->with(array_unique($relations));
        }
        
        // Si hay relaciones, necesitamos seleccionar el ID y las foreign keys también
        if (!empty($relations)) {
            return $query->orderBy('id')->get();
        }
        
        return $query->select($selectFields)->orderBy('id')->get();
    }

    public function headings(): array
    {
        if (empty($this->fieldLabels)) {
            return $this->fields;
        }

        return array_map(
            fn($field) => $this->fieldLabels[$field] ?? $field,
            $this->fields
        );
    }

    public function map($row): array
    {
        $data = [];

        foreach ($this->fields as $field) {
            $value = data_get($row, $field);

            if ($value instanceof \DateTimeInterface) {
                $value = $value->format('Y-m-d');
            }

            $data[] = $value;
        }

        return $data;
    }
}

