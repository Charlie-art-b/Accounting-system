<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Services\EstadoFinancieroService;
use Illuminate\Console\Command;
use Barryvdh\DomPDF\Facade\Pdf;

class GenerateFinancialStatements extends Command
{
    protected $signature = 'financial:generate {customer_id} {--format=pdf}';
    protected $description = 'Generate financial statements for a customer';

    public function handle()
    {
        $customerId = $this->argument('customer_id');
        $format = $this->option('format');

        $customer = Customer::find($customerId);

        if (!$customer) {
            $this->error("Customer with ID {$customerId} not found");
            return;
        }

        $service = app(EstadoFinancieroService::class);
        $service->setCliente($customerId);

        $this->info("Generating financial statements for: {$customer->name}");

        // Create storage directory if it doesn't exist
        if (!file_exists(storage_path('app/statements'))) {
            mkdir(storage_path('app/statements'), 0755, true);
        }

        // Balance General
        $this->line('Generating Balance General...');
        try {
            $balanceData = $service->balanceGeneral();
            
            if ($format === 'pdf') {
                $pdf = Pdf::loadView('exports.balance-general-pdf', [
                    'data' => $balanceData,
                    'fecha' => $balanceData['fecha'] ?? now()->format('Y-m-d'),
                ]);
                $filename = "Balance_General_{$customerId}_" . now()->format('Y-m-d-H-i-s') . '.pdf';
                file_put_contents(storage_path("app/statements/{$filename}"), $pdf->output());
                $this->info("Balance General saved: storage/app/statements/{$filename}");
            }
        } catch (\Exception $e) {
            $this->error("Error generating Balance General: " . $e->getMessage());
        }

        // Estado de Resultados
        $this->line('📈 Generating Estado de Resultados...');
        try {
            $resultadosData = $service->estadoResultados();
            
            if ($format === 'pdf') {
                $pdf = Pdf::loadView('exports.estado-resultados-pdf', [
                    'data' => $resultadosData,
                ]);
                $filename = "Estado_Resultados_{$customerId}_" . now()->format('Y-m-d-H-i-s') . '.pdf';
                file_put_contents(storage_path("app/statements/{$filename}"), $pdf->output());
                $this->info("Estado de Resultados saved: storage/app/statements/{$filename}");
            }
        } catch (\Exception $e) {
            $this->error("Error generating Estado de Resultados: " . $e->getMessage());
        }

        // Balance de Comprobación
        $this->line('⚖️ Generating Balance de Comprobación...');
        try {
            $comprobacionData = $service->balanceComprobacion();
            
            if ($format === 'pdf') {
                $pdf = Pdf::loadView('exports.balance-comprobacion-pdf', [
                    'data' => $comprobacionData,
                ]);
                $filename = "Balance_Comprobacion_{$customerId}_" . now()->format('Y-m-d-H-i-s') . '.pdf';
                file_put_contents(storage_path("app/statements/{$filename}"), $pdf->output());
                $this->info("Balance de Comprobación saved: storage/app/statements/{$filename}");
            }
        } catch (\Exception $e) {
            $this->error("Error generating Balance de Comprobación: " . $e->getMessage());
        }

        // Ratios Financieros
        $this->line('Generating Ratios Financieros...');
        try {
            $ratiosData = $service->ratiosFinancieros();
            
            if ($format === 'pdf') {
                $pdf = Pdf::loadView('exports.ratios-financieros-pdf', [
                    'data' => $ratiosData,
                ]);
                $filename = "Ratios_Financieros_{$customerId}_" . now()->format('Y-m-d-H-i-s') . '.pdf';
                file_put_contents(storage_path("app/statements/{$filename}"), $pdf->output());
                $this->info(" Ratios Financieros saved: storage/app/statements/{$filename}");
            }
        } catch (\Exception $e) {
            $this->error("Error generating Ratios Financieros: " . $e->getMessage());
        }

        $this->info('All statements generated successfully');
        $this->info(' Location: storage/app/statements/');
    }
}