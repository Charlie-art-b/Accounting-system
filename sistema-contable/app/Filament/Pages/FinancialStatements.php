<?php

namespace App\Filament\Pages;

use App\Models\AccountingAccount;
use App\Models\Customer;
use App\Services\EstadoFinancieroService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Pages\Page;
use Filament\Schemas\Schema;

class FinancialStatements extends Page
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-chart-bar';
    protected static ?string $navigationLabel = 'Estados Financieros';
    protected static ?string $title = 'Estados Financieros';

    protected string $view = 'filament.pages.financial-statements';

    public ?array $data = [];
    public bool $generated = false;

    public array $balance = [];

    // para ver conteos
    public array $debug = [];

    public function mount(): void
    {
        $this->form->fill([
            'customer_id' => null,
            'fecha_inicio' => now()->startOfMonth()->toDateString(),
            'fecha_fin' => now()->toDateString(),
            'tasa_impuestos' => 0,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->schema([
                Select::make('customer_id')
                    ->label('Cliente')
                    ->options(Customer::query()->pluck('name', 'id'))
                    ->searchable()
                    ->required(),

                DatePicker::make('fecha_inicio')
                    ->label('Fecha Inicio')
                    ->required(),

                DatePicker::make('fecha_fin')
                    ->label('Fecha Fin')
                    ->required(),

                TextInput::make('tasa_impuestos')
                    ->label('Tasa de Impuestos (ej: 0.13)')
                    ->numeric()
                    ->default(0),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('generar')
                ->label('Generar Reportes')
                ->action('generarReportes')
                ->icon('heroicon-o-play'),
        ];
    }

    public function generarReportes(): void
    {
        $this->form->validate();

        $customerId = (int) $this->data['customer_id'];

        // Debug SIN dd() para no romper Livewire
        $this->debug = [
            'customerId' => $customerId,
            'cuentas_total_cliente' => AccountingAccount::where('customer_id', $customerId)->count(),
            'cuentas_activas' => AccountingAccount::where('customer_id', $customerId)->where('status', 'Activa')->count(),
            'statuses_distintos' => AccountingAccount::select('status')->distinct()->pluck('status')->toArray(),
        ];

        $service = app(EstadoFinancieroService::class)
            ->setCliente($customerId)
            ->setTasaImpuestos((float) $this->data['tasa_impuestos'])
            ->setFechas($this->data['fecha_inicio'], $this->data['fecha_fin']);

        // Aquí sí se define el balance
        $this->balance = $service->balanceGeneral();

        $this->generated = true;
    }
}