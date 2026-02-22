<?php

namespace App\Services;

use App\Imports\GenericRowsImport;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GenericModelImportService
{
    public function importFromExcel(
        string $modelClass,
        string $filePath,
        array $fields,
        array $uniqueBy = [],
        bool $updateExisting = true,
        array $defaults = [],
        array $enumMaps = [],
        array $requiredFields = []
    ): array {
        $import = new GenericRowsImport();
        $rows = $this->readTabularRows($filePath, $import, $fields, $requiredFields);

        return $this->importRows($modelClass, $rows, $fields, $uniqueBy, $updateExisting, $defaults, $enumMaps, $requiredFields);
    }

    public function importFromPdf(
        string $modelClass,
        string $filePath,
        array $fields,
        array $uniqueBy = [],
        bool $updateExisting = true,
        array $defaults = [],
        array $enumMaps = [],
        array $requiredFields = []
    ): array {
        $rawText = app(PdfTextExtractorService::class)->extractText($filePath);
        $rows = $this->parsePdfRows($rawText, $fields, $requiredFields);

        if ($rows->isEmpty()) {
            throw ValidationException::withMessages([
                'file' => 'No se detectaron registros validos en el PDF. Use el PDF exportado por el sistema con encabezado delimitado.',
            ]);
        }

        return $this->importRows($modelClass, $rows, $fields, $uniqueBy, $updateExisting, $defaults, $enumMaps, $requiredFields);
    }

    private function importRows(
        string $modelClass,
        Collection $rows,
        array $fields,
        array $uniqueBy,
        bool $updateExisting,
        array $defaults,
        array $enumMaps,
        array $requiredFields
    ): array {
        /** @var Model $model */
        $model = new $modelClass();
        $fillable = array_values(array_intersect($fields, $model->getFillable()));
        $casts = $model->getCasts();

        $created = 0;
        $updated = 0;
        $skipped = 0;
        $errors = [];

        DB::transaction(function () use (
            $rows,
            $model,
            $fillable,
            $casts,
            $uniqueBy,
            $updateExisting,
            $defaults,
            $enumMaps,
            $requiredFields,
            &$created,
            &$updated,
            &$skipped,
            &$errors
        ) {
            foreach ($rows as $index => $rawRow) {
                $rowNumber = (int) $index + 2;
                $row = $this->normalizeRow($rawRow);

                $payload = [];
                foreach ($fillable as $field) {
                    $value = $row[$field] ?? $defaults[$field] ?? null;
                    $payload[$field] = $this->normalizeValue($value, $field, $casts, $enumMaps);
                }

                $payload = array_filter($payload, fn ($value) => $value !== null && $value !== '');

                if (empty($payload)) {
                    $skipped++;
                    continue;
                }

                $missingRequired = [];
                foreach ($requiredFields as $requiredField) {
                    $value = $payload[$requiredField] ?? null;
                    if ($value === null || $value === '') {
                        $missingRequired[] = $requiredField;
                    }
                }

                if (! empty($missingRequired)) {
                    $errors[] = "Fila {$rowNumber}: faltan campos requeridos (" . implode(', ', $missingRequired) . ').';
                    continue;
                }

                try {
                    $query = $model->newQuery();
                    $canMatch = ! empty($uniqueBy);

                    foreach ($uniqueBy as $key) {
                        if (! array_key_exists($key, $payload)) {
                            $canMatch = false;
                            break;
                        }
                        $query->where($key, $payload[$key]);
                    }

                    $existing = $canMatch ? $query->first() : null;

                    if ($existing) {
                        if (! $updateExisting) {
                            $skipped++;
                            continue;
                        }

                        $existing->fill($payload);
                        $existing->save();
                        $updated++;
                    } else {
                        $model->newQuery()->create($payload);
                        $created++;
                    }
                } catch (\Throwable $e) {
                    $errors[] = "Fila {$rowNumber}: {$e->getMessage()}";
                }
            }
        });

        return [
            'created' => $created,
            'updated' => $updated,
            'skipped' => $skipped,
            'errors' => $errors,
        ];
    }

    private function readTabularRows(
        string $filePath,
        GenericRowsImport $import,
        array $fields,
        array $requiredFields = []
    ): Collection
    {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $excelFacade = '\Maatwebsite\Excel\Facades\Excel';

        if (class_exists($excelFacade)) {
            $excelFacade::import($import, $filePath);
            return $this->validateTabularRows($import->rows, $fields, $requiredFields, 'Excel');
        }

        if ($extension !== 'csv') {
            throw ValidationException::withMessages([
                'file' => 'La libreria de Excel no esta disponible. Use archivo CSV o instale maatwebsite/excel.',
            ]);
        }

        return $this->readCsvRows($filePath, $fields, $requiredFields);
    }

    private function readCsvRows(string $filePath, array $fields, array $requiredFields = []): Collection
    {
        $handle = fopen($filePath, 'r');
        if (! $handle) {
            throw ValidationException::withMessages([
                'file' => 'No se pudo leer el archivo CSV.',
            ]);
        }

        $rows = collect();
        $headers = null;

        while (($line = fgetcsv($handle)) !== false) {
            if ($headers === null) {
                $headers = array_map(fn ($v) => $this->normalizeKey((string) $v), $line);
                if (isset($headers[0])) {
                    $headers[0] = ltrim($headers[0], "\xEF\xBB\xBF");
                }

                $this->assertHeaders($headers, $fields, $requiredFields, 'CSV');
                continue;
            }

            $row = [];
            foreach ($headers as $index => $key) {
                $row[$key] = $line[$index] ?? null;
            }
            $rows->push($row);
        }

        fclose($handle);
        return $this->validateTabularRows($rows, $fields, $requiredFields, 'CSV');
    }

    private function assertHeaders(array $headers, array $fields, array $requiredFields, string $source): void
    {
        $available = array_values(array_unique($headers));
        $validCount = count(array_intersect($available, $fields));
        $requiredMissing = array_values(array_diff($requiredFields, $available));

        if (! empty($requiredMissing)) {
            throw ValidationException::withMessages([
                'file' => "{$source}: faltan columnas requeridas en encabezado (" . implode(', ', $requiredMissing) . ').',
            ]);
        }

        if ($validCount < max(2, (int) floor(count($fields) / 3))) {
            throw ValidationException::withMessages([
                'file' => "{$source}: encabezado invalido. Use el archivo exportado por el sistema.",
            ]);
        }
    }

    private function validateTabularRows(Collection $rows, array $fields, array $requiredFields, string $source): Collection
    {
        if ($rows->isEmpty()) {
            throw ValidationException::withMessages([
                'file' => "{$source}: el archivo no contiene filas de datos.",
            ]);
        }

        $firstRow = $this->normalizeRow($rows->first());
        $headers = array_keys($firstRow);
        $this->assertHeaders($headers, $fields, $requiredFields, $source);

        return $rows;
    }

    private function parsePdfRows(string $rawText, array $fields, array $requiredFields = []): Collection
    {
        $lines = preg_split('/\R+/', $rawText) ?: [];
        $rows = collect();

        $header = null;
        $headerDelimiter = null;

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            $delimiter = $this->detectDelimiter($line);
            if (! $delimiter) {
                continue;
            }

            $parts = array_map(fn ($v) => trim((string) $v), str_getcsv($line, $delimiter));

            if ($header === null) {
                $headerCandidate = array_map(fn ($v) => $this->normalizeKey($v), $parts);

                $matched = count(array_intersect($headerCandidate, $fields));
                $requiredMatched = empty($requiredFields)
                    ? true
                    : count(array_intersect($headerCandidate, $requiredFields)) === count($requiredFields);

                if ($requiredMatched && $matched >= max(2, (int) floor(count($fields) / 2))) {
                    $header = $headerCandidate;
                    $headerDelimiter = $delimiter;
                }
                continue;
            }

            if ($delimiter !== $headerDelimiter) {
                continue;
            }

            if (count($parts) < count($header)) {
                continue;
            }

            $row = [];
            foreach ($header as $index => $column) {
                if (! in_array($column, $fields, true)) {
                    continue;
                }
                $row[$column] = $parts[$index] ?? null;
            }

            if (! empty($row)) {
                $rows->push($row);
            }
        }

        return $rows;
    }

    private function detectDelimiter(string $line): ?string
    {
        foreach (['|', ';', ','] as $delimiter) {
            if (str_contains($line, $delimiter)) {
                return $delimiter;
            }
        }

        return null;
    }

    private function normalizeRow(mixed $rawRow): array
    {
        $row = is_array($rawRow) ? $rawRow : (method_exists($rawRow, 'toArray') ? $rawRow->toArray() : (array) $rawRow);
        $normalized = [];

        foreach ($row as $key => $value) {
            $normalized[$this->normalizeKey((string) $key)] = is_string($value) ? trim($value) : $value;
        }

        return $normalized;
    }

    private function normalizeKey(string $key): string
    {
        $key = strtolower(trim($key));
        $key = str_replace([' ', '-'], '_', $key);
        return $key;
    }

    private function normalizeValue(mixed $value, string $field, array $casts, array $enumMaps): mixed
    {
        if (is_string($value)) {
            $value = trim($value);
        }

        if ($value === '') {
            return null;
        }

        if (array_key_exists($field, $enumMaps) && is_array($enumMaps[$field])) {
            $map = [];
            foreach ($enumMaps[$field] as $from => $to) {
                $map[strtolower((string) $from)] = $to;
            }

            $key = strtolower((string) $value);
            if (array_key_exists($key, $map)) {
                $value = $map[$key];
            }
        }

        $cast = $casts[$field] ?? null;
        if (in_array($cast, ['bool', 'boolean'], true)) {
            return $this->normalizeBoolean($value);
        }

        return $value;
    }

    private function normalizeBoolean(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (int) $value === 1;
        }

        $value = strtolower(trim((string) $value));
        return in_array($value, ['1', 'true', 'si', 'yes', 'activo', 'activa'], true);
    }
}
