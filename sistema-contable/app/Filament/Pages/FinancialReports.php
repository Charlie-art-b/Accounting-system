<?php

namespace App\Filament\Pages;

use App\Models\Customer;
use App\Models\FinancialReport;
use App\Services\EstadoFinancieroService;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use BackedEnum;
use Filament\Actions\Action;


class FinancialReports extends Page implements HasForms
{
    use InteractsWithForms;

    protected string $view = 'filament.pages.financial-reports';

    //aparece en el menú
    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-document-chart-bar';
    protected static ?string $navigationLabel = 'Reportes financieros';
    protected static ?string $title = 'Reportes financieros';

    protected static string|\UnitEnum|null $navigationGroup = 'PRINCIPAL';

    protected static ?int $navigationSort = 11;

    // Form state
    public ?int $customer_id = null;
    public ?string $fecha_inicio = null;
    public ?string $fecha_fin = null;
    public float $tasa_impuestos = 0;

    public string $report_type = 'balance_general';

    // Resultado
    public bool $generated = false;
    public array $result = [];
    public ?int $report_id = null;

    public function mount(): void
    {
        // defaults
        $this->fecha_inicio = Carbon::now()->startOfYear()->format('Y-m-d');
        $this->fecha_fin = Carbon::now()->format('Y-m-d');
        $this->tasa_impuestos = 0;

        $this->form->fill([
            'customer_id' => $this->customer_id,
            'fecha_inicio' => $this->fecha_inicio,
            'fecha_fin' => $this->fecha_fin,
            'tasa_impuestos' => $this->tasa_impuestos,
            'report_type' => $this->report_type,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Parámetros del reporte')
                    ->description('Selecciona cliente, rango de fechas y tipo de reporte. Luego presiona Generar.')
                    ->schema([
                        Grid::make(12)->schema([
                            Select::make('customer_id')
                                ->label('Cliente')
                                ->options(Customer::query()->orderBy('name')->pluck('name', 'id'))
                                ->searchable()
                                ->required()
                                ->columnSpan(6),

                            Select::make('report_type')
                                ->label('Tipo de reporte')
                                ->options([
                                    'balance_general' => 'Balance General',
                                    'estado_resultados' => 'Estado de Resultados',
                                    'balance_comprobacion' => 'Balance de Comprobación',
                                    'flujo_efectivo' => 'Flujo de Efectivo',
                                    'cambios_patrimonio' => 'Cambios en el Patrimonio',
                                    'estado_resultados_integral' => 'Estado de Resultados Integral',
                                ])
                                ->required()
                                ->columnSpan(6),

                            DatePicker::make('fecha_inicio')
                                ->label('Fecha inicio (Desde)')
                                ->required()
                                ->columnSpan(4),

                            DatePicker::make('fecha_fin')
                                ->label('Fecha fin (Hasta)')
                                ->required()
                                ->afterOrEqual('fecha_inicio')
                                ->columnSpan(4),

                            TextInput::make('tasa_impuestos')
                                ->label('Tasa impuestos')
                                ->numeric()
                                ->minValue(0)
                                ->helperText('Ej: 0.13 para 13%')
                                ->default(0)
                                ->columnSpan(4),
                        ]),
                    ]),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('historial')
                ->label('Ver Historial de Reportes')
                ->icon('heroicon-o-chart-bar')
                ->url(fn () => url('/admin/historial-reportes'))
                ->color('success'),
        ];
    }

    public function generateReport(EstadoFinancieroService $service): void
    {
        $data = $this->form->getState();

        // Validación 
        if (empty($data['customer_id']) || empty($data['fecha_inicio']) || empty($data['fecha_fin']) || empty($data['report_type'])) {
            Notification::make()
                ->title('Faltan datos')
                ->body('Completa cliente, fechas y tipo de reporte.')
                ->danger()
                ->send();
            return;
        }

        $this->customer_id = (int) $data['customer_id'];
        $this->fecha_inicio = $data['fecha_inicio'];
        $this->fecha_fin = $data['fecha_fin'];
        $this->tasa_impuestos = (float) ($data['tasa_impuestos'] ?? 0);
        $this->report_type = $data['report_type'];

        $cliente = Customer::findOrFail($this->customer_id);

        $service
            ->setCliente($cliente)
            ->setFechas($this->fecha_inicio, $this->fecha_fin)
            ->setTasaImpuestos($this->tasa_impuestos);

        $payload = match ($this->report_type) {
            'balance_general' => $service->balanceGeneral(),
            'estado_resultados' => $service->estadoResultados(),
            'balance_comprobacion' => $service->balanceComprobacion(),
            'flujo_efectivo' => $service->flujoEfectivo(),
            'cambios_patrimonio' => $service->cambiosPatrimonio(),
            'estado_resultados_integral' => $service->estadoResultadosIntegral(),
            default => [],
        };

        $report = FinancialReport::create([
            'customer_id' => $cliente->id,
            'report_type' => $this->report_type,
            'fecha_inicio' => $this->fecha_inicio,
            'fecha_fin' => $this->fecha_fin,
            'tasa_impuestos' => $this->tasa_impuestos,
            'payload' => $payload,
            'generated_at' => now(),
        ]);

        $this->result = $payload;
        $this->report_id = $report->id;
        $this->generated = true;

        Notification::make()
            ->title('Reporte generado')
            ->body("Se generó y guardó el reporte (#{$this->report_id}).")
            ->success()
            ->send();
    }

    /**
     * URLs de exportación PDF
     * Si alguna no existe aún, devolvemos null.
     */
    public function getPdfUrl(): ?string
    {
        if (!$this->customer_id) return null;

        $qs = http_build_query([
            'fecha_inicio' => $this->fecha_inicio,
            'fecha_fin' => $this->fecha_fin,
            'tasa_impuestos' => $this->tasa_impuestos,
        ]);

        return match ($this->report_type) {
            'balance_general' => url("/api/estados-financieros/{$this->customer_id}/balance-general-pdf?{$qs}"),
            'estado_resultados' => url("/api/estados-financieros/{$this->customer_id}/estado-resultados-pdf?{$qs}"),
            'balance_comprobacion' => url("/api/estados-financieros/{$this->customer_id}/balance-comprobacion-pdf?{$qs}"),
            'flujo_efectivo' => url("/api/estados-financieros/{$this->customer_id}/flujo-efectivo-pdf?{$qs}"),
            'cambios_patrimonio' => url("/api/estados-financieros/{$this->customer_id}/cambios-patrimonio-pdf?{$qs}"),
            'estado_resultados_integral' => url("/api/estados-financieros/{$this->customer_id}/estado-resultados-integral-pdf?{$qs}"),
            default => null,
        };
    }

    public function getExcelUrl(): ?string
{
    if (!$this->report_id) return null;

    return url("/api/financial-reports/{$this->report_id}/excel");
}
}