<?php

namespace App\Filament\Support;

use App\Exports\GenericModelExport;
use App\Exports\GenericModelPDF;
use App\Services\CsvExportService;
use App\Services\GenericModelImportService;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class CrudImportExportActions
{
    public static function make(
        string $modelClass,
        string $title,
        string $filePrefix,
        array $fields,
        array $uniqueBy = ['id'],
        array $defaults = [],
        array $enumMaps = [],
        array $requiredFields = [],
        array $fieldLabels = [],
        array $exportFields = []
    ): array {
        // Si no se especifican exportFields, usar fields
        $exportFields = empty($exportFields) ? $fields : $exportFields;
        return [
            Action::make('import_excel')
                ->label('Importar Excel')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('primary')
                ->form([
                    FileUpload::make('file')
                        ->label('Archivo Excel')
                        ->disk('local')
                        ->directory('imports/' . $filePrefix)
                        ->acceptedFileTypes([
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'application/vnd.ms-excel',
                            'text/csv',
                        ])
                        ->required(),
                    Toggle::make('update_existing')
                        ->label('Actualizar registros existentes')
                        ->default(true),
                ])
                ->action(function (array $data) use ($modelClass, $fields, $uniqueBy, $defaults, $enumMaps, $requiredFields) {
                    try {
                        $summary = app(GenericModelImportService::class)->importFromExcel(
                            modelClass: $modelClass,
                            filePath: Storage::disk('local')->path($data['file']),
                            fields: $fields,
                            uniqueBy: $uniqueBy,
                            updateExisting: (bool) ($data['update_existing'] ?? true),
                            defaults: $defaults,
                            enumMaps: $enumMaps,
                            requiredFields: $requiredFields,
                        );

                        self::notifySummary('Importacion Excel completada', $summary);
                    } catch (ValidationException $e) {
                        Notification::make()
                            ->title('No se pudo importar')
                            ->body(implode("\n", $e->errors()['file'] ?? ['Validacion fallida']))
                            ->danger()
                            ->send();
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('Error al importar Excel')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Action::make('import_pdf')
                ->label('Importar PDF')
                ->icon('heroicon-o-document-arrow-up')
                ->color('gray')
                ->form([
                    FileUpload::make('file')
                        ->label('Archivo PDF')
                        ->disk('local')
                        ->directory('imports/' . $filePrefix)
                        ->acceptedFileTypes(['application/pdf'])
                        ->helperText('El PDF debe incluir encabezado y filas delimitadas por "|" o ";".')
                        ->required(),
                    Toggle::make('update_existing')
                        ->label('Actualizar registros existentes')
                        ->default(true),
                ])
                ->action(function (array $data) use ($modelClass, $fields, $uniqueBy, $defaults, $enumMaps, $requiredFields) {
                    try {
                        $summary = app(GenericModelImportService::class)->importFromPdf(
                            modelClass: $modelClass,
                            filePath: Storage::disk('local')->path($data['file']),
                            fields: $fields,
                            uniqueBy: $uniqueBy,
                            updateExisting: (bool) ($data['update_existing'] ?? true),
                            defaults: $defaults,
                            enumMaps: $enumMaps,
                            requiredFields: $requiredFields,
                        );

                        self::notifySummary('Importacion PDF completada', $summary);
                    } catch (ValidationException $e) {
                        Notification::make()
                            ->title('No se pudo importar')
                            ->body(implode("\n", $e->errors()['file'] ?? ['Validacion fallida']))
                            ->danger()
                            ->send();
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('Error al importar PDF')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Action::make('export_excel')
                ->label('Exportar Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->action(function () use ($modelClass, $exportFields, $filePrefix, $fieldLabels) {
                    $excelFacade = '\Maatwebsite\Excel\Facades\Excel';

                    if (class_exists($excelFacade)) {
                        return $excelFacade::download(
                            new GenericModelExport($modelClass, $exportFields, $fieldLabels),
                            $filePrefix . '_' . now()->format('Y-m-d_H-i-s') . '.xlsx'
                        );
                    }

                    return app(CsvExportService::class)->downloadFromModel($modelClass, $exportFields, $filePrefix, $fieldLabels);
                }),

            Action::make('export_pdf')
                ->label('Exportar PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->color('warning')
                ->action(fn () => app(GenericModelPDF::class, [
                    'modelClass' => $modelClass,
                    'fields' => $exportFields,
                    'title' => $title,
                    'filePrefix' => $filePrefix,
                    'fieldLabels' => $fieldLabels,
                ])->download()),
        ];
    }

    private static function notifySummary(string $title, array $summary): void
    {
        $errors = $summary['errors'] ?? [];
        $errorText = empty($errors) ? '' : "\nErrores:\n- " . implode("\n- ", array_slice($errors, 0, 5));

        Notification::make()
            ->title($title)
            ->body("Creados: {$summary['created']} | Actualizados: {$summary['updated']} | Omitidos: {$summary['skipped']}" . $errorText)
            ->success()
            ->send();
    }
}
