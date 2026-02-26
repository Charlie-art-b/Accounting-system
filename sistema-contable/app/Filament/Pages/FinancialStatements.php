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
use App\Models\JournalEntry;
use App\Models\JournalLine;
use Filament\Schemas\Components\Grid;
use App\Filament\Widgets\FinancialStatsOverview;

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

    public array $estadoResultados = [];

    // ver conteos
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
                Grid::make(4)
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
                            ->label('Tasa de Impuestos (%)')
                            ->numeric()
                            ->default(0),

                        ]),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('generar')
                ->label('Generar Reportes')
                ->action('generarReportes')
                ->icon('heroicon-o-chart-bar'),
        ];
    }

    public function generarReportes(): void
    {
        $this->form->validate();

        $service = app(EstadoFinancieroService::class)
            ->setCliente((int) $this->data['customer_id'])
            ->setTasaImpuestos((float) $this->data['tasa_impuestos'])
            ->setFechas($this->data['fecha_inicio'], $this->data['fecha_fin']);

        // Generar balance
        $this->balance = $service->balanceGeneral();
        
        $this->estadoResultados = $service->estadoResultados();

        //debug en pantalla, sin detener ejecución
        $customerId = (int) $this->data['customer_id'];

        $this->generated = true;

        $this->dispatch('financial-report-generated',
            balance: $this->balance,
            estadoResultados: $this->estadoResultados,
        );
    }

    protected function getHeaderWidgets(): array
    {
        return [
             \App\Filament\Widgets\FinancialStatsOverview::class,
        ];
    }
}