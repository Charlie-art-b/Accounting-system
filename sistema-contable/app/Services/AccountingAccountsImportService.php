<?php

namespace App\Services;

use App\Imports\AccountingAccountsImport;
use App\Models\AccountingAccount;
use App\Models\Customer;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AccountingAccountsImportService
{
    public function importFromExcel(string $filePath, int $defaultCustomerId, bool $updateExisting = true): array
    {
        $this->assertCustomerExists($defaultCustomerId);

        $import = new AccountingAccountsImport();
        $rows = $this->readTabularRows($filePath, $import);

        return $this->importRows($rows, $defaultCustomerId, $updateExisting);
    }

    public function importFromPdf(string $filePath, int $defaultCustomerId, bool $updateExisting = true): array
    {
        $this->assertCustomerExists($defaultCustomerId);

        $rawText = app(PdfTextExtractorService::class)->extractText($filePath);
        $rows = $this->parsePdfRows($rawText);

        if ($rows->isEmpty()) {
            throw ValidationException::withMessages([
                'file' => 'No se detectaron registros validos en el PDF. Use lineas con formato: codigo|nombre|tipo|clasificacion|naturaleza|estado|seccion|parent_code',
            ]);
        }

        return $this->importRows($rows, $defaultCustomerId, $updateExisting);
    }

    private function importRows(Collection $rows, int $defaultCustomerId, bool $updateExisting): array
    {
        $created = 0;
        $updated = 0;
        $skipped = 0;
        $errors = [];
        $parentLinks = [];

        DB::transaction(function () use (
            $rows,
            $defaultCustomerId,
            $updateExisting,
            &$created,
            &$updated,
            &$skipped,
            &$errors,
            &$parentLinks
        ) {
            foreach ($rows as $index => $rawRow) {
                $rowNumber = (int) $index + 2;
                $row = $this->normalizeRow($rawRow);

                $customerId = (int) ($this->value($row, ['customer_id', 'cliente_id']) ?: $defaultCustomerId);
                $code = trim((string) $this->value($row, ['code', 'codigo']));
                $name = trim((string) $this->value($row, ['name', 'nombre']));

                if (! $customerId || $code === '' || $name === '') {
                    $errors[] = "Fila {$rowNumber}: customer_id, code y name son obligatorios.";
                    continue;
                }

                $type = $this->normalizeType((string) $this->value($row, ['type', 'tipo']));
                if ($type === null) {
                    $errors[] = "Fila {$rowNumber}: tipo invalido.";
                    continue;
                }

                $classification = $this->normalizeClassification((string) $this->value($row, ['classification', 'clasificacion']));
                if ($classification === false) {
                    $errors[] = "Fila {$rowNumber}: clasificacion invalida.";
                    continue;
                }

                $normalBalance = $this->normalizeNormalBalance((string) $this->value($row, ['normal_balance', 'naturaleza']), $type);
                if ($normalBalance === null) {
                    $errors[] = "Fila {$rowNumber}: naturaleza invalida.";
                    continue;
                }

                $status = $this->normalizeStatus((string) $this->value($row, ['status', 'estado']));
                if ($status === null) {
                    $errors[] = "Fila {$rowNumber}: estado invalido.";
                    continue;
                }

                $record = AccountingAccount::query()
                    ->where('customer_id', $customerId)
                    ->where('code', $code)
                    ->first();

                $payload = [
                    'customer_id' => $customerId,
                    'code' => $code,
                    'name' => $name,
                    'type' => $type,
                    'classification' => $classification ?: null,
                    'report_section' => $this->toNullable($this->value($row, ['report_section', 'seccion'])),
                    'normal_balance' => $normalBalance,
                    'status' => $status,
                    'level' => $this->normalizeLevel($this->value($row, ['level', 'nivel'])),
                ];

                if ($record) {
                    if (! $updateExisting) {
                        $skipped++;
                        continue;
                    }

                    $record->fill($payload);
                    $record->save();
                    $updated++;
                } else {
                    $record = AccountingAccount::create($payload);
                    $created++;
                }

                $parentCode = trim((string) $this->value($row, ['parent_code', 'codigo_padre']));
                $parentId = (int) ($this->value($row, ['parent_id']) ?? 0);

                if ($parentId > 0) {
                    $parentLinks[] = [
                        'record_id' => $record->id,
                        'customer_id' => $customerId,
                        'parent_id' => $parentId,
                        'parent_code' => null,
                    ];
                    continue;
                }

                if ($parentCode !== '') {
                    $parentLinks[] = [
                        'record_id' => $record->id,
                        'customer_id' => $customerId,
                        'parent_id' => null,
                        'parent_code' => $parentCode,
                    ];
                }
            }

            foreach ($parentLinks as $link) {
                $record = AccountingAccount::find($link['record_id']);
                $parent = null;

                if (! empty($link['parent_id'])) {
                    $parent = AccountingAccount::query()
                        ->where('customer_id', $link['customer_id'])
                        ->where('id', $link['parent_id'])
                        ->first();
                } elseif (! empty($link['parent_code'])) {
                    $parent = AccountingAccount::query()
                        ->where('customer_id', $link['customer_id'])
                        ->where('code', $link['parent_code'])
                        ->first();
                }

                if (! $record || ! $parent) {
                    continue;
                }

                $record->parent_id = $parent->id;
                $record->level = ($parent->level ?? 1) + 1;
                $record->save();
            }
        });

        return [
            'created' => $created,
            'updated' => $updated,
            'skipped' => $skipped,
            'errors' => $errors,
        ];
    }

    private function readTabularRows(string $filePath, AccountingAccountsImport $import): Collection
    {
        $requiredFields = ['code', 'name', 'type'];
        $allowedFields = [
            'customer_id', 'cliente_id',
            'code', 'codigo',
            'name', 'nombre',
            'type', 'tipo',
            'classification', 'clasificacion',
            'report_section', 'seccion',
            'normal_balance', 'naturaleza',
            'parent_code', 'codigo_padre',
            'parent_id',
            'level', 'nivel',
            'status', 'estado',
        ];

        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $excelFacade = '\Maatwebsite\Excel\Facades\Excel';

        if (class_exists($excelFacade)) {
            $excelFacade::import($import, $filePath);
            return $this->validateTabularRows($import->rows, $allowedFields, $requiredFields, 'Excel');
        }

        if ($extension !== 'csv') {
            throw ValidationException::withMessages([
                'file' => 'La libreria de Excel no esta disponible. Use archivo CSV o instale maatwebsite/excel.',
            ]);
        }

        return $this->readCsvRows($filePath, $allowedFields, $requiredFields);
    }

    private function readCsvRows(string $filePath, array $allowedFields, array $requiredFields): Collection
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
                $headers = [];
                foreach ($line as $header) {
                    $key = strtolower(trim((string) $header));
                    $key = str_replace([' ', '-'], '_', $key);
                    $headers[] = $key;
                }
                if (isset($headers[0])) {
                    $headers[0] = ltrim($headers[0], "\xEF\xBB\xBF");
                }

                $this->assertHeaders($headers, $allowedFields, $requiredFields, 'CSV');
                continue;
            }

            $row = [];
            foreach ($headers as $index => $key) {
                $row[$key] = $line[$index] ?? null;
            }
            $rows->push($row);
        }

        fclose($handle);
        return $this->validateTabularRows($rows, $allowedFields, $requiredFields, 'CSV');
    }

    private function assertHeaders(array $headers, array $allowedFields, array $requiredFields, string $source): void
    {
        $available = array_values(array_unique($headers));
        $missingRequired = array_values(array_diff($requiredFields, $available));
        $validCount = count(array_intersect($available, $allowedFields));

        if (! empty($missingRequired)) {
            throw ValidationException::withMessages([
                'file' => "{$source}: faltan columnas requeridas (" . implode(', ', $missingRequired) . ').',
            ]);
        }

        if ($validCount < 3) {
            throw ValidationException::withMessages([
                'file' => "{$source}: encabezado invalido. Use la plantilla exportada por el sistema.",
            ]);
        }
    }

    private function validateTabularRows(Collection $rows, array $allowedFields, array $requiredFields, string $source): Collection
    {
        if ($rows->isEmpty()) {
            throw ValidationException::withMessages([
                'file' => "{$source}: el archivo no contiene filas de datos.",
            ]);
        }

        $firstRow = $this->normalizeRow($rows->first());
        $headers = array_keys($firstRow);
        $this->assertHeaders($headers, $allowedFields, $requiredFields, $source);

        return $rows;
    }

    private function parsePdfRows(string $rawText): Collection
    {
        $rows = collect();
        $lines = preg_split('/\R+/', $rawText) ?: [];

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with(strtolower($line), 'customer_id|')) {
                continue;
            }

            $delimiter = null;
            foreach (['|', ';', ','] as $candidate) {
                if (str_contains($line, $candidate)) {
                    $delimiter = $candidate;
                    break;
                }
            }

            if ($delimiter === null) {
                continue;
            }

            $parts = array_map('trim', str_getcsv($line, $delimiter));
            if (count($parts) < 3) {
                continue;
            }

            $rows->push([
                'customer_id' => $parts[0] ?? null,
                'code' => $parts[1] ?? null,
                'name' => $parts[2] ?? null,
                'type' => $parts[3] ?? null,
                'classification' => $parts[4] ?? null,
                'report_section' => $parts[5] ?? null,
                'normal_balance' => $parts[6] ?? null,
                'parent_code' => $parts[7] ?? null,
                'level' => $parts[8] ?? null,
                'status' => $parts[9] ?? null,
            ]);
        }

        return $rows;
    }

    private function normalizeRow(mixed $rawRow): array
    {
        $row = is_array($rawRow) ? $rawRow : (method_exists($rawRow, 'toArray') ? $rawRow->toArray() : (array) $rawRow);
        $normalized = [];

        foreach ($row as $key => $value) {
            $cleanKey = strtolower(trim((string) $key));
            $cleanKey = str_replace([' ', '-'], '_', $cleanKey);
            $normalized[$cleanKey] = is_string($value) ? trim($value) : $value;
        }

        return $normalized;
    }

    private function value(array $row, array $keys): mixed
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $row)) {
                return $row[$key];
            }
        }

        return null;
    }

    private function normalizeType(string $value): ?string
    {
        $map = [
            'activo' => 'Activo',
            'pasivo' => 'Pasivo',
            'patrimonio' => 'Patrimonio',
            'ingreso' => 'Ingreso',
            'gasto' => 'Gasto',
        ];

        $key = strtolower(trim($value));
        return $map[$key] ?? null;
    }

    private function normalizeClassification(string $value): string|false|null
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        $allowed = array_keys(AccountingAccount::CLASSIFICATIONS);
        if (in_array($value, $allowed, true)) {
            return $value;
        }

        $byLabel = [];
        foreach (AccountingAccount::CLASSIFICATIONS as $key => $label) {
            $byLabel[strtolower($label)] = $key;
        }

        $normalized = strtolower(str_replace('_', ' ', $value));
        return $byLabel[$normalized] ?? false;
    }

    private function normalizeNormalBalance(string $value, string $type): ?string
    {
        $value = strtolower(trim($value));

        if (in_array($value, ['debit', 'deudora', 'debe'], true)) {
            return 'debit';
        }

        if (in_array($value, ['credit', 'acreedora', 'haber'], true)) {
            return 'credit';
        }

        if ($type === 'Activo' || $type === 'Gasto') {
            return 'debit';
        }

        return 'credit';
    }

    private function normalizeStatus(string $value): ?string
    {
        $value = strtolower(trim($value));

        if (in_array($value, ['activa', 'activo', 'active'], true)) {
            return 'Activa';
        }

        if (in_array($value, ['inactiva', 'inactivo', 'inactive'], true)) {
            return 'Inactiva';
        }

        return $value === '' ? 'Activa' : null;
    }

    private function normalizeLevel(mixed $value): int
    {
        $level = (int) $value;
        return $level > 0 ? $level : 1;
    }

    private function toNullable(mixed $value): ?string
    {
        $text = trim((string) $value);
        return $text === '' ? null : $text;
    }

    private function assertCustomerExists(int $customerId): void
    {
        if (! Customer::query()->whereKey($customerId)->exists()) {
            throw ValidationException::withMessages([
                'customer_id' => 'El cliente seleccionado no existe.',
            ]);
        }
    }
}
