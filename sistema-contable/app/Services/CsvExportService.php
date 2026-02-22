<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;

class CsvExportService
{
    public function downloadFromModel(string $modelClass, array $fields, string $filePrefix)
    {
        /** @var Model $model */
        $model = new $modelClass();
        $filename = $filePrefix . '_' . now()->format('Y-m-d_H-i-s') . '.csv';

        return response()->streamDownload(function () use ($model, $fields) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $fields);

            $model->newQuery()
                ->select($fields)
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

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}

