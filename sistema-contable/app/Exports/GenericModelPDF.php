<?php

namespace App\Exports;

use App\Services\PdfFallbackService;
use App\Services\SimplePdfService;
use Illuminate\Database\Eloquent\Model;

class GenericModelPDF
{
    public function __construct(
        private readonly string $modelClass,
        private readonly array $fields,
        private readonly string $title,
        private readonly string $filePrefix,
        private readonly array $fieldLabels = [],
    ) {}

    public function download()
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
        
        // Si hay relaciones, necesitamos cargar todos los campos
        if (!empty($relations)) {
            $records = $query->orderBy('id')->get();
        } else {
            $records = $query->select($selectFields)->orderBy('id')->get();
        }

        $displayFields = empty($this->fieldLabels) 
            ? $this->fields 
            : array_map(fn($field) => $this->fieldLabels[$field] ?? $field, $this->fields);

        $viewData = [
            'title' => $this->title,
            'fields' => $this->fields,
            'displayFields' => $displayFields,
            'records' => $records,
        ];

        $pdfFacade = '\Barryvdh\DomPDF\Facade\Pdf';
        if (! class_exists($pdfFacade)) {
            $lines = [];
            $lines[] = $this->title;
            $lines[] = 'FORMATO IMPORTABLE';
            $lines[] = implode('|', $displayFields);

            foreach ($records as $record) {
                $row = [];
                foreach ($this->fields as $field) {
                    $value = data_get($record, $field);
                    if ($value instanceof \DateTimeInterface) {
                        $value = $value->format('Y-m-d');
                    }
                    $row[] = str_replace('|', ' ', trim((string) $value));
                }
                $lines[] = implode('|', $row);
            }

            $pdfBytes = app(SimplePdfService::class)->fromLines($lines);
            return response()->streamDownload(function () use ($pdfBytes) {
                echo $pdfBytes;
            }, $this->filePrefix . '_' . now()->format('Y-m-d_H-i-s') . '.pdf', [
                'Content-Type' => 'application/pdf',
            ]);
        }

        return app(PdfFallbackService::class)->download(
            view: 'exports.generic-model-pdf',
            data: $viewData,
            baseFileName: $this->filePrefix . '_' . now()->format('Y-m-d_H-i-s'),
            paper: 'a4',
            orientation: 'landscape',
        );
    }
}
