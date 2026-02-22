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
    ) {}

    public function collection()
    {
        /** @var Model $model */
        $model = new $this->modelClass();

        return $model->newQuery()
            ->select($this->fields)
            ->orderBy('id')
            ->get();
    }

    public function headings(): array
    {
        return $this->fields;
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

