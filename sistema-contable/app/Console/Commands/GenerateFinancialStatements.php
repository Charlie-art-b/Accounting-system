<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Services\EstadoFinancieroService;
use App\Services\PdfFallbackService;
use Illuminate\Console\Command;

class GenerateFinancialStatements extends Command
{
    protected $signature = 'financial:generate {customer_id} {--format=pdf}';
    protected $description = 'Generate financial statements for a customer';

    public function handle()
    {
        $customerId = $this->argument('customer_id');
        $format = $this->option('format');

        $customer = Customer::find($customerId);

        if (! $customer) {
            $this->error("Customer with ID {$customerId} not found");
            return;
        }

        $service = app(EstadoFinancieroService::class);
        $service->setCliente($customerId);

        $this->info("Generating financial statements for: {$customer->name}");

        if (! file_exists(storage_path('app/statements'))) {
            mkdir(storage_path('app/statements'), 0755, true);
        }

        $pdfService = app(PdfFallbackService::class);

        $this->line('Generating Balance General...');
        try {
            $balanceData = $service->balanceGeneral();

            if ($format === 'pdf') {
                $rendered = $pdfService->renderBinary('exports.balance-general-pdf', [
                    'data' => $balanceData,
                    'fecha' => $balanceData['fecha'] ?? now()->format('Y-m-d'),
                ]);
                $filename = "Balance_General_{$customerId}_" . now()->format('Y-m-d-H-i-s') . '.' . $rendered['ext'];
                file_put_contents(storage_path("app/statements/{$filename}"), $rendered['content']);
                $this->info("Balance General saved: storage/app/statements/{$filename}");
            }
        } catch (\Exception $e) {
            $this->error('Error generating Balance General: ' . $e->getMessage());
        }

        $this->line('Generating Estado de Resultados...');
        try {
            $resultadosData = $service->estadoResultados();

            if ($format === 'pdf') {
                $rendered = $pdfService->renderBinary('exports.estado-resultados-pdf', [
                    'data' => $resultadosData,
                ]);
                $filename = "Estado_Resultados_{$customerId}_" . now()->format('Y-m-d-H-i-s') . '.' . $rendered['ext'];
                file_put_contents(storage_path("app/statements/{$filename}"), $rendered['content']);
                $this->info("Estado de Resultados saved: storage/app/statements/{$filename}");
            }
        } catch (\Exception $e) {
            $this->error('Error generating Estado de Resultados: ' . $e->getMessage());
        }

        $this->line('Generating Balance de Comprobacion...');
        try {
            $comprobacionData = $service->balanceComprobacion();

            if ($format === 'pdf') {
                $rendered = $pdfService->renderBinary('exports.balance-comprobacion-pdf', [
                    'data' => $comprobacionData,
                ]);
                $filename = "Balance_Comprobacion_{$customerId}_" . now()->format('Y-m-d-H-i-s') . '.' . $rendered['ext'];
                file_put_contents(storage_path("app/statements/{$filename}"), $rendered['content']);
                $this->info("Balance de Comprobacion saved: storage/app/statements/{$filename}");
            }
        } catch (\Exception $e) {
            $this->error('Error generating Balance de Comprobacion: ' . $e->getMessage());
        }

        $this->line('Generating Ratios Financieros...');
        try {
            $ratiosData = $service->ratiosFinancieros();

            if ($format === 'pdf') {
                $rendered = $pdfService->renderBinary('exports.ratios-financieros-pdf', [
                    'data' => $ratiosData,
                ]);
                $filename = "Ratios_Financieros_{$customerId}_" . now()->format('Y-m-d-H-i-s') . '.' . $rendered['ext'];
                file_put_contents(storage_path("app/statements/{$filename}"), $rendered['content']);
                $this->info("Ratios Financieros saved: storage/app/statements/{$filename}");
            }
        } catch (\Exception $e) {
            $this->error('Error generating Ratios Financieros: ' . $e->getMessage());
        }

        $this->info('All statements generated successfully');
        $this->info('Location: storage/app/statements/');
    }
}