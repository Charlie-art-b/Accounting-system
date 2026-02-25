<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;

class CsvExportService
{
    public function downloadFromModel(string $modelClass, array $fields, string $filePrefix, array $fieldLabels = [])
    {
        /** @var Model $model */
        $model = new $modelClass();
        $filename = $filePrefix . '_' . now()->format('Y-m-d_H-i-s') . '.csv';

        $displayFields = empty($fieldLabels) 
            ? $fields 
            : array_map(fn($field) => $fieldLabels[$field] ?? $field, $fields);

        return response()->streamDownload(function () use ($model, $fields, $displayFields) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $displayFields);

            // Detectar relaciones necesarias (campos con puntos)
            $relations = [];
            $selectFields = [];
            
            foreach ($fields as $field) {
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
            
            // Si hay relaciones, necesitamos cargar todos los campos
            if (!empty($relations)) {
                $query->orderBy('id')
                    ->chunk(500, function ($rows) use ($handle, $fields) {
                        foreach ($rows as $row) {
                            $line = [];
                            foreach ($fields as $field) {
                                $value = data_get($row, $field);
                                if ($value instanceof \DateTimeInterface) {
                                    $value = $value->format('Y-m-d');
                                }
                                $line[] = $value;
                            }
                            fputcsv($handle, $line);
                        }
                    });
            } else {
                $query->select($selectFields)
                    ->orderBy('id')
                    ->chunk(500, function ($rows) use ($handle, $fields) {
                        foreach ($rows as $row) {
                            $line = [];
                            foreach ($fields as $field) {
                                $value = data_get($row, $field);
                                if ($value instanceof \DateTimeInterface) {
                                    $value = $value->format('Y-m-d');
                                }
                                $line[] = $value;
                            }
                            fputcsv($handle, $line);
                        }
                    });
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}

